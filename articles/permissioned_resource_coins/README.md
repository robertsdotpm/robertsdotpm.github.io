## The problem with resource coins

Resource coins like Storj and Filecoin are systems that let anyone contribute their spare computational resources in exchange for payment. By using Storj, a person can lease out their extra hard drive space to other people and receive Storjcoins in return (a kind of special "utility token" in quotes...) The idea is very cool, but functionally it has problems.

The most significant problem, in my opinion, is the way in which these systems are designed to let anyone sell resources on an open market. At first impression, this might sound like an excellent property to have for a decentralized network, but in this particular context, it means that anyone can sell resources even if there is little demand for it.

In a cryptoeconomic system, this property is highly undesirable as an unregulated supply of storage space will cause wild fluctuations in the price of storage space on the network that effects all parties. In a worst-case scenario, offering resources would be unprofitable without cheating, and thus there would be no incentive to play by the rules.

If you take a regular "token" (with its wild swings from speculation) and add in an extra greed multiplier where everyone only wants to sell instead of buy. It becomes like a tragedy of the commons: there is a shared resource that is incentivized - reliable storage space - and that shared resource is important to maintain for future business. But if everyone only acts in their own self-interest there won't be enough of an incentive for quality.

## Permissioned resource coins

After thinking about some of these problems I believe there is room for a new kind of resource coin based on permissions instead of open markets. The idea behind a permissioned resource coin is that instead of listing resource contracts on an open market, you would alternatively program the coin to decide the conditions for who can sell storage space for coins.

### Generating an initial white list

To setup the resource coin, one must generate an initial list of hosts who will provide storage space on the network. These hosts, called "farmers" will consist of individuals who have verified identities via Civic and who have deposited a certain amount of resource coins to use as collateral.

This helps to prevent wealthy people from compromising the system and improves decentralization, but any other algorithm can also be used [insert your favorite proof-of-stake algorithm here] to generate the initial list.

```
bonds = {}
farmers = [
    {"pub": "alice", "gb": 10},
    {"pub": "bob", "gb": 10},
    {"pub": "eve", "gb": 10}, 
    {"pub": "malory", "gb": 10}
]

# Todo: allocate this to a pending pool and
# when pool is large enough -- shuffle it randomly
# before adding to farmers to prevent Google attacks
def add_new_farmer(pub, gb):
    if bonds[pub] < 100:
        return

    info = {
        "pub": pub,
        "gb": gb
    }
    
    farmers.append(info)
```
    
The registration algorithm can be as simple or as complex as needed. Here, the code is checking to see that a new farmer has made a large enough collateral deposit to impose higher costs on Sybil attacks. A more complicated function would allow farmers to increase the amount of storage space they can advertise based on reputation and other factors.

### Allocating storage space

Consider a simple example with four parties, each with 10 GB of storage space. To buy storage space in this system, a person must purchase resource coins and make a request to the coin for storage space. The coin would then use available storage space in the order defined in the farmer set, and reserve payments between them.

```
price_per_gb = 0.01
swarms = {}
pending = {}
balances = {
    "client": 100
}

# Unbound for loops don't work well on blockchains.
# This will need to be optimised.
def allocate_storage(gb_amount, client, id):
    id = random()
    swarm = []
    for farmer in farmers:
        # How much space can we get from this farmer?
        if farmer["gb"] > gb_amount:
            change = gb_amount
        else:
            change = farmer["gb"]
            
        # Check the client has enough to buy this.
        total_cost = change * price_per_gb
        if balances[client] < total_cost:
            break
            
        # Don't continue if there was no change.
        # We're done here.
        if not change:
            break        
        
        # Apply changes.
        farmer["gb"] -= change
        gb_amount -= change
        balances[client] -= total_cost
        pending[id][farmer] += total_cost
        
        # Record farmer in this resource set.
        swarm.append(farmer)
            
    swarms[id] = swarm
```
    
What I have described above is a simple proof-of-stake system. It is a system that awards storage based on the order in which people join. In a real system, there are other variations that could be used. For example, you could program the allocate_storage function to distribute 75% of the storage space based on join order and the remaining 25% randomly.
        
### Paying for storage space

In our sample coin pseudo-code I introduced an algorithm for allocating storage space, but what is missing from this is a protocol for the clients to use for making payments. In practice, all existing storage-based resource coins start by proving custody of a large amount of data.

The way they do this is to generate a large random number to use as a challenge and then prepend it to a piece of content to hash for an answer. To prove that a host still has the content they must be able to reproduce the same answer hash after given the random number.

While this challenge-response game works well for large amounts of content, for smaller pieces of content it is over-kill. What would be interesting is a storage system for smaller pieces of information such as those produced by sensors and other IoT devices.

