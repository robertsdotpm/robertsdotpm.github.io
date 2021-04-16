**Update 26 May 2017:** the 3 of 6 multi-sig scheme has an obvious race condition. This problem can be fixed by using a 4 of 7 scheme that looks like future, future, y1, xy, current, x1, and x2 public keys where xy becomes a special key with new consensus rules based on homomorphic secret sharing (will define this in more detail tomorrow.) 4 of 8 might also work where the keys would be future1, future2, y1, y2, current1, x1, x2, x3 but I'll need to work out how to reduce the key no first.

**Update 2:** The existence of valid signatures that come from a secret and which utilise knowledge of future or current owner outside of the expected reveal periods imply that the exchange is attempting to attack the process. Fortunately, this can be added to the protocol too as ECDSA sigs can be directly validated from within Ethereum. Thus the entire scheme can so far be reduced to a 3 of 4 multi sig: future, current, x1, y2 that will fit inside a standard p2sh transactions and the race condition is eliminated by using homomorphic secret sharing through additive pub keys. 

**Note:** It may be possible to combine x and y to improve standardness so that it is only a 3 of 3 multi-sig but I need to think on this some more.

**Update 3:** 1 of 2 seems like it will work taking into account the above where key1 = future + x and key2 = current + y. I'm not sure why I didn't see this before. 

**Update 4:** this design seems to allow for cross-blockchain lightning without the need to modify any blockchains and of course - it will work with ALL existing blockchains based on multi-sig -- currently working on this. I believe blind signatures are the key to this.

--------

If you've ever used a cryptocurrency exchange before you will have noticed a broad and dizzying array of new currencies. At the time of this writing there are hundreds of currencies being creating including new blockchains, colored coins, ERC-20 tokens, counter-party tokens... the list goes on. You can imagine that as these currencies proliferate it becomes harder and harder to directly match up  traders on an exchange. 

A standard exchange uses a limit-based order book to match up sellers and buyers by checking if the price per coin is compatible against a given currency pair. This means that limit orders can only be used to match up two traders if the currency pairs are the same so: Alice has x and wants y whereas Bob has y and wants x – simple.

This relationship works well where there are only a few major currencies but it doesn't work well for things like commodity trading or bartering where one asset may be traded directly for another.

It might also be confusing if the user just wants to trade currency A for currency B – having to sell currency A for a major currency first and then using that to buy currency B. Well, doesn't it make more sense to allow trades to be structured however the user likes?

# The Kidney-swapping Algorithm

When someone needs a new kidney a transplant from a friend or relative is far more likely to be a good match. The problem is that a donor isn't always compatible and waiting lists for a new kidney can be quite long.

An innovation solution to this problem is the kidney-swapping algorithm: instead of having the donors match up to the recipients directly they form chains of donors who swap with a stranger in exchange for having another stranger swapping with their friend or family - or indirect matching.

You can apply this same algorithm to pairing up traders more efficiently on an exchange. Consider this example:

> Alice: has 10 doge, wants 10 litecoin
> Bob:  has 10 bitcoin, wants 10 doge
> Eve: has 10 litecoin, wants 10 bitcoin

Here – none of the traders can be matched up directly with a limit order because none of them directly have what the other wants. However, if we do a kidney-swap - the trades can still happen:

Eve sends to Alice who sends to Bob who sends to Eve … and everyone gets something they want.

# Atomic kidney-swaps

On a centralized exchange kidney-swaps can easily be accomplished since the exchange ends up with full control over the coins. But how would you accomplish this on a decentralized exchange where all of the traders are anonymous and assumed-to-be highly adversarial?

On Ethereum this algorithm is trivial since you can write arbitrary contracts with ease. Simply create an escrow contract that receives deposits and acts on a signed message to do some action between N parties. This can all be directly enforceable so there is no need for a third-party

 But the question is – how would you accomplish this very same contract on legacy blockchains where the assets are on separate blockchains and where said blockchains have very poor support for complex contracts?

