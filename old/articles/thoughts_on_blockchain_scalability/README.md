After reading many papers on blockchain scalability over the years, I am starting to see the painful truth that none of these systems can be scaled. For years I thought that some genius researcher would find a way to do it, and we would get blockchains with on-chain Visa scalability with the same security properties as Bitcoin... but that just hasn't happened.

Here's why that is currently impossible and maybe a path forwards...

# The consensus basket

If you make a list of all the properties that make up a good consensus system you might end up with a basket with the following things inside it:

* Decentralization
* Censorship resistance
* Byzantine fault tolerance 
* Open, equal participation
* Privacy / pseudo-anonymity

Unfortunately, you cannot have everything and keep scalablity.

If you want to have more scalable p2p networks just reduce the number of nodes, and lose decentralization. If you want to make consensus “less wasteful”? Go ahead with [PoS](https://github.com/ethereum/wiki/wiki/Proof-of-Stake-FAQ) and remove equal, open participation. What about making the data structures more “efficient”? Well, [compressing random data is mathematically impossible](http://www.drdobbs.com/architecture-and-design/the-enduring-challenge-of-compressing-ra/240049914).

Bitcoin is an example of a consensus system that sets every parameter in the consensus basket to max settings [to get the most secure system possible](http://unenumerated.blogspot.com.au/2017/02/money-blockchains-and-social-scalability.html). As a result, Bitcoin is the least scalable of any blockchain system today, yet also one of the most secure. If you were to lower any of its settings you would lose it's full benefts. There is no way around this.

I know many people were hoping to disprove this relationship or find a chink in its armour, but if you look hard enough, you'll see that every recent paper on scaling has gone from trying to find that one great discovery to lowering different settings in the consensus basket.

What this says to me is that there is no silver bullet for scalability. Instead, researchers are starting to ask – "well, do we need a billion dollar network to secure every payment or can we use something else?"

# Towards adaptable consensus

[Lightning](https://lightning.network/) is an essential tool for off-chain scalability, but you cannot use it for complex, on-chain state transitions like the ones we see in Ethereum. For this reason, most of the research on on-chain scaling has focused on introducing small amounts of centralization to make improvements to scaling, but from my perspective, this is still the wrong approach.

What we seem to need isn't a single, consensus algorithm for every purpose, but an algorithm that can adapt its economic security guarantees to the required use-case.

In practice, this doesn't mean that consensus should be “proof-of-stake”, “proof-of-work”, "delegated proof of stake", or something else.... But that we need to start modelling consensus in a higher-level language based on the resources used and the benefits gained from doing so.

Our requirements may be greater transaction speed, security, or privacy. Whatever the trade-offs, the consensus algorithm needs to dynamically adjust the resources used to meet those requirements.

For example, if you only want to make everyday payments that are low value you might be satisfied with using a few servers for that ledger. It would resemble a slider of resource-usage that is adjusted based on needs, with a way to migrate funds to the more secure levels as needed. 

Moving up to a higher security level would mean reducing scalability by some factor of ten, but it would also mean that between any two security levels it would be possible to move dynamically between levels without incurring too much of a performance penalty.

# Domain specific consensus language

I like the idea of expressing this in a domain specific language that is formal enough to model the benefits gained from tweaking different parameters and combinations of resource-usage (not mathematics.) 

If this language were formal enough, you could run nodes in it and use it to scale a ledger to any number of transaction volume, since the ledger would genuinely scale with the security requirements. What this may look like as a language is anyone's guess. But it's something to think about.

It may be that after you sit down and formalize all the trust relationships, economic guarantees, resources used, and benefits offered, you'll start to see that the systems we use today already assume a certain level of trust....

Everything from trusting the hardware vendor, the download provider, the developer, and so on are "trusted third parties".... and I think there is room to create a more adaptive consensus algorithm based on assuming that security risk isn't black and white (like every system to date.)

# SGX-based chains

Something that seems to have been overlooked in current consensus design is the possibility of using trusted hardware to solve trust problems. I know, I know, I said “trusted” hardware. But if you think about it blockchains are already *practically* trusted given that it's beyond our current resources to *practically* verify them every time we use them.

>Protip: See "[Ken Thompson hack](http://wiki.c2.com/?TheKenThompsonHack)"

In reality the blockchains we use today rely on a mountain of trust-debt that accumulates to form an informal reputation system. Reputation systems like this are potentially harmful because we don't know exactly who has what influence, nor how that influence may be exploited in the future...

Because of this, I find it curious that whenever people try talk about trusted systems in the blockchain space, we act like we're not already using one. In this light trusted hardware doesn't seem like such a bad option.

I'm not about to say that trusted hardware is some quantum leap forwards in consensus design (it's not)... But I am saying that it has benefits worth considering. **Here are some of the things you can do with [Intel's SGX](https://software.intel.com/en-us/sgx):**

* Run trusted code on untrusted hosts. Use this for a decentralized [shapeshift](https://shapeshift.io/#/coins), low-trust dead-mans switch, escrow agents, and so on.
* More elegant soft forks since you can prove who is running what.
* A way to upgrade any blockchains with any rules you like, since you can write rules that are only run by protected routines based on your requirements and there is no trusted, third-party left to hack.
* Distributed, cross-blockchain smart contracts written in any language.
* Scale any existing blockchain by having provable transaction attestations.
* Improve privacy in payment protocols.
* Use it to create decentralized, autonomous agents on public infrastructure with very low trust. You can distribute agents that [run entire corporations](https://blog.ethereum.org/2014/05/06/daos-dacs-das-and-more-an-incomplete-terminology-guide/) this way that can be arbitrarily complex, even with private information.
* Use it to share public resources with private organizations, leading to greater decentralization, privacy, along with more efficient markets.
* Solve several unsolved trust problems in decentralized storage systems.
* Poormans [Indistinguishability Obfuscation](https://bitcoinmagazine.com/articles/cryptographic-code-obfuscation-decentralized-autonomous-organizations-huge-leap-forward-1391849871/).
* Run near bullet-proof, anonymous, decentralized marketplaces on public infrastructure and store it across obfuscated blackboxes. 
* Reduce attacks in the Lightning Network ([see Teechan](http://hackingdistributed.com/2016/12/22/scaling-bitcoin-with-secure-hardware/).)
* Potentially a better way to do ICOs as companies building software on [decentralized community-run infrastructure](http://roberts.pm/permissioned_resource_coins) would have to depend on their community for hosting so the relationship would be more equal.
* **Practical multi-party computation.** Not just 1 + 1 between hosts (sorry researchers but that's really underwhelming.)
* [Your blockchain problem here?]

**Yes, indeed, you can do far more with trusted computing in distributed systems then you can with blockchains.** So I think it would be a waste to dismiss it completely because of conspiracy theories about Intel.

**Tl; dr:** To continue to make progress we can't let dogmatic thinking overcome us, and most of all: we need to be honest about how these systems work in the real world and not just rely on wishful thinking.