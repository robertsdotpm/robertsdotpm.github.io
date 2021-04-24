- Random data compression code at http://github.com/robertsdotpm/rand
- Code and paper by <matthew@roberts.pm>
- Version 0.3.0

# Introduction

I've been working on a method to compress random data and I think I may have something interesting to share now. I didn't expect to make much progress on this problem because I've been told it's impossible. But after dividing the problem into manageable pieces, I feel ready to share my solution with you all.

On a high-level part of what makes compressing random data, a difficult problem is how poorly equipped mathematics seems to be when it comes to randomness. Most of the time you can think of math as dealing with concrete, defined objects. That's what makes it a great tool for understanding the universe but a poor one for generating randomness.

What this means for the data compression problem is intuitively a solution is likely to rely more on brute force than any well-defined, mathematical process. Some people may even go further and argue that random data compression is not just mathematically questionable but a logical impossibility altogether. To make their case they like to present two main arguments:

1. If random data compression were possible you could compress any data infinitely down to a single bit.
2. If compression maps big inputs to small outputs. The algorithm also can't map small inputs.

Argument 1 is an example of 'reductio ad absurdum' or argument to absurdity [rational-wiki]. It can be refuted by saying that any solution needs to have a minimum input size -- after which outputs become larger than inputs. While argument 2 is about data representation on the bit-level. It states that if there is N bits to store data, there must also be at least N bits worth of data to represent it -- meaning not all values can be compressed.

The algorithm in this paper avoids the problems with argument 2 by storing information in such a way that multiple values can share the same representation. If one were to try do this with only bits it wouldn't work. But the way this is accomplished is through a special cryptographic data structure called a golomb set. This data structure and much more will be introduced through the rest of the paper.

# Golomb-trees

**Throughout this paper I will be mentioning various parameters that come from nowhere. I will not attempt to explain these values other than to say they were painstakingly determined by trial and error.**

The algorithm builds on a remarkable data structure called a golomb-coded set (GCS) [gcs-info]. A GCS is kind of like a bloom filter in that it allows one to know with absolute certainty if an item is *not* in a set or if an item *may* be in a set. What makes a GCS special over a bloom filter is its size: a GCS is around 44% smaller than a bloom filter. It's so small in fact, that when you end up putting information inside the set the results practically resemble random data compression already -- that is -- if there were a way to retrieve the data!

The focus of this paper is on designing an algorithm that can recover information from a GCS using highly compact, cryptographic puzzles. Such a scheme allows multiple values to be **'stored' inside a GCS in a super-position of values within the same amount of space.** Such a property makes it possible to avoid the problems that arise from trying to store a large amount of values in a small amount of space using binary directly [counting-argument]. The scheme is fully lossless and has a very modest compression saving of 0.68% (translates to a mere 7 bytes / 1024 KB saved.)

https://github.com/robertsdotpm/rand/blob/master/utils.py -- see buf_to_chunks().

The algorithm is designed to operate on 1 KB buffers which are split into **484~ 17 bit words** and stored in the GCS together with their offset. Suppose one were to test the GCS for every possible 17 bit value prefixed by a given word offset. One would end up with a list of false positives based on the parameters chosen for the GCS accuracy (1024.) Among the list of **candidates** would always be an offset to the correct word you were looking for -- the word in the candidate set as seen in the GCS. Do this for every word and you'll have a list of **candidate lists** together with a list of **numbers for how many candidates are in each list.** You'll also have a list of the correct offsets, of course, which we'll call the **node list** for short.

To reduce the number of false positives by half, the algorithm makes use of a bit string that samples the first bit expected in each word hash that is 'added' to the GCS. The word hashes starting bit is compared to the bit string and skipped if it doesn't match. The amount of space to store this metadata versus the exponential rise in set size without it was found to be more favorable than resolving the problem by other means. Such a bit filtering string is part of the many elements that make this algorithm possible.

![brute force](articles/rand/brute_force.png)

