A couple of years back I was working on a [smart contract in Bitcoin that implemented pay for private key contracts](http://roberts.pm/atomic_cloud_storage). The idea was that you can setup a contract to pay someone for releasing the details that allow a particular ECDSA Bitcoin private key to be extracted and payment is obviously conditional on the solution being correct.

In Bitcoin this is really damn complicated for a number of reasons. The first  reason is that transaction IDs in Bitcoin can be randomly mutated so that chains of unconfirmed transactions can be invalided [0]. And the second reason is that all of the OP_CODES you need to create complex contracts in Bitcoin are either disabled or too limited to use.

That meant that the only way I could figure out how to get this contract to work on Bitcoin was to:

1. Rely on a theoretical segwit existing (it’s not deployed yet) and
2. Build a chain of transactions in such a way that Bitcoin itself is used to validate signatures so that if a person provides incorrect secrets to try claim payments they only end up scamming themselves out of a future payment.

Number 2 was done with complex zero-knowledge proofs and the code never managed to get anywhere beyond a shitty research-grade POC. I think its still sitting on some dark VM somewhere... So it’s not exactly very useful.

Now enter Ethereum.

# Pay for private key contracts in Ethereum

Believe it or not - it’s not exactly easy to do this in Ethereum either because Ethereum doesn’t allow you to directly generate and verify ECDSA key-pairs from within a contract [0]. The solution is to use a function called [ecrecover](http://solidity.readthedocs.io/en/develop/units-and-global-variables.html) that we can exploit to implement pay for private key contracts in Solidity.

Here’s how it works. ECDSA signatures have a few components that matter, s and r. The r value is generated either from a deterministic look at the message to sign OR from a random number, where the s value is uniquely determined by the message that is signed.

The idea is that the r value created for a signature should always be unique with respect to the same public key. Or to put this another way, if you can get 2 valid signatures for the same public key that contain the same r value but with *different* s values then you can effectively recover the priv key. 

**To implement this in Solidity requires that you:**

1. Generate two signatures for unique messages with duplicate r values.
2. Call ecrecover on both signatures and check that the public key returned matches the public key whose private key we’re paying for.
3. Check that the r values are the same; That s in both sigs are unique; And to make things less complex for recovery -  check that v is the same for both signatures [1][2].

I have implemented the first step [as a Python program](https://github.com/robertsdotpm/pay_for_private_keys/blob/master/backdoor_sigs.py) that generates an ECDSA key pair using the Bitcoin curve and then generates provably insecure signatures. This is what the output looks like:

```
Enter an Ethereum address that can redeem the coins: [enter is default]
Priv key = 0dbddccbd9c0397ae80d9ba2a01e625b71dae3413598ae21fe4d3e0cea2c5d67
Pub key = 049c1c62c019dc8156671f1e74aff64b2a102bedf29f33dc52abdb80dba70a95a3e37058f85d38771e6034715787d36877a57f4a739b1d3cdd62e738d4f8ad3d3c
Address = 0xfc2a2603163b3e3386507c28de32f560e33b25bc
r1 = 66b47c56dfc6d319786c6a7e4f3271426181766898208d5bcc06a1c8e3975c4f
s1 = 09781316b4b9188a074c152feecb3aceda224fb88d4b616011697baeeaeb7988
s2 = 6e1bc76f6796e950c9c072000fa69c53d4655323fdadde2ecb2e3874d231696e
hm1 = 6d255fc3390ee6b41191da315958b7d6a1e5b17904cc7683558f98acc57977b4
hm2 = 4da432f1ecd4c0ac028ebde3a3f78510a21d54087b161590a63080d33b702b8d
v1 = 1c
v2 = 1c
m1 = test1
m2 = test2
solution hash = a0365a69d289ebc16179c38dcf52770605c9920c702f6d751ccb0019b055b852
Eth input = 
 "0x6d255fc3390ee6b41191da315958b7d6a1e5b17904cc7683558f98acc57977b4", 28, "0x66b47c56dfc6d319786c6a7e4f3271426181766898208d5bcc06a1c8e3975c4f", "0x09781316b4b9188a074c152feecb3aceda224fb88d4b616011697baeeaeb7988", "0x4da432f1ecd4c0ac028ebde3a3f78510a21d54087b161590a63080d33b702b8d", "0x6e1bc76f6796e950c9c072000fa69c53d4655323fdadde2ecb2e3874d231696e", "0xcfd31d218dccc9b553458f1b6c4ace40dada01f7", "0xcfd31d218dccc9b553458f1b6c4ace40dada01f7", 0
Recovery 1 = 049c1c62c019dc8156671f1e74aff64b2a102bedf29f33dc52abdb80dba70a95a3e37058f85d38771e6034715787d36877a57f4a739b1d3cdd62e738d4f8ad3d3c
Ver sig hm1 from rec = True
Ver sig hm1 from attack = True
Recovery 2 = 049c1c62c019dc8156671f1e74aff64b2a102bedf29f33dc52abdb80dba70a95a3e37058f85d38771e6034715787d36877a57f4a739b1d3cdd62e738d4f8ad3d3c
Ver sig hm2 from rec = True
Ver sig hm2 from attack = True
```

**Now send this to [our Ethereum contract](https://github.com/robertsdotpm/pay_for_private_keys/blob/master/truffle/contracts/PayForPrivKey.sol) and here is what the steps are:**

1. Create a new instance of the PayForPrivKey contract and specify the Ethereum address for the public key whose private key you want to find. The Python program converts the ECDSA pub key to an Ethereum address for you automatically :) So copy-paste.
2. Create a commitment. This means you're just hashing all the solution values for the backdoored signatures along with the address that you want to receive an Ether reward at. When you run the Python script it asks for a destination address (there is a default Ethereum address there for testing already.)
3. Copy paste the solution_hash into the CommitSolutionHash function. It will return an index number that you should save.
4. Wait for the min_block number so you don't get h4x0red by blockchain devs.
5. Prove that you know the solution by revealing the commitment. There is a function in the Solidity contract call ProvePrivKey that takes most of the data above to prove you know the private key. I have made things easier -- just copy and paste the Ethereum input string directly into this function and replace the index number at the end with the result from step 3.
6. Enjoy your new Ether.

Note: I haven't deployed this to testnet or mainnet yet because my Internet is currently terrible, but if anyone wants to deploy this to either network hit me up over email with a contract address.

# Atomic storage contracts

Pay for private key contracts can be used as the basis for a large number of contracts. In fact, some of the original discussion around these concepts took place in the context of the Lightning Network where people were discussing how to get someone to release a private key. 

You can also use this contract for gambling and for Peter Todd's dark release scheme, but my original motivation for trying to get this to work was to implement my idea for atomic storage contracts.

...

Now image that you want to pay someone to store content on their hard drive. Traditionally, you could pay them after they store the content but you could easily scam them afterwards by not sending payment. The other solution is for you to send the money first but now the host can scam you. 

**What I wanted to design was a contract that could atomically bind content to money so that neither side can scam the other...**

1. The user and host generate ECDSA key pairs, {user_pub, user_priv}, {host_pub, host_priv} [4].
2. They add the public keys together using eliptic curve addition, add_pub.
3. The user uses add_pub to encrypt their content with ECIES, encrypted_file.
4. The user uploads encrypted_file to host and deletes their original copy.
5. When the user wants to retrieve the file, the host returns the encrypted form.
6. The user verifies the file. (The encrypted file is useless without the key.)
7. The user funds a contract that pays the host N Ether for releasing host_priv.
8. The host verifies the contract terms and releases host_priv to claim payment.
9. The user now has user_priv and host_priv. They use ECC addition on the private keys to get the private key for add_pub.
10. The original file can now be reconstructed at the same time that the host has been paid, thus neither side can be scammed in this agreement.

The tools for this contract now exist. Pay for private key contracts were the hard part but [pythonbitcointools](https://github.com/vbuterin/pybitcointools) already does public key addition and [Bitpay have released a library for Bitcoin](https://github.com/bitpay/bitcore-ecies) that does ECIES.

Thus, it is completely possible to implement an atomic payment system for a decentralized cloud storage system on top of Ethereum without the need for a custom blockchain [3] like Sia or Filecoin for trustless payments.

Anyway, that's it for now. Fin.

# Notes

[0] Ethereum could benefit from adding more support for crypto primitives to contracts.

[1] https://bitcoin.stackexchange.com/questions/38351/ecdsa-v-r-s-what-is-v 

[2] You don’t need to freak out at me dropping yet another constant. The value of v is almost always 27 or 28.

[3] Auditing protocols for proof-of-retrievability are also trivial on Ethereum. You can use a server to implement audits with a Merkle tree (credits Storj) or a version that doesn't require a server could utilize a [Timechain](http://roberts.pm/timechain) to do public audits over-time without the need for a party to be trusted for audits.

[4] Both sides need to commit to their public keys as hashes that are exchanged before they exchange keys otherwise one of them can use ECC addition to create a public key that results in a private key they already have. I left that step out for simplicity but its not too difficult.