Because the data is small, my protocol for this is to randomly sample data and check what farmers still have. The client would sign data under the same pub key and record an ID to identify it. That way they only have to maintain an index of metadata to verify bulk data sent over a unique period.

If you wanted to, you could also set a percentage ratio here to adjust how vital the data is to make it more like UDP. It might be okay if you lose one or two sensor readings along the way but not if you lose all of them!

```
audits = []
priv_key = "private"
pub_key = "pub"
swarm = coin.allocate_storage(20, pub_key)

def random_audit(swarm, pub_key):
    # An hour ago.
    start_time = time() - (60 * 60)
    end_time = start_time + ((60 + 60) * 2)
    
    # Get a list of data signatures for all data sent over the last
    # hour or so. Yes, I'm getting lazy here.
    offset = audits.get_offset_closet_to_start_time(start_time)
    challenges = []
    while 1:
        audit = audits[offset]
        if audit["timestamp"] > end_time:
            break
            
        challenges.append(audit)
        
    # Audit swarm nodes for data pieces.
    for farmer in swarm:
        responses = farmer.retrieve(start_time, end_time)
        if len(responses) < len(challenges) * 0.8:
            farmer.micro_payment = "break"
        else:
            total_correct = 0
            for response in responses:
                if response not in challenges:
                    continue
                
                if not valid_sig(response, pub_key):
                    continue
                    
            if total_correct <= len(challenges) * 0.8:
                farmer.micro_payment = "break"
    
def store_content(content, priv_key, swarm):
    for farmer in swarm:
        # Skip this farmer.
        if farmer.micro_payment == "broken":
            continue
    
        # Sign the data chunk
        nonce = random()
        sig = sign(chunk, nonce, priv_key)
        
        # Pay the farmer for this 
        farmer.store(chunk, sig)
        
        # Record meta data
        timestamp = time()
        audits.append({"timestamp": timestamp, "nonce": nonce, "sig": sig})
```

The payment protocol then follows a basic micropayment channel: if a farmer fails an audit, the client closes their channel. Likewise, if a client fails to make payment, the farmer drops storing data for that client. Either side can close the channel at any time to unlock their pending balances (storage space or reserved payment in micropayment channels.)

### Optimising data usage

To avoid having many transactions for every storage request, it is recommended that storage be reserved in bulk. To help prevent malicious clients from reserving large amounts of storage space - the coin can optimize its code to distribute smaller amounts of space between farmers in the defined order or limit the amount based on a users reputation.

It is possible to optimise payment channels and storage requests too. Instead of multiple payment channels to individual farmers, payments can be made to a swarm identifier, where settlement would allocate the payments between farmers in a given swarm. Replication is slightly tricky though and more thought needs to be put into how to do this efficiently.

### Adjusting for price fluctuations

By allocating storage space between farmers in a programmable resource coin, it solves the supply problem. But that is still only half of the solution. What good is it to control the supply if you still have a coin whose value fluctuates in price for storage space? A complete solution would therefore need to use a USD-peg... here's how that might work:

1. An oracle signs a USD quote for 1 resource coins every N blocks.
2. A payment channel is initialized using the most recent quote as reference.
3. Every update to the micropayment channel must use the most recent quote, if it falls behind the payment channel must be closed.

The way to accomplish this is to use bidirectional micropayment channels. What will happen is the amount sent in channels will be adjusted based on fluctuations to the USD quote price, to maintain a stable USD ratio for storage space cost. Thus, the debt that a side owes may be increased or decreased based on changes to the price of the underlying coin.

### Supply need not equal demand

Currently, all of our farmers have to keep their infrastructure online the entire time even when there's no one using their services; Infrastructure needs electricity to run, and electricity costs money. A more efficient algorithm would be to periodically calculate a set of farmers who should keep their equipment online to meet future demand.

Instead of distributing contracts to farmers in order, we might instead use an algorithm to keep track of the current demand for storage space and double our expected supply. Supply would then come from farmers in order, but it would be allocated randomly in allocate_storage. Farmers who are chosen would then have an incentive to stay online which should also create enough leeway to meet any future surges in demand.

Thus, the full algorithm would resemble difficulty adjustments made in a regular blockchain. A small tweak is to have auctions, but only between farmers who were chosen for a given period. That way, there would still be some price competition, but not enough to drive prices into the ground.

### Fixing the price of storage space

One interesting possibility for a permissioned resource coin is to simulate the operations of a normal business on top of a completely decentralized structure. Currently, market-based resource coins cannot do this because every participant in the market independently acts in their own best interests instead of with respect to the needs of the whole.