There isn't presently a way to encode the node list in a way that is addressable in the bytes that remain. The GCS takes up **706 bytes** which leaves just **318 bytes** to encode a list of **484 offsets.** On average the number of positives in this GCS will be **128**, meaning sometimes 8 bits will be required to store a node offset if it's above average. Sometimes less. That makes serialization and deserialization an issue. Requiring 8 bits for every offset is wasteful, yet the only alternative would be to flag which columns are 7 and which aren't -- a solution that also adds an extra bit of overhead. There isn't enough space to encode the offsets directly by standard means.

https://github.com/robertsdotpm/rand/blob/master/golomb_sets.py [gcs-parent-repo]

# Culumlative proof-of-work

Rather than deal with a full list of node offsets the algorithm works with sets that contain four pairs.

```python
q_set = [[node_a, node_b], [node_c, node_d], [node_e, node_f], [node_g, node_h]]
edge = [node_x, node_y]
```

These sets are called **'q sets'** and the pairs inside them are called **'edges'**. Edges consist of two **'nodes'**. Each node is an offset into a given candidate list for a single word (previously gained from brute-forcing the GCS for each word.)

The edges are hashed to form a list of **four edge hashes**. The hash algorithm chosen is xxhash128 because this algorithm makes use of hashing repeatedly and similar hash functions provide such a poor source of randomness as to be completely useless. Edge hashes need to be prefixed with their position relative to other edge hashes. This avoids collisions in later work.

```python
H = lambda node_x, node_y, abs_offset: return xxh128.digest(b"%d %d %d" % abs_offset, node_x, node_y)
hash_list = [H(edge_zero), H(edge_one), H(edge_two), H(edge_three)]
```

To understand what happens next it's important to revisit proof-of-work (PoW) in Bitcoin so that we may see it in a new light [hashcash][bitcoin]. Most people understand proof-of-work as a measure of computational energy spent trying to find 'nonces' that produce certain prefix patterns in a hash. But another way to look at this is it's a way to find a relationship between two random numbers.

As it stands, the relationship in Bitcoin is between an important number (the block header), and an unimportant garbage value (the nonce.) The individual numbers there don't matter as much as the result. It so happens that you could use this approach to relate a list of numbers [by a nonce] that you do care about.

The mind-blowing part about using PoW in this way is that it can be used cumulatively, to form a chain of heuristic filters. The filters can then be used to recover a small list of q set candidates from among trillions of possibilities. Once you have that list you only need a 7 bit offset to address the correct q set. 

https://github.com/robertsdotpm/rand/blob/master/shared_pow.py

**What is needed is a PoW function that enforces a pattern, cumulatively, over a list of edge hashes. It should maintain three basic properties at all times:**

1. Fixed -- there are at least 2 zero-bits in the prefix for pow = H(nonce + edge_hash_n).
2. Chained -- there are enough zero-bits in the prefix from H(prev_pow + pow). This criteria only applies for edge_hash 0 - 2 inclusive.
3. Independent -- there are enough zero-bits in the prefix for fingerprint = H(edge_hash_zero, edge_hash_one, edge_hash_two, edge_hash_three). This criteria only applies for edge_hash 3, zero-indexed.
4. Size -- the output set after filtering with this heuristic should stay below a certain threshold. This prevents exponential growth of the result set.

![cpow](articles/rand/cpow.png)

The cumulative proof-of-work algorithm or CPoW function determines what nonce to use by trying every nonce in a 4-byte range and sorting the nonces by output q set size. The filtered set size depends on the heuristic qualities that the nonce produces. A counter is also combined with the nonce due to the need to determine special qualities for the heuristics to be stored as metadata later on.

# Heuristic filtering

The heuristics algorithm is based on converting a q set into a tree and using heuristics gathered at an edge hash together with a nonce to decide on how to traverse the tree. The root starts at edge hash zero. For every value in edge zero, a heuristic is checked (as gathered during the nonce search phase.) If there is a positive match the next branch is 'opened and the process continues to the next heuristic. The algorithm moves back a branch if it fails a check or has run out of branches to try.

