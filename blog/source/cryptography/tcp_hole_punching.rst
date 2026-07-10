A most elegant TCP hole punching algorithm
============================================

.. CAUTION::
   The algorithm in this post needs "double buckets" to be reliable.
   Otherwise, the two sides can split on bucket boundaries and fail to converge.
   If the first bucket fails, compute an additional bucket step for convergence.
   My algorithm trades slow startup for less infrastructure.

TCP hole punching is a way to connect two computers behind NAT routers. The
technique has a lot of requirements to work.

- Both sides must know each other's WAN IPs
- They must know the right external ports to use
- They must connect at exactly the same time

In practice this collapses to using STUN to lookup the WAN IP, doing NAT type
enumeration and port prediction, synchronizing time with NTP, and
having both sides exchange all the needed meta data (WAN IP, port predictions,
future NTP punch time) via some "channel."

That involves a whole list of infrastructure and code to work -- which is complex
and error-prone. What if you just wanted to test whether
your punching algorithm works? You don't care about every other part of the software.
This is a way to do that.

Part 1: selecting the bucket
------------------------------

A simple way to bypass the need for fixed infrastructure in hole punching
algorithms is by using a deterministic algorithm to derive all meta data from
a single parameter. Here's how it works.

First, a starting parameter is chosen based on the unix timestamp. What we need is
a number both sides can converge on without requiring any communication. However,
as we know in distributed systems theory -- there is no "now" in networks. So a
little math is used to make the "now."

.. code::

    now = timestamp()
    max_clock_error = 20s # How far off the timestamp can be
    min_run_window = 10s # Total time window to run the program for both sides
    window = (max_clock_error * 2) + 2 
    bucket = int((now - max_clock_error) // window)

We define what an accepted solution looks like. There
ought to be a certain amount of time that the protocol can run in. A certain range
that a timestamp must fall in to be considered valid. This information is combined
and then quantized in a way that both sides converge on the same number
even given variations to clock skew. This we term the "bucket."

Part 2: selecting ports
------------------------

Now that both sides share the same bucket we can use this to derive a list of
shared ports. The idea behind the port list is that the local port will equal
the external port. Many home routers try to preserve the source port in external
mappings. This is a property called "equal delta mapping" -- it won't work on
all routers but for our algorithm we're sacrificing coverage for simplicity.

In order to generate a shared list of ports without communication between the two
sides -- the bucket is used as a seed to a pseudo-random number generator. This
allows for a much smoother conversion of the bucket number which might otherwise
have little direct variation.

.. code::

    large_prime = 2654435761
    stable_boundary = (bucket * large_prime) % 0xFFFFFFFF

The FF... number fixes the range for the boundary number so it doesn't overflow what
the PRNG accepts as a seed. A prime number is used for the multiplier because if
the multiplier shares a common factor with the modulus (0xff..) then the space of
possible numbers shrink due to shared factors. A prime number ensures
that the number space contains unique items.

The port range is now computed based on using a randrange func. In my code I
generate 16 ports and throw out any I can't bind to. It is expected that there
will be some port collisions with existing programs in the OS when manually
selecting random ports like this.

Part 3: sockets and networking
--------------------------------

It is helpful to review the requirements for TCP hole punching before we proceed.
There are very specific socket options that must be set for it to work.

.. code::

    s.setsockopt(socket.SOL_SOCKET, socket.SO_REUSEADDR, 1)
    s.setsockopt(socket.SOL_SOCKET, socket.SO_REUSEPORT, 1)

TCP hole punching involves aggressively reusing socket addresses. Normally with TCP, if a connection fails you close the socket. But with TCP hole punching
if you do any kind of cleanup you break the protocol.
Cleanup means calling close. And close means possibly sending out an RST packet
that tells the remote router "bad connection, disregard."

If you close the socket the OS will also start its own cleanup process. The socket
will enter states like TIME_WAIT and will be harder to reliably reuse the same address
even if you do set the right socket options. I also want to add that
the only really correct model for TCP hole punching is to use non-blocking sockets.
You can't use blocking sockets because you need to be able to rapidly send SYN
packets without having to wait for a response.

Async networking won't work here either. Async networking prevents your software from
being able to precisely control timing and TCP hole punching is
highly sensitive to timing. If packet exchange is off by even a few miniseconds the whole
protocol can fail. In my opinion: the best way to implement this is to
use non-blocking sockets with select for polling. This allows you to properly handle
every connection state without impacting timing.

.. code::

    for ... sel.register(s, selectors.EVENT_WRITE | selectors.EVENT_READ)

For punching: we simply call connect_ex on a (dest_ip, port) tuple with a 0.01 second sleep until an expiry (src_port == dest_port.) Actual punching here is not terribly elegant
as you're just spamming out SYNs. But this part needs to be aggressive enough to create a remote mapping but not so aggressive that it kills your CPU. 
I use a 0.01 second sleep for that.


Part 4: winner selection
--------------------------

In this algorithm multiple ports are used so many successful connections can be returned. The problem is how to choose the same connection? My approach is to start by having the sides choose a leader and follower. The leader has the greater WAN IP number. The leader sends a single character on a connection and cleanly closes the rest.

.. code::

    winner.send(b"$")
    ...
    loser.shutdown(socket.SHUT_RDWR)
    loser.close()

The follower then uses select to poll events. If it finds one it calls
recv(1) to choose that connection (winner.) The reason a single char
is used here is because TCP is stream-based. If the "success" delimiter was a word then
the follower would have to implement a buffered-reader algorithm just to ensure they
have everything (and I didn't want that complexity.) A single char is atomic.

Part 5: putting it all together
---------------------------------

I present to you: a simple TCP hole punching algorithm requiring only a dest IP to use.
Since the protocol is deterministic no infrastructure is required for testing and
there is no need for the hosts to exchange meta data to use it. The hosts may still use NTP for time which is probably recommended though optional (older OSes in VMs can't maintain time well.)

It is assumed another process will coordinate the running of this tool. Though you can easily
run everything yourself on multiple terminals as long as the commands fall within the 10 second min_run_window. Here, punching applies for common routers using equal delta allocation.

:download:`tcp_punch.py <tcp_punch.py>`

You can run tcp_punch.py 127.0.0.1 to get a feel for the code.
