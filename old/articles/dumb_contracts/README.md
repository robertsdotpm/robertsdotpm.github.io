**Update: 7/12 -** I merged the discussion of datachains into a separate article which is now here: [http://roberts.pm/datachain](http://roberts.pm/datachain).

-----

When I first entered the Bitcoin space the term "smart contract" had a very specific meaning: it was any transactional protocol built on top of Bitcoin whose basic functionality did not have to depend on trusting a third-party. In other words, a smart contract had the same requirements for trust as Bitcoin did -- that the results were absolute and it was beyond the power of anyone to circumvent the intended behaviors of the system.

Today, the term "smart contract" is used to refer to any kind of program or transactional protocol making use of the blockchain, period. The definition has become so very broad that it can more or less be applied to anything, but before I get side-tracked talking about that - I would like to revisit the early days and describe the things that have happened in recent years.

If we go back in time and look at what kinds of smart contracts were around then you'll notice that the trend was towards designing smart contracts to eliminate trust under various scenarios. Things like micro-payment channels and cross-chain contracts were invented which allowed trading to take place without a third-party and whose outcomes were guaranteed by the cryptographic nature of running their respective protocols.

There was a great sense that the blockchain could one day be used to usher in a cryptographic renaissance where fraud was a thing of the past. Of course, none of that ended up happening and the ironic thing was that Bitcoins openness and lack of regulation actually lead to a string of overnight startups whose overall security was infinitely worse than anything Bitcoin could have replaced -- a terrible, and verifiable fact.

# Bitcoin today

I think what happened was: people got greedy. They quickly realized that security is quite hard to get right and that the benefits for doing so are quickly lost for a company who can just use the same rhetoric as Bitcoin to claim the same benefits and people will still buy it. It's depressing that this trick works but its also true: startups have long since tried to "iterate" on security and by the time anyone notices its usually too late.

There's just isn't an incentive for companies to try uphold the same high levels of security that Bitcoin introduced - even if a compatible cryptographic protocol exists for their use-case. If we think of Bitcoin as a security model its perhaps one of the hardest to uphold since it not only allows for complete decentralization of resources but its also highly resistant to collusion - all without becoming a closed, proprietary system.

Most smart contracts aren't like that at all. In fact, most of the current smart contracts are simple representations of real world businesses and IOU-backed derivatives recorded on top of the blockchain. There's really nothing new about that -- the same old flawed trust models are still behind everything we do and you don't need a blockchain to express that.

# Dumb contracts

I would like to try take back the meaning behind some of these words by introducing a new term: the **dumb contract**. A dumb contract is any contract that relies heavily on the actions of other people to carry out its basic functionality, and thus requires third-party trust to operate.

A dumb contract is said to be dumb because people can easily be misguided, hacked, or tricked into doing things outside the parameters of the contract, making the dumb contract no better than using IOUs. Examples of dumb contracts are pervasive and include:

* Escrow systems of any kind where a third-party acts as a clearing house (e.g. Trading systems and currency exchanges.)
* Voting systems (e.g. Bitshares as an organizational structure.)
* Oracle and external state anything (e.g. Augur)
* Multi-sig exchanges (e.g. B & C exchange)
* Tokens for physical derivatives, goods, stocks, or IOUs (e.g. Colored coins, Counterparty, etc - since they may or may not exist and require trust to be redeemed.)
* Virtually all "Decentralized" "autonomous" anything (e.g. The DAO -- because voting can be subverted -- imagine if we voted in Bitcoin to achieve consensus. Absolutely disastrous.)
* Collateral backed protocols that don't account for irrational actors
* Projects that use reputation systems as a magic solution for everything

# Smart contracts

Consider trading cryptocurrencies via a micro-payment channel where only a micro quantity is needed as collateral. If such a contract only requires a minimal amount of trust to run is it smart or dumb? I would say dumb since the micro-collateral is still at risk to a third-party (even though it may only be fractions of a cent.) We should instead reserve the term "smart contract" for contracts that are wholly trustless in the same way that Bitcoin is.

I consider this a useful definition for trust because I believe it is a unique benefit to using Bitcoin. Everything else is just a standard transactional protocol --  completely unremarkable in every way.

Here's a list of contracts which fit my stricter definition:
* Cross-chain contracts
* Atomic cross-chain contracts
* Atomic storage contracts (disclaimer: some of my work)
* ZK swaps (disclaimer: some of my work)
* Secure multi-party gambling protocols
* Secure multi-party lottery protocols
* Certain reward protocols like fair random rewards in games, and virtual items traded over the blockchain.
* Micro-payment channels
* Lightning networks
* Thunder networks
* Hub-and-spoke micro-payment channels
* Two-way pegs
* Artificially intelligent agents operating strictly on publicly accessible, verifiable data sets such as data contained in torrents in order to find useful patterns.
* Certain Internet-based applications that depend on a ledger like Namecoin.
* Certain kinds of contracts backed by collateral where game theory can be used in such a way that the protocol always proceeds as intended regardless of the actions of the participants (e.g. some lottery protocols.)
* Greg Maxwell's conception of zero-knowledge contingent payments
* Other innovative stuff that I'm missing (sorry)

You may think that this is needless nitpicking as the blockchain still allows us to vastly reduce trust when it comes to maintaining accurate records of ownership, but I believe its highly misleading to lump all smart contracts into the same category since you end up implying that a system that relies on third-party trust provides the same level of security as something like a cryptographically enforceable gambling protocol, which is ridiculous.

Actual smart contracts (in my experience) are much harder to invent and produce software for so if we reward every dumb contract with the same level of exposure as say - the Lightning Network - we promote a culture of laziness where cutting corners is more profitable than doing new research. Probably not the best situation for the businesses and customers in the blockchain space who depend on having secure software ...

# Other purposely misleading terms

I'm trying hard to dispel some of the hype around these terms and I can't do that as long as we insist on calling a cat a dog. I'm not sure how aware most people are of this but here's a list of terms that I've put together in the blockchain space that I believe have been purposely abused by marketers to manipulate Bitcoin users and certain investors.

## Decentralized

Bitcoin originally used this term to refer to the absence of structural (or hierarchical) organization needed to record account balances. The Bitcoin system is thus an emergent property that arises through the gradual consensus reached by allowing equal and open participation of everyone who uses it (in this case miners acheive the consensus.)

This property of consensus is quite different from a reputation system or a democratic voting protocol because the process is achieved through a one-way, cryptographic protocol that can't be cheated outside the assumptions made by the protocol -- a guarantee that made Bitcoin famous.

Unfortunately, this term is abused to refer to any kind of organizational structure run by multiple people (ironically, one of the key things Bitcoin solved through proof-of-work.) Examples include The DAO where multiple investors in the system vote on proposals submitted to The DAO.

B & C exchange, BitShares, and OpenBazaar also label their systems as decentralized when the original (and implied meaning) that was popularized by software like BitTorrent and Bitcoin is completely different. There are numerous examples of this misleading term being used to market projects in the blockchain space and it needs to stop.

Recommended definition: distributed.

## Peer-to-peer

Bitcoin and similar decentralized programs used this term to refer to the unstructured network typologies formed by the users running the software. It's a definition that usually implies having the same properties of decentralization and thus has become synonymous with decentralization. 

The outside usage of "peer-to-peer" is different and is more generally used to refer to any service whose functionality is provided by its users. Good examples include Uber and Airbnb. So while this may not be intentionally misleading its still quite misleading in the context of blockchain tech.

Recommended definition: Use client-to-client to talk about peer-to-peer computer networks (imperfect since every client is also a server but that seems to be implied here.) The service-based definition of p2p seems to predate modern networks so they can keep that definition.

Thus under these new terms we can now talk about how the Bitcoin software is client-to-client, peer-to-peer, and decentralized to denote its network topology, logical service provisioning, and organizational trust model without any confusion -- useful right?

## Decentralized organization

When talking about a decentralized organization, Ethereum has said that "instead of a hierarchical structure managed by a set of humans interacting in person and controlling property via the legal system, a decentralized organization involves a set of humans interacting with each other according to a protocol specified in code, and enforced on the blockchain. A DO may or may not make use of the legal system for some protection of its physical property, but even there such usage is secondary."

The problem with this definition is that it confuses the trust model of a standard real-world organization with that of the blockchain which is quite misleading. How about "programmatic organization" since that captures what it is without being confused with the underlying blockchain.

Recall that I said that an organization can't be considered decentralized. So at best, you could say that a programmatic organization is a collection of members whose assets and permissions are controlled in accordance with the rules programmed when the organization was founded.

The state of these assets and permissions is managed by a blockchain in such a way that its operations are transparent to every member within the organization. Thus, a programmatic organization is represented and managed through a decentralized consensus mechanism like a blockchain but relies on a trust-based model for its real world operations.

## Decentralized autonomous organization (DAO)

Ethereum defines a DAO as "an entity that lives on the internet and exists autonomously, but also heavily relies on hiring individuals to perform certain tasks that the automaton itself cannot do."

There's a lot going on here. A lot, which makes it utterly confusing to make any sense out of this. What's on offer here seems to have multiple parts:

* The organizational structure
* The level of automation needed
* The level of human involvement
* The software protocol

The idea behind the organization structure for a DAO is that humans only need to provide minimal input into an automated process so that while some things happen automatically -- the humans are still ultimately in control. If that sounds a little vague its because it kind of is - the level of automation and how involved the humans need to be is ill-defined.

Putting aside the obvious issues of using "decentralized" in the title for now: my biggest problem with this definition is how it seems to be applicable to a standard human-based voting organization and with Bitcoin itself. The consensus-based properties of a blockchain are unique and are far less vulnerable than a human-based democratic process. So I consider likening things like "The DAO" to "Bitcoin" to be purposefully misleading.

To try sort this mess out I tried to find some examples of a "DAO" but almost none of them were autonomous or decentralized in the same way that Bitcoin is, and obviously a human run organization like "The DAO" that needs humans to invest in other (potentially) decentralized autonomous systems shouldn't be considered an autonomous structure at all.

As far as I can tell: "The DAO" is functionally no different to any regular programmatic organization run by humans -- autonomous in the name or not. I do realize that it's heavily implied that we're somehow talking about some kind of next-generation intelligence or blockchain-like structure, but this is a simple smoke screen -- for, wait for it -- **nothing new.**

Even though this term seems to have been constructed to purposely mislead journalists and investors into thinking that Ethereum is doing something ground breaking (and somehow manages to contains **multiple** oxymorons), I believe that the idea behind a DAO is still possible and useful. We just have to restrict the definition to only those systems with similar properties to a blockchain (which I'll refer to as "decentralized autonomous systems" for convenience.)

## Examples of decentralized autonomous systems.

* **Bitcoin (and most cryptocurrencies)** -- Gives rewards for solving hash-based puzzles to facilitates consensus, allowing the movement of assets to be accurately maintained in a shared ledger -- no human input or verification needed as this is all based on cryptographic proof.
* **Namecoin** -- Runs on a standard blockchain and allows for registering domain names which can be queried on the blockchain for their IP address. The system is more useful when combined with standard DNS so that domains are accessible without installing Namecoin.
* **Datachains** -- Full disclaimer: my speculation. Arbitrarily specialized AIs that are downloaded by peers to find patterns in scattered data sets. New assets in the system would be given to participants for finding matches which would be publicly verifiable so that the structure would function similar to a blockchain but would use collateral in a two-way-peg to mitigate spam and a hybrid PoW for security. 
* **Timechains** -- Full disclaimer: some of my work. Timechains create a time-lock encrypted chain of public keys which release rewards when broken. The public keys can be used to encrypt information so that the information is released in the future without human involvement.

Note: This last one may not be a good example of a DAS as a timechain requires initial trust in the person who stitches the chain together -- but after the timechain is setup it requires no further human involvement to provide its basic functionality so it operates as a DAS.

# Other problems with DAOs

The bigger problem with the idea of investing in some kind of artificial, decentralized agent is the fact that a human being is virtually never going to be able to audit all of the code. Even a simple neural network is hard for a human to understand so if someone does manage to make some kind of business-like AI how is anyone going to be able to trust it?

You would need to put in some kind of VETO power or kill switch which is kind of what the Bitcoin alert key does. Furthermore, in a decentralized system everyone is going to need to see the exact code used for that agent ... which may undermine any competitive advantage that might have been produced by running it.

Business is, after-all, heavily about timing as well as having some kind of edge over your competition. So how useful would it be to invest in an AI where those details all needed to be public? I have seen black box obfuscation / indistinguishability obfuscation thrown around as potential use-cases where the code behind an agent could be hidden while still being able to be run securely on untrusted systems, but this would again mean blindly trusting a black box when investing.

My conclusion on the notion of general-purpose AIs being used to control autonomous systems is that investing in them will end up being extremely risky if they do catch on so I think more specific and limited autonomous systems like my conception of [datachains](http://roberts.pm/datachain) will likely fare much better.

# Where we're at today

I think that we're still in the hype stages when it comes to things like programmatic organizations, smart contracts, and decentralized autonomous systems based on the current quality of reporting. Perhaps the reason for this is that nobody can figure out what any of this stuff means since it's virtually impossible to decipher key definitions when so many of them are utterly confusing double-speak or straight up non-sense.

I think that if we were to start by defining what a company like Ethereum does using my definitions, we would end up with little more than a glorified accounting and permission system focusing on recording dumb contracts and programmatic organizations in a shared ledger -- the bulk of which is probably better served by using a traditional legal structure anyway.

Even most of the so-called "smart contracts" on Ethereums are actually just  "dumb contracts" since they require third-party trust to function.  This view also means that its highly unlikely that Ethereum would even be used for a genuine smart contract as such a contract would still need to be done via a specifically constructed cryptographic protocol where a consensus system like Ethereum was unlikely to help with that.

Even less flattering is how things look when we consider the real-world user-cases for this technology. If you have a look at the actual DAPPs that Ethereum developers have already created you'll notice that most of them already existed in a far better, more refined form using various cryptocurrencies or centralized versions before Ethereum existed.

[See https://medium.com/@bedeho/why-your-ethereum-project-will-most-likely-fail-d14b6d8f1c7c#.ejs2da9e2 for more details](https://medium.com/@bedeho/why-your-ethereum-project-will-most-likely-fail-d14b6d8f1c7c#.ejs2da9e2)

# Closing remarks

If we keep letting companies make bold and misleading claims about their technology by deliberately mislabeling things to erroneously allude to properties of the blockchain, we allow them to undermine what the blockchain stands for and to make a profit by doing so.

If you notice any company doing this you need to call them on their bullshit otherwise people are going to start saying things like "I invested in 20 Blockchain the other day - my banker gave me a great deal on the monthly fees!" which is honestly quite a disturbing thought.

Thanks for reading.