The heuristics algorithm only opens a new branch if they match the cumulative heuristic checks at that edge. Such a process ameliorates the need to have to test billions of combinations of q sets and allows for rapid traversal of the tree.

![heuristics](articles/rand/heuristics.png)

After running the heuristic algorithm, what remains is a result set no larger than 128 elements in size. Any larger than this and the corresponding nonce is considered invalid (as 7 bits is the max size available to store the final offset of the 'correct' q set to be encoded.) Presently, the only optimization being done is having checks at edge zero outsourced to an independent core in a cluster. The rest of the algorithm has not been optimized.

https://github.com/robertsdotpm/rand/blob/master/ex_filter.py

# Heuristic encoding

For this solution to work, a huge amount of metadata still needs to be encoded. There is the four-byte nonce created for each of the 61 q sets and its associated list of heuristic information for each q set edge hash (nonce prefix counts.) All of this as raw data far exceeds the remaining space. Fortunately, there are fairly standard coding schemes that can be used to solve this problem.

# Encoding scheme

The heavy use of hash functions in PoW helps to ensure the distribution of nonces is random. But in practice, this property is not guaranteed since the randomness received depends on the number of nonces sampled. Just as if you were to flip a fair coin only a handful of times so too can bias creep into a list of nonce samples. Another way bias can creep in is to intentionally introduce it -- keep a list of hashes that satisfy the conditions needed for the program and choose nonces such that they become closer together.

![random nonces](articles/rand/random_nonces.png)

What my sample of 'good' nonces looked like.

When bias shows up for nonce lists (and in the real world it does), it can be fed to a compression algorithm and its size reduced. The approach taken for nonce compression is to use golomb-rice codes [golomb-rice]. Sort the nonces in ascending order and divide all the nonces by the smallest value. Store the quotient and remainders. Golomb-rice coding uses the average of all the numbers for the divisor. But the smallest number can also be used. Using this approach makes it possible to take a 4-byte nonce and store it as a 9-bit quotient + 9-bit remainder -- with an extra 4 bytes stored for the divisor.

Overall you save around 13 bits per nonce, with 1 extra dedicated to storing the divisor. 

**Bits 0 - 5 inclusive -- heuristic table a offset**

The prefix bit heuristic information for edges 0 - 1 is mapped to a fixed, 32 element table. The table contains the most common heuristic pairs for edges 0 - 1 inclusive. Bits 0 - 5 then store the offset into this table. What this implies is that if no elements exist in the table, then a new nonce needs to be tried.

**Bits 6 - 10 inclusive -- heuristic table b offset**

Same as edge 0 - 1 but generated uniquely for edges 2 - 3 inclusive. There are five bits here. Unfortunately, 4 bits need to come from the end of the nonce itself. Nonces that don't correspond to having an ending sequence of bits that matches up with the offset into the second prefix table will be skipped.

In tests, there's around a 1 / 20 chance of finding nonces using the standard process that overlaps with both frequency tables. That means it will be around 16 times more difficult to find one (1 / 320) that also encodes a partial offset in its ending bits. This makes the algorithm much slower to run -- but importantly -- not impossible to run on a small cluster!

https://github.com/robertsdotpm/rand/blob/master/ex_create_nonces2.py

![meta data encoding](articles/rand/encoding.png)

**Remaining space 7 bits -- q set offset**

The last 7 bits per q set are used to encode the absolute offset in the filtered result set after running the heuristic algorithm. It maps directly to the correct q set among the q set candidate list. Its maximum value is 127.

![file format](articles/rand/file_format.png)

# Checksums and finding the perfect nonce

An integer starting at 0 is incremented each time the full 4-byte nonce range is searched. This integer is NOT included in the final data format. That means that it will be up to the decoder to figure out what value to use. 

