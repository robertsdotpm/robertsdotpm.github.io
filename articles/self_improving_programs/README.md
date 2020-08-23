Imagine for a moment that you wanted to pay someone to improve the speed of your software.  How would you do it? If you're like most software companies then you'll mostly likely hire someone to do the job.

This means spending time to find the right person you want, interviewing them, interviewing someone else if they're not right... until hopefully you find the right person... well maybe. There's still no guarantee that paying them will lead to any improvements to software speed.

But lets assume that you do find the right person for the job. At last, you've gone through the hiring process and found a solid addition to the team. Unfortunately, your company has a lot of software in the works and the demand for this role rises and falls accordingly.

Wouldn't it be nice if you could magically pay someone for improvements as they were needed and not have to manage anything? Sounds like science fiction but smart contracts do offer that possibility. 

# Trustless algorithmic speed improvements

Unlike most aspects of software development – algorithmic speed improvements are something that we can objectively measure.

A developer can either make a set of routines faster or slower – there is no in-between. Such improvements could be attached to conditional payments in a special general-purpose blockchain and then offered as bounties to anyone who can satisfy those conditions.

The cool thing is this could all happen automagically – you could even have tools that automatically issued algorithmic speed improvement bounties for programs based on detecting high run times (IDEs, compilers, etc.)

Imagine a network of self-improving programs that paid for their own electricity by improving the run-time of other software. There is potential for humans to improve the process and for humans to design programs to do it for them, therefore an autonomous network of self-improving programs is possible, but how on Earth would this happen in reality?

**Some weird science fiction shit? Not exactly.**

The idea is that you would generate a list of test inputs and outputs that offered full branch coverage and record the run time against an algorithm. For a person to be able to claim a bounty they would have to create a new algorithm that satisfied every test case with full branch coverage of the new algorithm and a lower medium execution time.

To prevent solutions that output every test case and don't implement the algorithm you would generate large amounts of random test data and store them in a merkle tree. If a person can satisfy every test case with full branch coverage, within a reasonable algorithm size, then they must be implementing the original algorithm and not outputting tests.

The test data in effect becomes like a hash function to use against an algorithm, but its still not perfect. A person could easily add new functionality that did something malicious on the target system so you would need to confine the language of the new solution to avoid malicious additions. This should be easier if its a pure function and it's implemented in a language that prevents memory corruption like Rust.

# The dispute process

To start with: the bounty contract already has crypto-asset locked inside of it to pay for solutions, but the same idea also needs to be applied for solutions so there is something to prevent spam.

The idea is that every solution will require a bond that is returned on success to its owner or given to someone else based on a dispute process. A solution has a period where it may be disputed by anyone else in the network where there are two main ways to dispute a solution:

1. Prove that there is a known input and output for the original algorithm that doesn't occur with the new solution (i.e. the new solution doesn't implement the full functionality of the original algorithm.) OR
2. Prove that the new solution is insecure.

As per point 2 - every single computable solution should also be a [bonded exploit bounty](http://roberts.pm/exploit_markets) against itself, including solutions for other exploit bounties, such that disputes are recursive and settlement will eventually elapse with no one to claim a dispute. This process ripples up the chain, causing funds to be transferred based on a final success or failure.

For more complex cases it should be possible for a human intermediary to verify that a solution is invalid by committing to a larger deposit and asking for their intervention. If the humans convene that the solution is flawed the bond offered for the flawed solution will be distributed between the humans and the person claiming that its flawed. This is the last line of defence. The primary lines of defence can all be automated.  

Now what about readability? Suppose we use this process to prove that the new algorithm is equivalent and secure. We can always use the original algorithm as documentation for the subsumed functionality of the new algorithm as if the improvement were its complication.

Improvements won't necessarily come from humans in the future so the need for readability is reduced. If the routines need to be replaced in the future by a human the same process can be repeated to improve any new changes, thus the original code is still readable.

# Consequences

It is possible to build a network of self-improving programs that modify each other to become faster and more secure. This could be combined with standard software testing practices to improve the speed of important algorithms automatically without the need for managers.

It should be assumed that these aspects of the program are managed by a network of crowd-sourced, self-improving programs that take a human written set of algorithms and make them faster / more secure  – the higher level definition is still human readable – so improvement contracts could be done transparently to the user by their development software.

