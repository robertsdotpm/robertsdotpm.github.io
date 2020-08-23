If you've ever used a SIM card before then, you already know you need one to place calls. But what you might not know is a SIM card is actually a fully functional computer, complete with its own RAM, "disk space", and operating system [uuic-hw-spec][uuic-sw-spec].

The latest SIMs are called "USIMs" and they are universal integrated circuit cards (UICC) programmed to store subscriber details. A UICC has about 16 KB of RAM-- that means with four of these you have as much memory as an original Commodore 64 and it would all fit in your wallet [u/sim-mem]!

If you find that crazy wait until you learn that these cards can be powered through the air using magnetic fields. Bank cards operate using the same chips -- they use UICC too -- which means you can turn an average SIM into a swipe card if you wanted [nfc]. But I won't go into that today. I want to talk more about eSIM cards, they're even cooler.

# What the heck is an eSIM?
An eSIM is kind of like a regular SIM but it's designed to be reusable. If a regular SIM comes attached to an account, then you can think of an eSIM as a blank slate with more flexible account management.

In the future many devices will come with an eSIM chip already installed and all the customer will have to do is scan a QR code and the chip will take care of the rest [esim-overview].

The new eSIM chips mean less messing around with physical cards but they also make life easier for operators and manufacturers, too. For operators, eSIM gives them new remote provisioning capabilities to help with account management, and for device manufacturers it gives them the power to lock down devices, paving the way for IoT that "just works" [esim-spec].

In short: eSIM offers all these great new features over the old U/SIM cards. Of course, to support all that new functionality the GSMA (Global System for Mobile Communications Association) have had to make some changes to the way trust is represented in the mobile ecosystem. **And what they've come up with is pretty darn impressive to say the least!**

# The eSIM public key infrastructure

The new eSIM card introduces a novel public key infrastructure designed to protect the mobile ecosystem [esim-hw-sec-spec]. Here's how it works.

Every network operator and manufacturer must undergo a rigorous audit to participate. Once complete, they receive a certificate which is signed by the GSMA root key [gsma-root-key]. The manufacturer is also required to sign every eSIM they produce, creating a secure audit trail [esim-hw-sec-spec].

**For a manufacturer the process looks like this** [esim-cert-process]:

1. The eSIM manufacturer requests an audit from the GSMA.
2. The GSMA auditors visit the manufacturer premises and check the onsite location against [SAS-UP] standards [gsma-sas].
3. The auditors return a report to the GSMA SAS Certification Body.
4. If approved, the manufacturer will be certified.

The manufacturer is now considered to have a secure facility (SAS-UP certified), **but their product still needs to be tested, and so:**

1. The GSMA maintains a list of security properties for eSIM cards which has been validated and approved by national security agencies [SAS-SM.]
2. A manufacturer creates an eSIM and requests an audit from an independent party. Auditors are specialized laboratories that have been certified by GlobalPlatform and are recognized by national security agencies as highly competent  [esim-cert-process][gp-certified-labs].
3. Auditors attempt to find weaknesses in the product and test the integrity of the solution against a list of criteria set by the GSMA [sas-sm].
4. The audit results get returned to GlobalPlatform who certifies that the product has been tested up to a EAL4+ standard [eal]. 

The manufacturer now has an eSIM that is certified by multiple parties. **At this point there is now proof their facility and product has been audited which becomes part of their cryptographic identity. Impressively, all this information gets recorded in the eSIM cards they manufacture.**

Network operators undergo a similar procedure to check the security around their remote subscription management services [sas-sm][gsma-sas].

**(WTF) Why does any of this matter?!**

The way that U/SIM cards are manufactured today doesn't allow an end-user to tell who made it. Once more, because U/SIMs use symmetric key cryptography for everything, it becomes impossible to identify who created a message (as the operator must also hold the same private keys to decipher encrypted messages)[gsm-crypto].

Without being able to distinguish between a U/SIM manufacturer, network operator, or mobile device-- we cannot establish trust in identities within the system which is required if we are to build secure products. **Being able to know this information would also be beneficial for blockchain applications as a secure identity system solves many trust problems in distributed systems and smart contracts protocols.**

