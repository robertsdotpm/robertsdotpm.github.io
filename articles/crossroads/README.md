A few years back I was tasked with designing a smart commodities exchange using blockchain tech and IoT sensors as a data feed. At first I considered designing a regular exchange but our company wasn't focused on financial services and this would have meant having a lack of resources available to dedicate towards security after launch.

In the end I wasn't comfortable with the idea of launching an under-staffed, centralised exchange, potentially responsible for storing a large amount of users funds. The solution: make it decentralised!

# The big idea -- reusability

There are already hundreds of exchanges and they all do mostly the same thing. Every time these exchanges launch a new UI is created offering users a slight variation on every other trading system. The user is then required to get verified from scratch even though they may have done this on many other exchanges before.

I thought: what if I could design this exchange so that the client would also work for other exchanges that launched? Then the interface would be more useful and it would avoid duplicating work.

I eventually realised that with only minor additions the UI could work not just for asset exchange -- but many other types of two-party contracts too.

# Towards formalising contracts

Building a good, re-usable client for contracts lead me to the idea of creating a basic formalisation of contracts. I realised that if I wanted to make the client as useful as possible, then it ought to be able to work for as many different use-cases as possible.

Forming contracts with people safely requires a good understanding of not just the terms of the contract -- but how the contract is to be carried out and enforced in the event of a dispute. Currently these are key aspects that aren't expressed well in most blockchain and financial systems and it's my belief that highlighting them would make markets safer for investors.

The following two links introduce my ideas in more depth:

1. https://medium.com/@matthew_13387/crossroads-towards-a-universal-browser-for-deals-1905b024dee3
2. https://github.com/robertsdotpm/research/blob/master/two_party_contracts.pdf

