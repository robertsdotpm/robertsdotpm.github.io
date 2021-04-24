Article super seeded by: https://roberts.pm/index.php?p=article&path=rand&category=cryptography

For the past 3 months I've been working on designing an algorithm to compress random data. Read on to learn about the extreme difficulty in solving this problem and why my work eventually lead me to building a small super computer to assist in finding a solution.

# Why compress random data?

Existing compression algorithms are based on finding simple patterns in data or reducing the quality of a message. They cannot handle random data. What this means in practice is in order to squeeze more content down the tubes (the Internet is a series of tubes) companies like YouTube and Netflix reduce the quality of their content to compensate.

You can only use this approach so many times before the content starts to look too shitty for your customers to enjoy. The obvious downside is this approach involves **loss.** You're not enjoying how the message originally looked. It's been warped to fit your connection.

A compressor for random data would be useful because it would work on all kinds of data without loss. Basically producing two main benefits:

1. Faster Internet speed
2. More hard drive space

Alternatively, you could state this as **more efficient use of resources...** potentially allowing for new use-cases on both high-speed and low-speed networks. Random data compression is truly the holy grail. But like the holy grail: a general approach to attaining it has not been found yet.

# The power of entropy

To start to understand why this problem is so difficult it's useful to look at how much information can fit in a mere 8 bytes:

``` python
2 ** (8 * 8) = 18446744073709551616
```

After only 8 bytes the number of different combinations that can fit are in the million-trillion range. It's clear to me that an algorithm that attempts to use brute force to wholly recover data won't ever finish in our life-time.

Normally an observation like this would be enough to steer to me away from ever trying to attempt something as crazy as compressing entropy. But there is one compelling data structure that changed my mind. It's called a golomb-coded set (GCS), and what it does is quite interesting.

A GCS is a probabilistic data structure that can tell you for certain if an item is not in a set or if an item **might** be in a set. The 'might' part implies there are false positives for set inclusion but not set exclusion. 

``` python
PROB = 1024 # false positive rate as power of 2
CHUNK_SIZE_BITS = 17
AVG_BLOOM_POSITIVES = (2 ** CHUNK_SIZE_BITS) * (1.0 / PROB) # 128
```

Golomb-coded sets are highly compressible so the output size is less than the input size. The problem is there's presently no known way to reproduce the input data from a given set. **What's remarkable about this is how close a GCS already 'feels' like a workable solution.** If only there was a way to reconstruct the input data... A list of clues.

# The entropy treasure hunt

Imagine you have 1 KB of cryptographically random data that you want to compress with a GCS. Storing it all as-is will not work because there are too many potential combinations for a bit string that long. Instead, the data should be split up into chunks and stored with its chunk number. Next, you need to determine the right parameters for the GCS.

**Balance is everything**

As the false positive rate is reduced the size of the GCS increases and there will be less space left over to store clues to reconstruct the input data. If you decide to make the false positive rate too low the number of candidates to check will become prohibitive and the algorithm will never finish.

There are similar issues with the choice of how large the chunks should be. If they're too small then you end up storing too many hashes in the GCS and bloating the set size. But if the word size is too large then the number of candidates per chunk will be too large to check and you won't be able to reduce them later on.

The parameters I've found that work best are a **17 bit** chunk size with a false positive rate of **1024.** The minimum amount of compressible data is also influenced by these parameters. A lower false positive rate makes the filter more 'efficient' (less accurate) so you can compress smaller amounts of data. The minimum size for my parameters is **1024 bytes.**

# Planning the treasure hunt

GCS feel magical but they don't give us much to work with regarding compression.

``` python
GCS_LEN = 706
WORD_NO = 482
SPACE_LEFT = 318
```

Somehow a scheme needs to fit inside less than 318 bytes that allows for a sequence of 482 random 17 bit words to be reconstructed from a 706 byte GCS. My approach is probably not what you will expect.

First: every word will have an average of 128 candidates and among those candidates will be the original word. Brute forcing every possible candidate for every word is feasible since the word size is only 17 bits -- a single core on one of todays processors won't take more than a few minutes to build one list of candidates.

Within the candidate list there will be a fixed offset to our target word. The offset will never change because the GCS set will never change. The challenge is to find the right offset in each of the 482 candidate lists. I have tried many different approaches to do this but they have all failed.

* Bit filters -- don't work because there's too many false positives and they use up all the remaining space
* Merkle trees -- no room to store the merkle tree
* Checksums -- no room to store enough of the checksum to matter
* Offsets -- there's only 318 bytes left and 482 offsets are needed