W͟h͟a͟t͟ ͟t͟h͟e͟ ͟G͟S͟M͟A͟ ͟h͟a͟v͟e͟ ͟d͟o͟n͟e͟ ͟i͟s͟ ͟s͟o͟l͟v͟e͟d͟ ͟t͟h͟a͟t͟ ͟p͟r͟o͟b͟l͟e͟m͟.͟ They have created a secure, digital identity system and tied it into the global mobile system. It is a clever, new public key infrastructure (PKI) that identifies all of the main actors, and since it's built directly into eSim chips billions of future IoT devices (and people) are likely to use it. **Usually, these kinds of features are only used by nerds**, but with digital identity systems, you need as many people as possible using it for maximum benefits.

**So here's a quick recap:**
- eSim introduced a new identity system for actors in the mobile network.
- It has the full backing of the GSMA, GlobalPlatform, various national security agencies, security laboratories, and industry partners.
- It will be used for billions of devices; some phones already support it.
- We can use it to solve many complex trust problems in unexpected ways.

# Blockchain applications of eSIM

Before I introduce the blockchain applications its essential to understand that eSim represents a "trusted" system. Most large-scale systems require trust in some way. Even blockchains. It's just that the blockchain advocates neglect to mention who the trusted parties are (CPU, developers, etc.)

With eSIM, the trusted parties have been formally specified and they can only participate after passing rigorous security checks, with multiple independent parties playing a role in the process [esim-cert-process]. If anything goes wrong, it is much clearer which parties are to blame. **In other words: the eSIM PKI forces people to stand behind their claims.**

**There are no blockchain eco-systems today with even half as much accountability.** Blockchain systems, on the whole, seem to emphasize **a lack of accountability** as a key benefit. But on bad days that lack of accountability can really hurt the customer [parity].

I would argue that this doesn't scale well for most admin tasks and that a digital identity system (that doesn't suck) can solve many such problems. The one defined by the GSMA happens to be very well thought out, with proper documentation and transparency in place. Furthermore, since **billions of dollars worth of revenue depend on the system staying secure, it makes the industry motivated to ensure that continues to happen.**

So without further ado -- blockchain-based eSim applications.

## Application 1: Sybil-proof identity management

Many distributed systems work best when one entity cannot control more than one identity within the system [sybil-attacks]. For example, a decentralized Reddit works best if there is only one account per person to limit upvote manipulation, and a proof-of-stake blockchain is more secure if it can limit the amount of stake that a single actor can own.

The eSIM PKI is a new tool that can be used to place a cost on creating sock puppet accounts. It costs money to purchase an eSIM capable device, and it costs time to register it. What you end up doing is forming a cryptographic key pair that is bound to an economic cost and real-life identity. 

Depending on the operators and manufacturers involved -- you might define the weight of an identity and whether to trust it.

## Application 2: eSIM-based digital money

Every eSim is now digital money. We use the quality of the (manufacturer + GSMA + operator + card) signatures to decide its value. **I want to note that the card manufacturer must maintain an audit log for quality control purposes as defined by the GSMAs [SAS-UP] standard.**

The standard specifies safe disposal of key pairs and requires good record keeping (among other things.) I.E., don't reuse the same device IDs in successive cards, and keep track of serial numbers used in production, along with the people responsible for overseeing production.

If a manufacturer or operator were to target the program in spite of the massive audit trail, it would be proof they cannot secure or dispose of their sensitive information, possibly causing them to lose their SAS-UP and/or SAS-SM certification and having to shut down their whole production line. **The resulting damages from this would be immense.**

Depending on the exact currency design, you could have it act more like a bank wire and use a DAO to double-check minting for strange activity. Or have it completely automated. There are many ways it could operate.

## Application 3: Proof-of-burn

Proof of burn is a process of destroying resources to create new ones [proof-of-burn]. A notable example is how master coins were initially produced by destroying Bitcoins which simultaneously served as a demonstration of its perceived value. An eSIM could work the same way.