Micro-payment channels are obviously out for this. Traders can easily cut off other parties and leave someone else holding useless assets. So what about cross-chain contracts? Well, these never really worked that well for the stated purpose anyway – the idea was to link separate blockchains but such blockchains have poor support for complex transactions so the contract would either be considered invalid or never confirm.

# Ethereum saves the day (again)

This is a new one for me – but it is possible to create an Ethereum contract that can actually be used to enforce N-party cross-chain atomic contracts on legacy style Bitcoin blockchains in such a way that all transactions taking place on the legacy blockchains use ONLY standard transactions.

Here's how it works.

You create a special deposit contract on Ethereum that stores the state of four sets of secrets x1, x2, y1, y2. The x variables define refunds, whereas the y variables are for claiming coins. The contract says that before a certain time-frame if you reveal x* or y* all funds are released to the counter-parties. After this time has elapsed you have a set time-frame to reveal either X or Y or all the funds in the contract are automatically distributed to the Ethereum addresses of the kidney-swap participants.

Once the secrets are revealed, the contract enters a settlement phase where it must clear for a set period. During this period if anyone can prove knowledge of the OTHER secret then the contract automatically releases the Ether to the kidney-swap participants. Otherwise, the settlement period elapses and the Ether can be claimed by the original owner.

Now prepare to have your mind blown because **these aren't ordinary secrets – they are [pay-for-private key contracts](http://roberts.pm/pay_for_private_keys).** Essentially, the secrets prove knowledge of a particular ECDSA key and what can ECDSA keys be used for? Multi-signature transactions on legacy assets! So essentially, you can now use Ethereum to implement hash-locked atomic swaps on any legacy blockchains with 100% compatibility by using private-keys as secrets.

Further, we enforce time-periods that allow our traders on the legacy blockchains chance to either claim their respective coins OR claim their refunds. This is accomplished by utilizing a special multi-signature address for deposits that looks like this:

> 3 of 3: owner, x1, x2, future, y1, y2

# The three possible paths for redemption

1. The current owner uses his key and knowledge of x1 and x2 secrets to sign a refund to himself.
2. The future owner uses his key and knowledge of y1 and y2 to claim his coins.
3. X* + Y* are used. This would allow the secret-holder to claim all coins but we make this unprofitable by making him deposit collateral into the Ether contract worth more than the assets he can claim with both secrets.

The reason we use this particular leverage in this contract is subtle but we do not know beforehand whether or not the secret-holder also holds the keys for multiple other people in the trade. If it were just 2 of N then the secret holder could use owner + future to claim the coins, theoretically violating the guarantees of this protocol.

By using this particular leverage it means that a secret must ALWAYS be released to move coins. Therefore, there are only two states for this that make any sense: x* is released so the current owner gets a refund or y* is released and the future owner claims the coins. 

1. **Deposit phase:** all parties make a deposit into a multi-sig based on who the future owner of the coins needs to be. If any secrets are released before the deposit phase + N minutes the collateral is realised as compensation.
2. **Claiming phase:** if all the parties went through with the deposits the secret-holder releases y* and grabs his coins. The secret is revealed on the Ethereum contract by a set time to start the clearing phase for the collateral holder. He gets his collateral back if he doesn't reveal x* during this period and try to cheat – this gives the users chance to use the secret.
3. **Refund phase:** one or more of the parties didn't go through with their deposit. The secret-holder releases the refund-secret. If the claiming secret is revealed during the refund phase he loses all his collateral.

**This protocol allows an Ethereum user to earn money from trade participants by providing a new service to help improve exchange across blockchains (and asset classes) by locking up his Ether into a modified settlement-delayed pay-for-private-key escrow contract.**

The beauty of this design is that only one party needs to hold collateral and they can provide this service to potentially hundreds of other users that form kidney-swapping chains between legacy assets.

Potentially it may be possible to adapt this contract so that there is a fee phase prior to entering into the deposits. That way you can actually punish users who don't go through with the deposit phase by taking their fees and the participants still wouldn't have to hold collateral.

But I leave that for future work. For now It is very interesting that Ethereum can be used as a switch board to connect traders on other blockchains without trust – and I honestly don't believe I have seen this before.

So anyone need a kidney?