Goroutines for Python
========================

Networked software often ends up written in Go for one simple reason: **goroutines.** They are tiny, fast, and cheap enough to run by the millions.

So where does that leave Python?

Where Python networking struggles
----------------------------------------

Python has good networking libraries, but its dominant concurrency model is **asyncio.** Inside one event loop, tasks take turns running cooperatively.

Think of it like a restaurant with one line. Customers order, sit down, and get their food when it's ready. It works great -- until one customer holds up the waiter.

In asyncio terms, that is a coroutine that does not yield. It might be busy rendering JSON, or sitting inside a C extension that takes too long. Python's usual guidance is: **"move the blocking work to a thread."** But that opens a different set of problems.

Why threads don't solve blocking
-----------------------------------

Introducing threads sounds like a solution, but it does not fully fix the model. This is because:

#. One bad coroutine can still stall the event loop it is running on.

#. Code that mixes async and threads can introduce new deadlocks.

#. Even when nothing blocks, one event loop still only runs **one thing at a time.**

The major flaw is not that Python cannot do networking. It is that Python's async model expects developers to manually avoid every stall. That makes high-performance async code much easier to get wrong than it looks.

How Go fixes networking
-------------------------

Unlike classic CPython, Go was designed to spread work across multiple OS threads. But it does not just throw goroutines onto threads and hope for the best. It has a runtime scheduler that manages lightweight queues of runnable work.

That scheduler is the important part. When a goroutine waits on I/O, Go can usually park it and keep the rest of the system moving. If one worker runs out of work, it can steal work from another.

So the model is not "one loop, don't block it." The model is lightweight tasks, real parallelism, and a runtime that can rebalance work automatically.

Adding goroutines to Python
------------------------------

Python has extensions, so I wondered if an AI could build a Go-style fiber runtime. A runtime that could use multiple cores and work-steal fibers across cores, like Go can.

Part of the appeal was testing the hype. Depending on who you ask, AI either produces unmaintainable slop or can rewrite millions of lines of code perfectly.

So I designed the architecture, trade-offs, and testing strategy. Claude wrote the runtime implementation. This is the result: **an extension on par with Go for regular network handlers, and still close to Go for CPU-bound work with compiled handlers.**

**Free-threaded CPython 3.13t+ with custom extension, state-of-the-art:**

* **Echo req/s:** loadgen-bound; on par with Go in this benchmark (epoll; ~638,000 / s.)
* **Connection churn:** on par with Go, similar CPU usage. (~77,000 / s)
* **Fiber spawn:** 1.35m / s vs Go's 2.1m / s (Cython is 2.29m / s)
* **Regular handlers:** on par with Go for non-CPU-bound network work.
* **CPU-bound handlers:** Cython handlers within 8% of Go; pure Python is ~180x slower.
* **Memory:** 8.8 KB vs 2.7 KB, empty fiber (Cython is 4.7 KB)

I've done my best to squash bugs -- but I don't want to oversell this. The software has over 30k lines of C implementing exotic concurrency algorithms. It will have bugs I haven't found yet. **Consider this extension experimental and subject to change.**

As for OS support: you can expect the software to work best on Linux, because it has had the most testing there. Other platforms are supported, though they do not have wheel builds yet.

The `GitHub repo is here <https://github.com/robertsdotpm/runloom>`__ if you want to try it, and the `benchmarks are here <../_static/runloom_benchmark.html>`__.

----

Technical details
----------------------------