By exposing secrets within the card it can no longer be used as part of a reliable mobile service. Furthermore, since only one person can use the same identity on the mobile network at the same time, the eSIM would lose most of its value even if people were willing to risk using it.

Interesting note: tamper-proof, secure cards like eSIMs often include hardware measures that brick the device after N unsuccessful attempts. Cryptographic proof-of-failure may be possible. In which case, proof-of-burn has never been so literal!

## Application 4: Proof-of-stake

Create a new blockchain using eSIM-based proof-of-stake. The blockchain allocates stake based on the validity of the eSIM as determined by the GSMA, manufacturer, and / or operator signatures.

## Application 5: Fairer ICO distribution

A scheme whereby the right to buy coins in an ICO is determined by the type of identities in the eSIM PKI. By choosing a number of operators that do know-your-customer (KYC) processes, it will be harder to create sock puppet identities. A fixed limit of coins per customer.

## Application 6: Mining

If it turns out that extracting secrets from eSIM is hard to do then eSIM-based mining becomes a possibility. Depending on the eSIMs security measures, you would have to use specialized tools to extract the secret details. Again, this can all be cryptographically proven for rewards. 

I should note here that certification of the manufacturer dictates that they dispose of sensitive provisioning data after the "personalization" phase (where unique customer-specific information is written to a card [sas-up.]) So this is still a risk since you're solely trusting the operator. 

Another idea would be to do repeated signing on the eSIM certificate details to find a proof-of-work and a valid signature. The algorithm could periodically switch which signed operator + manufacturer combination constituted a valid PoW. That way a single security issue would make a full compromise much harder. Maybe hybrid PoW / PoS?

## Application 7: Pre-paid credit that can never expire (long read)

http://roberts.pm/p2p_mobile_carriers

## Application 8: Spam mitigation

Require a valid eSIM identity for incoming emails.

## Application 9: Improved registration flow

Automatically allow access to services on websites based on possessing a valid eSIM identity. No more time wasting registrations. Bonus points if you need to do KYC and piggyback off an operators KYC process.

## Application 10: Two-factor auth

Many services use SMS messages as part of a two-factor authentication flow not realizing that hackers can impersonate account owners to get replacement SIMs [sim-cloning]. They then insert these SIMs and intercept messages. An eSIM would help stop this problem as each eSIM contains unique certificates that can be verified off the card.

## Application 11: More secure crypto wallets

Numerous hacks and scams in the blockchain world have occurred as a result of bad key management [crypto-losses]. Part of the problem is that people like to run wallets on regular computers which they use to access the Internet and various dangerous websites.

An eSIM would significantly improve wallet security as it could be used to store private keys and place limits on signing transactions. For example, it could enforce daily spending limits, place time-delays on approving transactions [bitcoin-vaults], or require additional approval for large transactions.

## Application 12: Secure messaging

Encrypted messaging tools have historically been too complicated for everyday people to use and asking them to take the time to generate their own key pairs and figure out how to use encryption is not happening. 

The eSIM chip makes this slightly easier as it already contains a valid key pair that can be used for encryption [esim-hw-sec-spec] (ECDSA.) 

## Application 13: Secure DHT node IDs

Distributed hash tables (DHTs) are systems that attempt to allow a network of computers to manage a collection of key-value pairs. For example, animal1 = cat might be stored on two nodes in a network and replicated to more nodes as the network grows or as nodes hosting it leave.

DHTs can be used for all kinds of neat things like distributed file sharing and even video streaming, but they do contain one problem: **they depend critically on good identity management to ensure that identities in the network are sufficiently random and that one entity cannot control more than one identity [dht-security].** The problem is more complicated than it sounds if you don't want to create a centralized registry!

A simple solution is to use eSIM-based identities. Have one valid identity per node in the network. You would have a very secure, federated identity system that could be further improved with SPV-block headers (to show deposit bonds for identity registration) or perhaps proof-of-work.

There are other ways to mitigate the damage that malicious node IDs can do in a DHT, but it's beyond the scope of this post to go into them.