A permissioned resource coin could be run by a group of shareholders who make decisions on how to set prices. The effect is a structure that much more closely resembles how businesses are run in the real world. That is - it is far easier to control and align incentives when this is built directly into the currency than with relying on an unpredictable market to organise itself.

### Stagnant farmers quitting

Overtime, it is likely that people are going to stop being farmers leading to offline farmers being allocated the right to store content. If too many of these farmers fill up the supply pool the service will become unusable.

To prevent this: if a farmer doesn't commit a heat beat every so often it be removed from the supply set in the future. Obviously this doesn't stop malicious nodes from trying to pull off DDoS attacks but the allocated swarm should be structured around trying to prevent this as much as possible.

E.g. a hybrid approach that consists of storage allocated from a set of authoritative nodes + storage from a set of sequential farmers + a randomly allocated set from the total supply could be more secure than storing everything on one type of farmer - with or without redundancy. 

### Private permissioned resource coins

Potentially an organisation could have an access list in place for users making payments to farmers on the network. This would give the public the ability to contribute resources towards creating a decentralised storage system while ensuring that only certain users can store content.

In the future, this may be a better way to do an ICO as the organisation building software that runs on a permissioned resource coin would have to depend on its users for operation. [This is in contrast](http://roberts.pm/survivability) to existing coins that have no other purpose than to serve [as economic hacks](http://roberts.pm/ico_crapcoin_checklist) to raise money and keep value artificially locked in an ecosystem.

# How does this compare to existing systems?
### Or "why not just use [buzzword]" edition?

**IPFS** - Not actually a decentralized storage system and cannot store content. People only think it can because the IPFS team run caching servers to improve accessibility, but old content is still deleted from those servers when they run out of space or if the devs ever feel like it.

**Factom** - Not a decentralized storage system either. It only does data integrity checks / blockchain timestamping of documents. 

**Filecoin, Storj, Sia, Swarm, Bluezelle, etc** - Have no way to maintain a stable value for payments made for storage space using their coin. Without a stable coin value versus available resources within the system - it will be unprofitable to contribute resources to the network and thus unreliable to use as a decentralized storage system. 

**IOTA** - Uses an immutable ledger which isn't needed for the vast majority of small readings made from sensors. IOTA creates additional problems for IoT too because it requires PoW to be done on every individual transaction and in IoT systems an edge gateway is often required which could become a bottleneck when dealing with many small transactions.

**DHTs** - Cannot guarantee data availability for unpopular content and without incentives it would be unreliable to use at scale. DHTs also suffer from multiple attacks that are easy to do with only minor resources.

**Tahoe-LAFS** - Given that incentives in Storj, Filecoin, etc are broken Tahoe-LAFS and Storj are functionally equivalent since resources in the network end up being maintained more by community spirit than by any solid economic reasons. Needless to say, this won't scale to any sizeable volume as infrastructure costs money in the real world to maintain.

**Bittorrent** - Distributes data to peers in chunks and maintains a list of nodes for content. Anyone who has ever tried to download a torrent without seeders will already know Bittorrent doesn't work as a reliable, decentralized data storage system.

**JoyStream** - Uses a pricing function to reward hosts for maintaining content based on its rareness in the system. My approach is about maintaining storage resources in a network and is not content-specific.

**Freenet, Tor, etc** - Freenet provides storage while Tor only offers anonymous routing. Like Bittorrent, the public nature of these networks has lead to a very poor user experience (slow speeds, unreachable servers) and thus perfectly demonstrates the role of economics in network maintenance.

**Maidsafe** - Comes the closet to describing this system. Like Storj, Maidsafe ranks farmers based on their performance against a set of criteria like availability, latency, capacity, and reliability, and distributes coins in response to demand for resources. But the difference here is subtle - the coin is randomly rewarded even for valid service which needlessly wastes resources, and doesn't offer any way to maintain stable pricing.

# What about Sybil attacks, 'Google attacks', malicious oracles, eclipse attacks, and so?

It is likely that the full algorithm will need tweaks to improve decentralization and remove needless points of failure. E.g. A prediction market might be used to improve the pricing oracle and a more sophisticated, network-based random audit protocol with proof-of-space time for replication might lead to more secure resource verification. I leave this as a basic blue print for now.

### Conclusion

* Resource coins allow spare computational resources to be shared.
* Permissioned resource coins can be used to allocate resources more efficiently and solve several problems with existing market-based coins.
* Permissioned does not mean that the system is less open to participation than a market-based coin but that participation is more tightly controlled.
* The result is a system that better meets the needs of those who use it and which is hopefully more reliable than a market-based coin .