I was going to give up here but then I started wondering if there were any existing solutions that might be comparable (even if just metaphorically.) The problem statement could be written as **having a list of possibilities vs a list of choices and discriminating against the choices.**

There's a famous program that already does this... but only for one number. Bitcoin. We can conceive of proof-of-work as discriminating against a set of one using a special nonce that when combined with the set has an exceedingly low probability of occurring with anything else.

The nonce is like driving a stake through a distribution and pinning down the candidate that you want. Well... roughly, anyway.

# Constructing the nonces

In order for the nonces to fit into the remaining space and still produce a surplus the right parameters need to be chosen for their construction. I'll introduce the following definitions and values:

1. If there is a fixed offset in a list of unique candidates for every word let this offset be called a **node**.
2. A pair of two nodes will be called an **edge**.
3. Every edge will have a hash that contains the pair and their offsets.
4. Each nonce operates over a set of **4 edge hashes.**
5. Thus, there are 61 sets and 241 edge hashes in total.
6. Each set has a corresponding clue / 4 byte nonce / proof-of-work.

Using this approach it's possible to reduce literally billions of combinations per set to less than or equal to 1. I think that's stunningly efficient for a 4 byte discriminator... and we're down to **77 bytes remaining.**

The code I use to generate the nonces is not that different to standard hashcash. The main difference is I hash over a chain of hashes, total the complexity of any zero prefix bits, and sort by complexity overall. If the complexity is too low the nonce is not strong enough to discriminate against the edge hashes and I throw it away. Github link at the end.

Note: I've emitted the calculations here for filtering sets -- they are quite lengthy and there are other points to cover first.

# Following the clues

The length of the candidate lists must be determined by brute forcing the GCS. From there every possible edge hash can be constructed and compared against the nonce clue for its complexity.

It's not that simple though.

```
CHKSUM_BITS = 1
AVG_BLOOM_POSITIVES = AVG_BLOOM_POSITIVES * (1.0 / (2 ** CHKSUM_BITS)) 
AVG_EDGE_CANDIDATES = AVG_BLOOM_POSITIVES ** 2 # 4096
AVG_SET_CANDIDATES = AVG_EDGE_CANDIDATES ** 4
281474976710656
```

Trying every possible edge hash has over 1000 billion combinations. Since that is more than the entire search space of a nonce puzzle (2 ** 32) you will end up finding 'better' candidates than our set of edge hashes making the puzzle incomplete.

There are some ways to vastly reduce the number of possibilities checked. Because we're doing PoW over a sequence, we know the number of prefix bits to expect at certain edge hashes for 'good' nonces. Such information can form a heuristic for a basic filter -- cutting through 1000+ BILLION candidates with a total search time of less than 40 minutes (168 cores.)

The other thing you might notice is I ended up adding a bit filter  taking down the remaining bytes (482 / 8) from 60 to 17. The question I now ask is: **what combination of heuristics will enable us to store the set offset in the remaining space after applying the clue filter to the edge hash candidates?**

The barrier that's stopping me from answering this question at the moment is **speed.** My code needs access to a general-purpose CPU compute cluster to apply the puzzles as filters. To verify the results (and prove that set recovery is possible) I'll need to build a larger cluster. I have an order for some some servers that will be arriving soon and these will hopefully add enough processing power to get me my data.

In the mean time: I've been able to successfully recover the first set (slicing through 1000+ billion combinations in minutes!) I also started to work on the general heuristic for sorting the sets and I don't see anything to indicate that  this approach won't be viable (although I may be wrong.) For now I'm going to release my current code and leave you with some obvious low hanging improvements to the current work.

# Nonce compression is very easy

The algorithm records the best possible puzzles that match certain criteria. On average this requires trying a large number of nonces before a solution is found. I have found that its very easy to compress the nonces based on size as they're all in a similar range:

1. Determine the smallest nonce, q.
2. Divide all numbers by q and record max number of bits needed, q_b.
3. Record max number of bits for the remainder of n % q, r_b
4. Store 1 byte field: 4 bits to describe r_b size, and 4 for q_b size.
5. Store entirety of q proceeding it.
6. Pack all numbers into bit fields based on q_b and r_b sizes.

I was able to save 14 bits per nonce saving a massive 106 bytes overall. If the scheme is adjusted to avoid using the bit string previously (possibly by making a trade off in run time) -- then it would add an additional 60 bytes. Or 183 bytes remaining. That should be more than enough to express set offsets in the clue filtering stage assuming ~15 bits per offset.

I think with access to more computing power (so I'm not waiting days for my experiments) I'll be able to prove this works with more certainty. Here's my current prototype code. It's very messy but demonstrates my overall concept: 

https://github.com/robertsdotpm/rand
