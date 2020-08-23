**Update: 7/12/2017 -** There is an elegant approach to the fee problem raised bellow by designing the virtual transaction overlay so that anyone can submit them by paying the Eth fees themselves in order to claim a fraction of the simulated currencies (perhaps as part of the virtual mining process.) The fee idea was raised by someone else, but its possible to adapt it and use it here to make the whole system work as intended.

-----

Recently someone over at /r/ethereum posted a very interesting topic - the idea of building Bitcoin on top of Ethereum.

It's an interesting thought experiment for many reasons - how would you actually do it? How would you optimize data storage? Are new changes possible? And so on. But more than that it seems to offer potential - by bringing them to Ethereum they become interoperable! 

So first of all let me say that I *really* like this idea, but I also think that we can do much better than just porting Bitcoin over to Ethereum. Over the years there have been HUNDREDS of coins launched using the Bitcoin source code and it is still popular to do that today.

Do you know what these coins tend to have in common with each other:
* They use the same transaction format.
* The same cryptographic primitives for message IDs and signatures
* And often they have the same RPC interfaces.

So I'm thinking that technically if we designed software that was general purpose we're not just going to get Bitcoin ported over to Ethereum but potentially hundreds of assets like Dogecoin, Litecoin, Blackcoin, Peercoin, Feathercoin... there are so many assets out there that we could add!

# Some cool use-cases

## Use-case 1: Preserving cryptoeconomic history

Archiving old blockchains on Ethereum is highly useful from an economic perspective and might even be a new profession in the future.

There could be cryptoeconomic historians whose job it was to preserve old blockchains on top of Ethereum to allow for future study by cryptoeconomists looking to understand the impact of early cryptoeconomic experiments on top of older blockchains.

## Use-case 2: Breathing new life into old blockchains

There have been hundreds of blockchains forked from Bitcoin that achieved significant valuations over very short periods before fading into obscurity. Many of these blockchains had economic activity occur on them but are no longer functional or secure because its unprofitable to mine them.

By recreating these coins on top of Ethereum as highly realistic simulations with virtual mining rules we allow them to function again as currencies - old blockchain assets are put back into circulation.

## Use-case 3: Decentralized friggin' exchange

By "scanning" in old blockchain assets on top of Ethereum we can create a situation where it becomes possible to directly link these new blockchains such that coins can be traded on a decentralized exchange.

Technically this is already possible to do with Bitcoin-style blockchains but it is worth noting this can be done very elegantly on Ethereum.

Technical note 1: the exchange would still need to be coded using atomic cross-chain contracts because using the Ethereum-layer to do this would create incompatibility between older-blockchains.

Technical note 2: soft-forking to add CLTV is now trivial if they are virtual Ethereum assets - cool, right?

## Use-case 4: New wallet support

Because said older assets would all exist on Ethereum as virtual coins respecting their original rules - we can also add support for ERC-20.

Imagine being able to use 100s of different older blockchain assets with your favorite Ethereum wallets today.

It's like you're also benefiting from the entire ecosystem with this approach, so many of these coins would be significantly more usable.

## Use-case 5: Hard-forking away from drama

Occasionally teams in the blockchain space experience a dead-lock where nobody can agree on the exact technical vision going forwards.

In this case, if we scan in a blockchain into Ethereum it provides the market a simple mechanism for hard forking away from the original currency.

It is possible to provide full backwards compatibility with the original currency. In this case, virtual transactions that occur on Ethereum would be made 100% compatible with transactions occurring on the real asset.

You would then have logic to detect whether a transaction first occurred on the Ethereum "fork" or on the real asset and after a certain period of economic activity on the Ethereum fork you can stop requiring this backwards compatibility - in which case its a hard-fork.

This would probably require adding some kind of settlement phase to the virtual coin and allow SPV-proofs to be given from the real asset. You would also have to keep the virtual coin in-sync with all transactions and block header activity happening on the real asset so its potentially a burden.

## Use-case 6: Smart contracts support

It becomes possible to write smart contracts in this system that will work with all of these assets.

This could be done in several different ways - the simplest of which is simply to allow the virtual mining layer to have programmable rules added by users for transaction inclusion. 

From a compatibility perspective, you are not violating any of the original protocol rules. It simply looks like "miners" have trustlessly colluded to enforce smart contracts in every possible case on behalf of the users programmable rules (a weird idea, honestly.)

Seriously, proof-of-work allows this. Programmable virtual mining would also allow for Lightning to be added to every older-style blockchain at once. It's like programming a soft fork as a hard fork but its still a...

Mind = blown

Anyway, this is still a hard fork though so its probably a bad idea.

## Use-case 7:  Elegant soft-forks

If we define Script as the underlying primitive that gives all these coins their "smart contract" capabilities then Ethereum makes it incredibly, incredibly simple to add new functionality to these coins that is backwards-compatible. 

As an example: enable some of Satoshi's disabled OP_CODES
Or perhaps: add CLTV support to every asset?

Or how about adding in new logic entirely? It's Ethereum, it already allows for complex transactions so this is an incredibly elegant way to add new functionality to these older-style blockchains.

By the way: all of the work in Bitcoin designed to avoid hard forks now becomes a huge benefit here as the work can be used to add support to hundreds of assets for new technical features without breaking older transactional protocols (or old-school software) :) 

# Technical thoughts on implementation

* As already stated - the TX formats for Bitcoin-style blockchains are all the same so if the code is built to be general-purpose we can support hundreds of currencies instead of just one.
* Merkle-trees allow for the entire blockchain to be scanned in as a single hash where outputs can be spent by revealing branches in the merkle tree. This is a highly efficient way to scan in gigabyte blockchains on Etherem and allow the coins to be economically usable straight away.
* It was a standard practice to modify the reward mechanism and sometimes even the consensus algorithm for Bitcoin forks. IMO, this can still be specified with higher-level virtual mining rules.
* Because of technical overlap between currencies, it is only necessary to build contract support that understands a general-purpose definition of the Bitcoin transaction format. For optimizations you can use merkalized abstract syntax trees to add support for Script on top of Etherem.
* Fees are your biggest problem with this. You're going to have to pay Ether as fees to execute your virtual transactions but that isn't realistic. What I can suggest regarding this is to combine the decentralize exchange with the contract so that you can specify fees in the virtual coin that are automatically sold to users on the exchange for enough Ether to cover the transactions - this is highly experimental and Ethereum doesn't yet allow contracts to pay for their own fees.
* It is not necessary to have exact support for the economic logic for rewards before these virtual coins are usable. You can choose to defer that until later and only use standard transactions to allow for value to be spent between users.  In this case, there are no new coins created while the economic rules are undefined but "virtual mining" would still allow the system to confirm transactions for users.