.. collapse:: Click to see technical details.

	Runloom targets free-threaded CPython 3.13t+. It runs many stackful fibers across real OS threads called hubs. Each fiber has its own C stack and switches with an fcontext assembly swap, which means it can suspend inside code that looks blocking.
	
	The trade-off is memory. Fibers need stacks, so Runloom starts each one with a safe 512 KiB default and resizes it based on actual use. Shallow stacks shrink toward what they really need. Growth happens by copying at yield boundaries, with guard pages catching overflow before it becomes memory corruption. The reservation is not the cost: **only touched stack pages count as real memory**, so even a huge declared stack only pays for what it uses.

	With the fiber itself handled, the next piece is where it runs. Fibers are assigned to hubs, and each hub has its own scheduler loop. New fibers enter a Chase-Lev work-stealing deque, while woken fibers go into a local FIFO queue. Idle hubs can steal fresh work from other hubs, but once a fiber has started running, the default scheduler pins it to its hub. Each hub also owns its counters, sleep state, and epoll/kqueue poller; socket blocking goes through netpoll.

	The ugly part is CPython's thread-affine internals: PyThreadState, biased ref counting, mimalloc state, critical sections, and stop-the-world GC. By default, Runloom keeps one PyThreadState per hub and snapshots each fiber's slice of state during switches. That is memory-cheap and works on stock Python, but it means an **already-ran fiber cannot truly migrate across hubs.**

	My optional `CPython patch <https://github.com/robertsdotpm/runloom/blob/main/src/patches/cpython313t-tstate-alloc-home.patch>`__ allows true, "already-run" work-stealing with one ``_alloc_home`` pointer. It works by separating interpreter state from allocator ownership. If you think that's a good idea, here's where I `pitched the patch on the Python discussion forums <https://discuss.python.org/t/migratable-execution-contexts-for-free-threading-a-tiny-allocation-home-hook-pre-pep-seeking-feedback-a-sponsor/107860>`__.

----

Testing process
------------------

.. collapse:: A nice, boring section on testing:

	My main rule for this project was: I would not hand-write the runtime implementation. But my opinion on vibe coding is very low. Any work that the AI does, I can't trust. The question then becomes: "how do you build reliable software in a situation like this?" I think Python has a good answer to this already.
	
	**"If it looks like a duck, and quacks like a duck, then it's a duck."**
	
	The software reliability equivalent of this for a stackful fiber runtime is:
	
	1. Do its performance metrics match up to similar systems?
	2. What about resource usage?
	3. Is it stable over time?
	4. Can it be used in the claimed capacity?
	
	Some of this is manual. You have to measure different variables. Are they as expected? Some of this can be automated: fuzzing, deterministic simulation testing, and so on. It would fill another post to list everything done to test this software, so I'll list the highest payoffs.
	
	- A suite of programs was designed to stress test concurrency, threading, mutexes, and other behavior that matters for a stackful runtime. Most of these programs were able to run at a million fibers each. They exposed the most bugs over every other technique.
	- A bridge for pre-existing asyncio programs allows code to run unmodified. The 100 most popular open source projects test suites were ran through this bridge and any bugs in the runtime were fixed.
	- Custom tooling was designed to hunt for hangs in the scheduler. It consists mostly of fuzzing and often found rare hangs.
	- Open source tests from Python, Linux, Go, and many other projects were ported.
	- The entire scheduler, including I/O, can be fuzzed using fixed seeds. This helps simulate years worth of real usage across the program. The seed makes errors replayable for debugging.

	The testing process also found bugs outside of Runloom. Some of the stress programs exposed issues not just in this runtime but at the CPython boundary too.
	
----

Future research
--------------------

.. collapse:: Still want more? How about this:

	1. Dynamic recompilation of C extensions. If memory-touching instructions could be rewritten to track stack usage directly, fibers would not need the same guard-page overhead. That could take a couple of KB off every fiber. In the best case, Cython handlers could get much closer to Go's memory profile.

	2. Python can be compiled to make it faster, but most ways to do that are still hard to use. Better tooling here would attack the same problem from the other side: keep Python's ergonomics, but make the hot path cheap enough that the runtime is not always waiting on the language.

	The open question is whether Python can get cheap stackful execution without needing quite so much machinery outside the interpreter.

----

I'm currently looking for work. So if you find my work interesting shoot me an email at matthew@roberts.pm. Thanks for reading this long page. Cheers.
