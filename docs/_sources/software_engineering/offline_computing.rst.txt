Offline computing
===================

Not long ago I was playing the computer game "V Rising" when I came across a surprising
feature. In the game you are able to have followers gather resources after a set period. 
What's interesting is how the feature was implemented. You might think that they
used in-game time to measure this -- but instead it was tied to real-life time.
This meant that if the host shut down the server the amount of time was applied
**as if the followers were still working when the server was off.**

When I saw that feature I couldn't stop thinking about it. I meant: isn't there
something extremely weird here? Normally computer programs aren't supposed to be
able to run when the power is off. This goes against every instinct there is for
how software works. Granted, the effect is an illusion -- the algorithm isn't
really "running" offline -- the server only adjusts the results when it's back on.
But I believe this is still an absolutely whimsical way to think of software.

It means that there are systems for which results don't have to be given
in real time and that further -- there are interesting trade-offs. For instance,
imagine a novel securities market based on the V Rising gathering design.
You might allow algorithms to control resources, defend, attack, gather, and so on.
The server would charge a fee on submission which is used to spawn
resources in-game. Effectively, a time-simulated game
without the need for full server availability.

Benefits
----------------

**Reduced interaction** -- Humans don't have to interact in real time. All strategies
are pre-emptively committed to, possibly with markets for different time frames
like daily, weekly, etc.

**Deterministic** -- A purely deterministic simulation might not be as interesting,
but the server can still inject randomness, e.g., random areas for resources to
spawn.

**Lower cost** -- The entire system can be down after strategies are committed to,
which reduces costs. Users don't have to watch charts all day either.

**Fairness** -- Market blackouts make participation fairer. Front-running is strongly
mitigated by avoiding order books and keeping strategies private.

**Editing** -- One twist would allow strategies to be edited until execution is ready.

Implication
---------------

The essential property of deterministic algorithms is not temporal execution, but
mapping from initial conditions to outcomes given the passage of time as a parameter.
This opens the door to systems that operate in offline, batch, or delayed modes
without losing fidelity, fairness, or strategic complexity. In essence, algorithms
can exist conceptually before they are ever executed, and their “running” is a
matter of computation convenience, not logical necessity.
