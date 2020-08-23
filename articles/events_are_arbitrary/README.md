When I first got into Bitcoin my main area of interest was in smart contracts. I used to marvel at how the blockchain could be used to eliminate trust between people and I'd despair whenever an OP_CODE was removed (making the former harder to do.) But that's only because I didn't understand one subtle quality of how a blockchain works: **events are arbitrary.**

The blockchain really only has two qualities worth mentioning:

1. It can securely order events on a network of untrusted computers.
2. It defines an event called a transaction.

The second quality is optional [0]. It just so happens that in the case of Bitcoin enough information is already included with the software to describe what a “transaction” means so that now its become impossible to separate the network definition of “the blockchain” from “a transaction” [1].

But if you understand why this is then you understand that the meaning behind events on a blockchain only ends up mattering to the people who use them. One interesting consequence of this is that a blockchain only needs to defines fault-tolerant ordering and it leaves the rest up to the user.

When I first realized this it was a huge revelation. It meant that for the first time ever I no longer had to rely on the blockchain for enforcing my smart contract logic. **Instead, I could simply implement my own rules as a separate consensus layer by ascribing the meaning behind events myself and then using transactions as a carrier for that information.**

So imagine I wanted to build a wallet that was using disabled OP CODES. No problem. I'll define a new event and write the rules for what that event enables in any old programming language. And so long as everyone who uses that wallet has a copy of the same set of rules they will all agree on the outcome of events (which will be elegantly ordered via the blockchain.)

So if, for example, I defined an asset for something ridiculous like Sudoku-locked transactions by saying that a group of meta-data within a TX stores a Sudoku-puzzle that accepts Sudoku-solutions to redeem them - everyone who uses that wallet will be able to understand said asset class without the blockchain itself needing to be expanded to “support” those rules.

So, as long as a blockchain allows information to be written to it can be extended to support any kind of qualities you like. You can even implement Ethereum's entire virtual machine on top of Bitcoin as a consensus layer and anyone who chooses to run your extension will benefit from that additional view of information. This is actually how Counterparty and Rootstock have managed to add such complicated features to Bitcoin but every blockchain developer can benefit from this same basic understanding.

**Tl; dr:** The meaning behind information on a blockchain is whatever a program makes of it and this means that any kind of smart contract can be supported on any kind of blockchain so long as a program's users all agree on using the same formulation of rules provided for an event.

[0] You could build a blockchain that has no idea of a transaction at all.

[1] ... Which is probably a good thing because it turns out transactions are useful for controlling who gets access to publish on the blockchain.