To determine its value consider that the q set offset ends up pointing to a specific quad set. The quad set is converted to an edge hash list and the value of i being checked is passed to the nonce search algorithm. This algorithm generates a list of nonces with their heuristic data. If the best nonce and its heuristic data match the metadata then the correct value of i must have been found.

The integer is a key way to solve the problem that arises from running out of nonces to check. Since the value of i is not saved in the data format, the inclusion of i allows each q set to be used uniquely with its own nonce range without taking up extra space in the data format.

Example code: not available yet.

![no selection](articles/rand/no_selection.png)

# Calculations

When testing nonces the potential number of q sets is calculated at each edge hash to ensure the output set hasn't grown too large. The following calculations are used:

```python
PROB = 1024 # Bloom filter false positive rate.
CHKSUM_BITS = 0 # No longer used for anything -- ignore this OwO
CHUNK_SIZE_BITS = 17

# The prefix_no refers to the number of zero bits at the
# fixed, chained, and independent hashing expressions in the CPoW function. 
def calc_set_growth(set_total, prefix_no, chunk_size_bits=CHUNK_SIZE_BITS):
    chained_p = (1.0 / (2 ** prefix_no))
    bloom_positives = (2 ** chunk_size_bits) * (1.0 / PROB)
    bloom_positives = bloom_positives * (1.0 / (2 ** CHKSUM_BITS)) 
    edge_candidates = bloom_positives ** 2
    set_change = (set_total * edge_candidates) * chained_p
    return set_change
    
# Edge zero heuristics
pre = 2
chained = 1
out = calc_set_growth(0, chained) # 0 > round up to 1

# Edge one heuristics
pre = 3
chained = 8
out = calc_set_growth(1, chained) # 64

# Edge two heuristics
pre = 1
chained = 21
out = calc_set_growth(out, chained) # 0.5

# Edge three heuristics
pre = 3
indep = 5
out = calc_set_growth(out, indep) # 256

# This is how zero bit prefixes in CPoW battles entropy
```

# Results

A proof for the algorithm would show that both the heuristics algorithm worked and the metadata could be encoded in the remaining space. In practice it is not necessary to run the full algorithm on a 1 KB buffer since success for a single q set would imply the overall approach works for all 61 q sets.

In the real world, it was possible to use cumulative proof-of-work as a heuristic sorting algorithm for decoding q sets and to store all required metadata in the remaining space. The nonce encoding step was skipped as it would have added an average 16 times longer compute time and the author's cluster only has 240 cores.

Some people may not be satisfied with these conclusions but I encourage them to look at the code and check the results for themselves. It's the opinion of this author that the most popular challenge for compressing random data has been poorly designed because of the excessive file size of the input source (1 MB) and the excessive number of program runs that constitutes a solution [compression-challenge].

Nevertheless, this algorithm should be enough to satisfy the random data compression challenge -- though it would require a larger cluster to run in any reasonable amount of time. Not an issue for a typical university. But certainly an issue for individuals without access to larger clusters.

# References

[rational-wiki] https://rationalwiki.org/wiki/Reductio_ad_absurdum

[counting-argument] http://mattmahoney.net/dc/dce.html#:~:text=The%20counting%20argument%20applies%20to,it%20cannot%20be%20compressed%20again. 

[gcs-info] https://giovanni.bajo.it/post/47119962313/golomb-coded-sets-smaller-than-bloom-filters

[gcs-parent-repo] https://github.com/rasky/gcs

[hashcash] http://hashcash.org/papers/hashcash.pdf

[golomb-rice] Advances in Information Retrieval 36th European Conference on IR Research, ECIR 2014, Amsterdam, The Netherlands, April 13-16, 2014. Proceedings by Maarten de Rijke, Tom Kenter, Arjen P. de Vries, Che; Page 363

[bitcoin] https://bitcoin.org/bitcoin.pdf

[compression-challenge] https://marknelson.us/posts/2012/10/09/the-random-compression-challenge-turns-ten.html