# Summary

* Similar to SIM cards, eSIM is for managing subscriber identities.
* Unlike SIM cards, eSIM supports remote provisioning & multiple plans.
* The eSIM white paper defines a new public key infrastructure (PKI) to interact with identities in the mobile system.
* To participate in the PKI you must pass a rigorous security audit.
* The PKI is thus highly secure and will become ubiquitous.
* A secure PKI solves many trust problems in distributed systems.
* It is therefore useful for blockchain and smart contract applications.
* Fairer ICOs, more accurate review sentiment, less email spam, more secure wallets, accessible encryption, and p2p mobile carriers are but a few of the services that can be created with a good PKI layer.
* **Reliable reputation systems solve trust problems in the online world!**

# References

[uuic-hw-spec] TS 102 221 - V8.2.0 - Smart Cards; UICC-Terminal interface; Physical and logical characteristics (Release 8), https://www.etsi.org/deliver/etsi_TS/102200_102299/102221/08.02.00_60/ts_102221v080200p.pdf

[uuic-sw-spec] GlobalPlatform Card Specification 2.2, https://www.win.tue.nl/pinpasjc/docs/GPCardSpec_v2.2.pdf

[u/sim-mem] SIM/USIM cards, http://cedric.cnam.fr/~bouzefra/cours/CartesSIM_Fichiers_Anglais.pdf

[nfc] Near-field communication, https://en.wikipedia.org/wiki/Near-field_communication

[esim-overview] eSIM whitepaper - The what and how of Remote SIM Provisioning, https://www.gsma.com/esim/wp-content/uploads/2018/12/esim-whitepaper.pdf

[esim-hw-sec-spec] Official Document SGP.25 - Embedded UICC for Consumer Devices Protection Profile, https://www.commoncriteriaportal.org/files/ppfiles/pp0100b_pdf.pdf

[esim-cert-process] The importance of Embedded SIM certification
to scale the Internet of Things, https://www.gsma.com/iot/wp-content/uploads/2017/02/1038-FM-GSMA-Test-Cert-eBook-V6.pdf

[gsma-sas] Security Accreditation Scheme, https://www.gsma.com/aboutus/workinggroups/working-groups/fraud-security-group/security-accreditation-scheme

[sas-up] Official Document FS.04 - Security Accreditation Scheme for UICC Production - Standard, https://www.gsma.com/aboutus/workinggroups/wp-content/uploads/2015/01/FS.04_SAS-UP_Standard_v8.0.pdf

[sas-sm] Official Document FS.08 - GSMA SAS Standard for Subscription Manager Roles, https://www.gsma.com/aboutus/workinggroups/wp-content/uploads/2015/01/FS.08_SAS-SM_Standard_v3.0.pdf

[sybil-attacks] Sybil attack, https://en.wikipedia.org/wiki/Sybil_attack

[proof-of-burn] Proof of burn, https://en.bitcoin.it/wiki/Proof_of_burn

[crypto-losses] List of Major Bitcoin Heists, Thefts, Hacks, Scams, and Losses, https://bitcointalk.org/index.php?topic=83794.0;all

[bitcoin-vaults] Bitcoin Vaults: How to Put an End to Bitcoin Theft, http://hackingdistributed.com/2016/02/29/bitcoin-vaults/

[dht-security] DHT improvement ideas, https://github.com/libp2p/research-dht/issues/6

[gsma-root-key] GSMA Root Certificate Issuer (CI) for eSIM Remote SIM Provisioning, https://www.gsma.com/esim/ceritificateissuer/

[gp-certified-labs] Creating trust through an independent and industry driven certification program, https://globalplatform.org/laboratories/

[eal] Common Criteria for Information Technology Security Evaluation - CC part 3 (page 31), https://www.commoncriteriaportal.org/files/ccfiles/CCPART3V3.1R4.pdf

[parity] '$300m in cryptocurrency' accidentally lost forever due to bug, https://www.theguardian.com/technology/2017/nov/08/cryptocurrency-300m-dollars-stolen-bug-ether