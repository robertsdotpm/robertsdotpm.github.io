Sythetix Exchange is a sophisticated Ethereum smart contract that allows investors to gain exposure to financial instruments without having to own the underlying asset. The main currencies it supports are it's own native stable coin (sUSD) and Ether. Instruments that are bought in Synthetix are called 'synthetic assets.'

If you come from a financial background you may have heard the term synthetic asset before and be tempted to think of these assets as equivalent. However, it's best to proceed with a clear slate due to the complex and often misleading way Synthetix Exchange operates.

Indeed, almost nothing in Synthetix Exchange is what it seems (and the white paper doesn't do a very good job to clear this up.)

# The stable coin

Throughout the Synthetix 'Litepaper' many references are made to a currency called 'SNX' [litepaper]. SNX is the main ERC-20 token used in Sythetix Exchange to pay for synthetic assets. Makes sense so far? Well things only go downhill from here.

Though SNX is mentioned 63 times in the white paper - it is not actually the main currency. Instead, that role is filled by another currency called 'sUSD' that is built using SNX. The sUSD currency turns out to be a stable coin, and it's never formally introduced in the 'litepaper.'

What's confusing about is sUSD (short for synthetic USD) is how one might believe the currency to be in the same category as other synthetic assets within the system. In fact, it's not at all. The currency is its own unique token, completely seperate to 'synthetic' assets within the system. So here's an overview of how it really works.

1. Each sUSD is supposed to represent $1 - this is a fixed exchange rate.
2. Thus: if you want to buy $250 worth of Bitcoin you need 250 sUSD. The corresponding amount of synthetic (fake) Bitcoin you receive is based on the price of real Bitcoins at the time of the purchase.
3. The value of sUSD is backed by a certain amount of SNX. It's SNX that trades publicly on an exchange -- not sUSD. Thus, all synthetic assets are backed by a certain amount in SNX.

Below I describe in more depth how this all works.

# The value of the stable coin

**Everything in Synthetix Exchange is fake.** Fake USD. Fake assets. Fake prices. The sUSD asset needs to ensure that there is always enough SNX collateral to make up the equivalent value in terms of sUSD. However, because SNX is an exchange traded asset- it's price is always in flux. If one used the price of SNX in USD directly with no further changes it would expose traders to significant risk as the currency fluctuates.

Stable coins are suppose to be the solution. Synthetix must have thought deeply about how to build a good stable coin. It turns out their solution is just to over-collateralise sUSD. So rather than backing it say: 1 to 1 in SNX. They instead calculate a small percentage of their available balance in SNX, price it in USD, and reserve that value as sUSD. The remaining amount is used as collateral in case the price of SNX drops.

[issuance_ratio]
```solidity
// The raio of collateral
// Expressed in 18 decimals. So 800% cratio is 100/800 = 0.125 (0.125e18)
function issuanceRatio() external view returns (uint) {
	return getIssuanceRatio();
}
```

In practice what this amounts to is having to check the price of SNX / USD on a regular exchange to work out profit and loss during liquidation / closing a position; People are just using an additional obfuscated layer (the stable coin) as a medium of exchange for the quote currency.

**So how good is SNX as a backing asset for the stable coin?**

Well, the authors of the Synthetix lite paper claim that SNX gets its value from fees made from synthetic exchanges...

But wait a minute... Don't synthetic assets --also-- get their value from SNX? It all sounds a bit circular - intrinsic value-out-of-thin-air to me. There doesn't seem to be a point with SNX where you can say "synthetic assets have a real value beyond people choosing to use them for speculation."


[issuable_synths]
```solidity
function _maxIssuableSynths(address _issuer) internal view returns (uint) {
	// What is the value of their SNX balance in sUSD
	uint destinationValue = exchangeRates().effectiveValue("SNX", _collateral(_issuer), sUSD);

	// They're allowed to issue up to issuanceRatio of that value
	return destinationValue.multiplyDecimal(getIssuanceRatio());
}
```

The story becomes a little muddled when you introduce the possibility to use Ether as collateral because it starts to undermine the need for SNX (even though you don't earn trade fees from ether collateral.) After-all, why use such a new and uncertain currency if you can use something far more stable and widely accepted? 

At best you could say: "SNX is a token used for exchange rewards in Synthetix whose value derives from peoples' willingness **NOT** to use it as the sole mechanism to back assets and hence serves as a stronger indication that fee rewards should have a backing value."

It is a lovely self-defeating currency.

# Maintaining the stable coin

In the previous section I explained that X worth of sUSD is more than X USD worth of SNX collateral. It's equal to X + (X * Y) USD worth of SNX, where Y = 7.5 or 750% [issuance_ratio][issuance_ratio2].

The value of Y represents the 'collateralization ratio' between a spendable sUSD balance and how much extra SNX is required to hedge against price swings. You can see that 750% is a very inconvenient number (how useful is an investment platform that only lets investors leverage a pittance of their wealth?)

To ensure investors keep their sUSD balances well collateralized there is a function to flag accounts for violations, along with a penalty cost if their collateral isn't increased above a  threshold within a set timeframe. The full values can be found in the [SystemSettings.sol] settings contract.

One point to pay attention to is like most so-called 'decentralized' applications- almost every aspect of Synthetix Exchange can be changed by it's owners, making it trivial to steal funds or destroy the exchange.

1. [Liquidations.sol#L172] **flagAccountForLiquidation(address account)**
2. [Synthetix.sol#L301] **liquidateDelinquentAccount(address account, uint susdAmount)**
3. [Issuer.sol#L613] **liquidateDelinquentAccount(address account, uint susdAmount, address liquidator)**

Users can call **calculateAmountToFixCollateral(uint debtBalance, uint collateral)** [Liquidations.sol#L145] to obtain the amount in sUSD that needs to be paid to fix their collateral ratios should they fall under the required limits. 

One of the most important pieces of code in Synthetix:
```solidity
    /**
     * r = target issuance ratio
     * D = debt balance
     * V = Collateral
     * P = liquidation penalty
     * Calculates amount of synths = (D - V * r) / (1 - (1 + P) * r)
     */
    function calculateAmountToFixCollateral(uint debtBalance, uint collateral) external view returns (uint) {
        uint ratio = getIssuanceRatio();
        uint unit = SafeDecimalMath.unit();

        uint dividend = debtBalance.sub(collateral.multiplyDecimal(ratio));
        uint divisor = unit.sub(unit.add(getLiquidationPenalty()).multiplyDecimal(ratio));

        return dividend.divideDecimal(divisor);
    }
```

# Wrapping your head around synthetic assets

In Synthetix Exchange the idea of 'owning' an asset is mistaken. Trader’s don't own anything other than their collateral so even saying that one 'bought' or 'sold' an asset is incorrect. What ends up happening instead is you either create or destroy an asset.

When you create an asset: you use collateral to back it and instruct the system to track profit / loss from an external price feed. Destroying an asset means taking back any remaining sUSD collateral and either taking on a profit or loss (remember sUSD represents a certain amount of SNX.)


[asset_exchange]
```solidity
(amountReceived, fee, exchangeFeeRate, sourceRate, destinationRate) = _getAmountsForExchangeMinusFees(
	sourceAmountAfterSettlement,
	sourceCurrencyKey,
	destinationCurrencyKey
);

// ...

// Burn the source amount
issuer().synths(sourceCurrencyKey).burn(from, sourceAmountAfterSettlement);

// Issue their new synths
issuer().synths(destinationCurrencyKey).issue(destinationAddress, amountReceived);

// Remit the fee if required
if (fee > 0) {
	remitFee(fee, destinationCurrencyKey);
}

// Nothing changes as far as issuance data goes because the total value in the system hasn't changed.
```

What I think will confuse most people is the lack of counterparties. Normally when you trade something on an exchange you’re taking the opposite position to someone else. In other words: it’s a zero-sum, two-party contract, that is fully backed by someone else willing to take the opposite bet. But Synthetix Exchange doesn't do this.

On Synthetix Exchange a position is shared by everyone and so is the risk. The way this is accomplished is by using the same quote currency for all pairs and substantially over-collateralising positions. 

If we say that there is X amount of active value in the system in terms of sUSD, what we really mean is the total amount of X * the collateral ratio has been reserved. The assumption is this will provide enough funds to be able to cover any profit made by traders within the system. 

[issue_asset]
```solidity
function _internalIssueSynths(
	address from,
	uint amount,
	uint existingDebt,
	uint totalSystemDebt
) internal {
	// Keep track of the debt they're about to create
	_addToDebtRegister(from, amount, existingDebt, totalSystemDebt);

	// record issue timestamp
	_setLastIssueEvent(from);

	// Create their synths
	synths[sUSD].issue(from, amount);

	// Store their locked SNX amount to determine their fee % for the period
	_appendAccountIssuanceRecord(from);
}
```

The unusual part is what happens if everyone with an active position ends up making a profit? All the account holders still own 100% of their collateral. Essentially, there would not be enough funds within the system to cover the profit made. In simpler terms, the design of Synthetix Exchange does not guarantee it will have the funds to cover positions

It is just assumed that enough people will keep positions active to subsidise those who are moving money out of the system as they take on profit. Hopefully by forcing funds to be locked by a minimum amount of time. In a sense this is similar to a ponzi-scheme but not quite as bad since we don't know for certain if a person will end up being a victim or not. As long as there is enough funds in the system available for conversion-- the system will work much like a hot wallet on an exchange.

There is one considerable benefit to using a shared counterparty system despite the added risks-- **liquidity.** Any asset in Synthetix can be converted into any other. Instantly. Regardless of the need for an order book or the necessary volume in the market (there is no order book.) Such a system can easily make arbitrary asset pairs a possibility.

Any kind of thing that can be tracked can be turned into an asset in Synthetix and traded without anyone having to own it. That is impressive. But as pointed out-- in order to achieve these benefits the system makes considerable security sacrifices - which in my opinion go too far.

# Pricing vulnerabilities

First, because Synthetix is not really an exchange it doesn’t have an order book and can't determine prices directly. The rates for assets in Synthetix come from an external provider (Chain Link.)[exchange_rates]

I won’t be looking into how Chain Link works in this post, but it’s important to note that Chain Link has already messed up its feeds multiple times -- a fact that lead to the creation of an emergency circuit breaker being added to Synthetix that suspends trading should an asset price fall by a certain percent within a short time period [circuit_breaker].

Unfortunately, due to the way Synthetix handles exchange requests there is no guarantee the circuit breaker will ever be triggered!

**There are multiple reasons for this:**

1. The Exchange Rate contract code can allow price quotes to be stale by several hours [stale_quotes].
2. The exact value it can be stale by is arbitrary as every aspect of Synthetix can be changed by the contract owner at any time [backdoors].
3. The Exchange API call does NOT include a price parameter which leaves it vulnerable to race conditions [contract_no_quote].
4. **The UI has no way to specify rates (at all)** and consequently the freshness of any rates provided to the UI is verified and makes any contract-level circuit breaker completely redundant [ui_no_quote].
5. The UI contains multiple pricing race conditions on the same page. E.g. miner fee calculation, expected exchange rate, final confirm, final submit... This is very amateurish to say the least.

**The user has no clue what rate they end up paying. WEW:**

[contract_no_quote][ui_no_quote]
```javascript
// ...

const rate = getExchangeRatesForCurrencies(exchangeRates, quote.name, base.name);
const inverseRate = getExchangeRatesForCurrencies(exchangeRates, base.name, quote.name);
	
// ...

const tx = await Synthetix.exchange(
	bytesFormatter(quote.name),
	amountToExchange,
	bytesFormatter(base.name),
	{
		gasPrice: gasInfo.gasPrice * GWEI_UNIT,
		gasLimit: rectifiedGasLimit,
	}
);
```

Synthetix Exchange were probably thinking that by leaving out a price parameter they would be making the API easier to use -- after-all the price can just be determined at the time of exchange and everything floats anyway. But it means that showing quotes in the UI for a price feed has literally no purpose as new prices are fetched at the time of exchange.

What this means is the prices in the UI can be stale by an indefinite period of time or completely wrong (thereby bypassing the circuit breaker.) The user has ZERO WAY to indicate whether they agree to a certain quote. Just roll the dice and accept because it's no different [pricing_notes].

# Options exchange

Synthetix Exchange includes a feature to allow investors to trade options. In finance an option is an instrument that affords the right to buy or sell an asset in the future. You have the option to choose to take advantage of this right or not (hence to term ‘option.’)

In Synthetix Exchange options eventually expire and have a date by which they mature and can be used (or ignored if one wishes.) The way these options are implemented is much more akin to a traditional exchange as **there is a clear counterparty to the position and a clear winner and loser.** The difference is: multiple people can be on the same side. In which case -- the losers funds are split between the winners.

That means the most important value to consider when trading binary options on Synthetix Exchange is the ratio of funds bet between the two sides. For example, if there is 90 sUSD on long and 10 sUSD on short -- you’re going to make a lot more money if you go short and win than if you go long as there’s more profit to split. The second variable is obviously the amount of funds you put into a position!

Your profit is calculated by multiplying what you bet by the odds ratio of your position. **We can say then that the options exchange is the safest part of the Synthetix project since positions are fully funded.** However, it still relies on external oracles, a poorly designed stable coin, and likely an interface that contains race conditions for pricing these options. 

# Other issues

## Bad engineering practices

A cursory glance of the Synthetix solidity code shows poor coding practices. For instance: there are multiple places within the code that try to hide errors and anticipate what the user wants rather than crashing (with potentially disastrous consequences.)

One such example of this is the code used to check balances for exchanges. You would expect that if a user doesn't have enough funds to cover the conversion that the software would crash with some kind of error. But Synthetix defaults to using all of their remaining balance. This is bad because the user might have made a mistake like accidentally getting the order of the pairs backwards or putting a decimal in the wrong place.

[hide_error]
```solidity
        // when there isn't enough supply (either due to reclamation settlement or because the number is too high)
        if (amountAfterSettlement > balanceOfSourceAfterSettlement) {
            // then the amount to exchange is reduced to their remaining supply
            amountAfterSettlement = balanceOfSourceAfterSettlement;
        }
```

Another example is in the issuance code which seems to have some kind of weird logic to detect edge cases where users were ‘accidentally’ allocated more than they should have from an exchange (WTF?) Much of the code is a mind-fuck moment in general.

You can see that the price quotes are divided up into discrete time periods. It wasn’t my job to do a code audit for this project -- I only glanced where there was ambiguity -- but I have a feeling that the settlement logic around these price periods / edge-cases may be directly vulnerable.

[edge_case]
```solidity
// and deduct the fee from this amount using the exchangeFeeRate from storage
uint amountShouldHaveReceived = _getAmountReceivedForExchange(destinationAmount, exchangeEntry.exchangeFeeRate);

// SIP-65 settlements where the amount at end of waiting period is beyond the threshold, then
// settle with no reclaim or rebate
if (!_isDeviationAboveThreshold(exchangeEntry.amountReceived, amountShouldHaveReceived)) {
	if (exchangeEntry.amountReceived > amountShouldHaveReceived) {
		// if they received more than they should have, add to the reclaim tally
		reclaim = exchangeEntry.amountReceived.sub(amountShouldHaveReceived);
		reclaimAmount = reclaimAmount.add(reclaim);
	} else if (amountShouldHaveReceived > exchangeEntry.amountReceived) {
		// if less, add to the rebate tally
		rebate = amountShouldHaveReceived.sub(exchangeEntry.amountReceived);
		rebateAmount = rebateAmount.add(rebate);
	}
}
```

## Misleading claims

The project claims that there is zero price slippage. Such a claim is provably false due to their poor implementation; Their inability to guarantee redemption of funds; Their inability to control external price feeds; And their inability to guarantee order execution time.

## Backdoors

A key benefit of smart contracts is the ability to create protocols that operate deterministically with minimal trust. One can automate a broad number of contracts without ever having to depend on trusted third parties to play a key role in the system.

In Synthetix Exchange the benefits of smart contracts are lost because most aspects of the exchange can be controlled by the contract owners: They are free to suspend trading pairs. Liquidate positions. Set price feeds directly. Change SNX:sUSD ratios. Really, anything that you might want to set in as an investor can be changed by the admins [backdoor].

There are no client-side checks for adverse parameter selection within the contract state. You are trusting that the state in the exchange hasn’t been tampered with, and there is already at least one way to render the whole exchange userless. For example: the admin can set the settlement time to zero and it will make every trade fail forever. In this sense, no contract made on the exchange can ever be considered final or fully trustless.

For what it’s worth: the correct way to design contract systems that need to be upgraded in the future is to build in a signed update channel with clear change logs and allow users to decide whether or not they want to accept an upgrade. Given that Synthetix can change anything they want at any time it also puts them in a position where a lawmaker can easily argue that they are operating an unlicensed exchange. Whereas, if the system were designed securely that could not happen.

# Conclusion

While there clearly seems to be some benefits to using synthetic assets - the potential harm to investors from using poorly designed financial applications can be significant.

Throughout this report I have identified many common issues that serve to make using the software more risky than it needs to be. I would encourage people to stay away from this project until such obvious problems have been addressed by its engineers.

As these are such common problems an in-depth code review might also be a good idea.

# References

[litepaper] https://www.synthetix.io/litepaper

[Liquidations.sol#L172] https://github.com/Synthetixio/synthetix/blob/6125f587a7e6c22989e1a239ed3d3932bce79fcf/contracts/Liquidations.sol#L172

[SystemSettings.sol] https://github.com/Synthetixio/synthetix/blob/6125f587a7e6c22989e1a239ed3d3932bce79fcf/contracts/SystemSettings.sol

[Issuer.sol#L613] https://github.com/Synthetixio/synthetix/blob/6125f587a7e6c22989e1a239ed3d3932bce79fcf/contracts/Issuer.sol#L613

[Liquidations.sol#L145] https://github.com/Synthetixio/synthetix/blob/6125f587a7e6c22989e1a239ed3d3932bce79fcf/contracts/Liquidations.sol#L145

[Synthetix.sol#L301] https://github.com/Synthetixio/synthetix/blob/6125f587a7e6c22989e1a239ed3d3932bce79fcf/contracts/Synthetix.sol#L301

[issuance_ratio] https://github.com/Synthetixio/synthetix/blob/6125f587a7e6c22989e1a239ed3d3932bce79fcf/contracts/SystemSettings.sol#L66

[issuable_synths] https://github.com/Synthetixio/synthetix/blob/6125f587a7e6c22989e1a239ed3d3932bce79fcf/contracts/Issuer.sol#L247

[issuance_ratio2] https://docs.synthetix.io/litepaper/#snx-as-collateral

[asset_exchange] https://github.com/Synthetixio/synthetix/blob/6125f587a7e6c22989e1a239ed3d3932bce79fcf/contracts/Exchanger.sol#L343

[issue_asset] https://github.com/Synthetixio/synthetix/blob/6125f587a7e6c22989e1a239ed3d3932bce79fcf/contracts/Issuer.sol#L493

[circuit_breaker] https://sips.synthetix.io/sips/sip-65

[exchange_rates] https://github.com/Synthetixio/synthetix/blob/6125f587a7e6c22989e1a239ed3d3932bce79fcf/contracts/ExchangeRates.sol#L15

[stale_quotes] https://github.com/Synthetixio/synthetix/blob/4fd3c1e6ac849f8c43277493e636a2ecc63801e0/contracts/ExchangeRates.sol#L42

[contract_no_quotes] https://github.com/Synthetixio/synthetix/blob/4fd3c1e6ac849f8c43277493e636a2ecc63801e0/contracts/Exchanger.sol#L198

[backdoor] https://github.com/Synthetixio/synthetix/blob/6125f587a7e6c22989e1a239ed3d3932bce79fcf/contracts/SystemSettings.sol#L193

[ui_no_quotes] https://github.com/Synthetixio/synthetix-exchange/blob/master/src/pages/Trade/components/CreateOrderCard/CreateOrderCard.js#L266

[edge_case] https://github.com/Synthetixio/synthetix/blob/6125f587a7e6c22989e1a239ed3d3932bce79fcf/contracts/Exchanger.sol#L186

[hide_error] https://github.com/Synthetixio/synthetix/blob/6125f587a7e6c22989e1a239ed3d3932bce79fcf/contracts/Exchanger.sol#L271

[pricing_notes] In Synthetix Exchange the meaning of ‘pricing’ a synthetic asset is a little different because the amount of the underlying currency you have doesn’t really exist and floats according to the current price (everything is recorded as ratios to sUSD.)

That doesn’t mean a price at the time of exchange isn’t needed though. It’s basically a way to say that: ‘yes, as a user I saw this price and I find it agreeable.’ The exchange still needs to look that the rate is still valid upon receipt of the trade and have some logic to accept or decline it.