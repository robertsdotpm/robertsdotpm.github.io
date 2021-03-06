Algorithms like merkle trees and bloom filters allow you to take a group of data items and construct a unique fingerprint of the set. The fingerprint can then be used as a way to prove that some or all members in the group are part of that "fingerprint."

These constructs are useful and you seem them used a lot in blockchains. The issue for me with these data structures is really the size of the meta data relative to the set size: Bloom filters suck because they only compress the original data like 30%; Merkle trees suck because you still need to retain a shit-load of meta-data to be able to construct proofs.

I think in some situations you might already have a large list of candidates and you want to compactly see if a candidate is in a set. I've been thinking about this problem today and I actually think that hashcash might be the solution here. The idea would be to generate an IV that constituted a valid proof-of-work for **multiple** items in succession. By comparing candidates in different lists to the IV (and subsequent PoWs generated by hashing) one could rapidly eliminate large lists of candidates and it would be vastly more compressible than both bloom filters and merkle trees. 

Here's how the algorithm looks in Python (emitting some helper functions for brevity):
```
def shared_pow(hash_list, target=4, max_val=4294967295):
    word_list = []
    for pair_offset in range(0, int(len(hash_list) / 2)):
        word = sha1(hash_list[pair_offset * 2] + hash_list[(pair_offset * 2) + 1]).digest()
        word_list.append(word)
    
    found = 0
    for nonce_int in range(0, max_val):
        nonce_bits    = Bits(intVal=nonce_int)
        nonce_bytes = to_bytes(nonce_bits)
        
        # Initial PoW.
        pow1           = sha1(nonce_bytes + word_list[0]).digest()
        pow1_bits   = Bits(rawbytes=pow1)
        if int(pow1_bits[:target]):
            continue
            
        # Every word after the first.
        fail = 0
        for word in word_list[1:]:
            pow2           = sha1(pow1 + word).digest()
            pow2_bits   = Bits(rawbytes=pow2)
            if int(pow2_bits[:target]):
                fail = 1
                break
                
            pow1 = pow2
        
        # Fail or not
        if fail:
            continue
                
        found = nonce_int
        break
        
    if found:
        print("Nonce found!", found)
    else:
        print("Nonce not found :(")
        

hash_list = gen_rand_hashes(4)
shared_pow(hash_list, target=8)
```

Imagine you have only 4 bytes and you want to encode the membership of 4 items from 4 large fixed lists of candidates. It would be very difficult to solve this problem. A 1 byte hash per member isn't enough data to reduce false positives. 4 bytes is not enough for a merkle tree. You could do a bloom filter with individual bits but there would still be false positives. Potentially the joint hashcash approach has the potential to bind together a list of data elements.

One possible use-case for this is in compression. Use it to eliminate candidates in a set by binding together random data with IVs that result in exceptionally high difficulty PoWs for their shared data members. There's 4.2 billion combinations in just 4 bytes. You can vary the number of data members if you exhaust all possibilities or increment every data member by 1 bit... and so on. It's interesting to think about.

Could an algorithm like this be used for general-purpose data compression? I don't know but I intend to find out. 

