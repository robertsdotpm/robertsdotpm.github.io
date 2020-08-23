**7/12/2017: **The protocol is flawed and contains a black mail risk. The other side cannot claim a refund without knowledge of the secret so even if the TXIDs can be validated with ZK-proofs the scheme still doesn't work. I guess its back to the drawing board with this idea.

**Edit 5/9/2016: **I've updated the scheme. I'll update it again if I get time to think of a way to avoid using timelock encryption for the refunds since that will make it more secure. Although I want to add that timelock encryption depends on the security assumptions of hash functions so its also not like this is particularly crazy (as new and scary as things may sound.)

-----

Quite recently its become possible for those outside the field of cryptography to construct zero-knowledge proofs. One such proof is a proof for SHA256 hashes that basically allows anyone to state that "yes, I know some value that produces some known hash."

You can also make this more complex, you can enforce arbitrary constraints on the pre-image and create templates so now you can say to someone that "yes, I know some such value in the form [...] that produces some known hash." While that may seem quite interesting by itself, what's even more interesting is the applications this has for Bitcoin.

Essentially, zero-knowledge proofs for SHA256 allow us to do something that I believe was originally intended in the Bitcoin design: sign partial transactions. So imagine you have a p2sh redeem script that you want to send money to. The p2sh script pub key only includes a hash of the program needed to be provided when you try spend money.

So now you can do something like this "I'm going to send money to this p2sh scriptPubKey but only if it satisfies the following Script template -- I don't care about the rest." Ah ... So why is that useful, again? Well, you can actually use this technique (among other things) -- to solve a previously unsolvable problem in Bitcoin-style smart contracts -- the atomic cross-chain contract using only standard transactions.

What this contract allows you to do is trade coins between blockchains -- only version 1 wasn't that useful because it relied completely on non-standard transactions for it to work. (Non-standard transactions meant that you needed rouge miners to confirm the TX in the blockchain so it would making getting TXs accepted mostly a gamble to impossible.)

TierNolan responded to this criticism by slightly adjusting his original contract. Now it doesn't use a secret revealing scheme based on hashes but I believe it was adapted to actually use hashes of public keys as the secrets (I may be wrong about this) -- it's how I'd do it anyway.

Result: now only one blockchain requires non-standard transactions. An improvement that allows you to use certain currency pairs as a proxy: want to trade DOGE/LTC but only Bitcoin supports non-standard TX? First sell DOGE for BTC, then sell BTC for LTC. Simple stuff. But that might not be ideal. Maybe the trading partners are already known and they just want to swap coins. Maybe there's low liquidity. I think I have the solution.

Basically, you would adapt TierNolan's original atomic secret revealing scheme but mask it with a secret output. The secret will become a standard public key that was hidden in an additional scriptPubKey output which is hashed to produce a TXID and then validated in zero-knowledge SHA256. There would be two versions of the send transaction -- both containing the same secret public key in an additional output. The first version sends coins from Alice to Bob, the second version does the opposite.

The protocol proceeds exactly like in classic cross-chain contracts, only Alice needs to produce a zero-knowledge proof that validates the construction of two hidden transactions to Bob before he proceeds with the protocol. The zero-knowledge proof basically validates the transactions are created properly and includes the correct public key hashes for both transactions (because Bob ends up signing a TXID for an unknown TX.)

Redemption is then done like this: Alice goes to claim Bob's coins and in so doing reveals the hidden public key hash used in her own send transaction. Now Bob knows enough to be able to construct the full transaction for his own use since he now has the missing pub key hash used in the secret output, enabling him to form the full serialized transaction.

Full details described here: https://www.reddit.com/r/Bitcoin/comments/4dum58/zeroknowledge_atomic_crossblockchain_swaps/

**Non-standard transactions used for this scheme = zero.**

It will work across blockchains too and without the need for any stupid game theory or collateral payment. This solution is quite elegant. But what about refunds? Just use a timechain built by a third-party. The construction of a timechain has a trusted setup phase just like the construction for a proving and verification key pair for a ZK-proof, but it only has to be constructed correctly once and then it can't be hacked.

**TX malleability = non-existent**

There's probably a far better way to provably backdoor an ECDSA key that can (eventually) be recovered. I know you can make a ZK-proof for ECC key generation but it would take ~15 hours and be extremely large. Maybe you would choose a point in an infinite known series for the ECDSA private key and then prove with zero-knowledge that the private key was somewhere on that point? What's cool is that some of this stuff is practical now.

Proptip: even the code that would have to be written for ZK-SHA256 is mostly already done. Just checkout the Snarkfront project on Github.

**Now, what other problems are left with this:**
1. Decentralized order book.
2. Enforcing execution of the orders.

*Problem number 1* usually goes something like this "a decentralized exchange needs a decentralized order book or its not truly decentralized! I would never use such an oppressive system!!1two!!" I don't think that's the case. When people talk about decentralized exchanges they only really mean the decentralization of money -- often through some kind of smart contract that maintains the owners full control over their funds.

So if you build an exchange that can be hacked that *doesn't* result in the loss of the customer's money then does that really matter? I would argue there's little to no benefit for the user to having a decentralized order book. All it adds is slower trade executions, more technical debt, complexity, etc, and in the end it doesn't improve security that much. (There are consensus algorithms that prevent DoS and front-running but that's about it.)

*Problem number 2* is quite legitimate though. It states that for users to have full control over their money implies that trades can't be enforced by a third-party which means trades can be canceled as economic conditions become less favorable to the traders.  Collateral offers a partial solution to this -- but lets face it: having the customer hold collateral to back every trade they make will make the exchange unusable.

Maybe the answer isn't to use game theory or crypto for this. Maybe the answer is simply to verify the customer's identity with a photo of them holding their ID / stating their trading intentions and then make them enter into a legally binding contract between the other trader with well explained legal consequences if they refuse to go through with the trade.

An opportunistic attacker who risks being sued and who has to publicly associate himself with his time wasting attacks is definitely going to have second thoughts before he tries to cancel a trade. It may not be 100% but when you combine this with fail-safe refunds and perhaps even a small proof-of-stake payment to open an account - I don't think you're going to have much trouble left with people abusing their leverage.

**So in conclusion:** I think the market is still 100% open for a company who wants to do secure, decentralized asset exchange across blockchains – though their business role would mostly be reduced to handling an order book and providing highly efficient and low-friction identity verification + legal services (you don't want to annoy your users.) 

... And while I can't say with certainty that it would be competitive with the likes of ShapeShift.io -- it definitely would be more secure.

Thoughts?