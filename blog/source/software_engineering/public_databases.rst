Public access databases
=========================

Most online systems require logins to prevent abuse, since anonymous access often leads to:

- email = spamming
- web hosting = malware
- IRC = botnet control
- pastebins = leaked credentials
- proxies = attacks

Because resources cost money, systems usually lock access and bill usage. Still, some systems choose to buck this trend, and the results can be quite interesting.

The magic of image boards
----------------------------

Image boards are a notable example of a successful anonymous system:

- Anyone can post without registration
- New threads appear first
- Replies move a thread back to the top
- Threads past a maximum limit are deleted

Deleting threads beyond the active limit automatically removes problematic content, reducing moderator burden. As a site grows, old threads disappear faster, making moderation easier. This simple “bumping” strategy scales very well.

Distributed hash tables
-------------------------

Distributed hash tables (DHTs) resemble image boards in key ways:

- Anyone can store values without registration
- Popular values persist longer, while unpopular ones expire
- Persistence depends on node activity and data popularity; storage is not guaranteed

DHTs are useful because programs can use them without captchas. However, guaranteeing persistence is difficult: node count, churn, and data popularity all affect lifespan, making DHTs unreliable for software that requires consistent information availability.

A hybrid approach would combine the pruning properties of image boards with the openness of a DHT. This would allow any program to store key-value pairs that persist while setting an upper bound on resource usage through expiratory bumping. This would be interesting as very few
public access storage systems for software exist on the Internet.

Public access KVS
-------------------

A simple public access key-value store could function with the following rules:

- Anyone can store key-value pairs without registration
- There is a maximum number of key-values per IP
- The oldest last-updated key-value past the limit is removed
- Key-values can be updated to refresh their expiry and associated IP

Keys are handled on a first-come, first-serve basis, with ownership tied to an ECDSA public key. The maximum number of allowed key-values per IP is based on the IP version. For IPv4, the address space is easy to accommodate. For IPv6, compromises are necessary:

.. code::

    global_main_limit (32 bits) -- over-allocated
    global_extra_limit (16 bits) -- over-allocated
            |
            |
            LAN_ID_limit (16 bits) -- 15000
                    |
                    |
                    interface_ID_limit (64 bits) -- 20

The global main and global extra portions form the root of the "tree." The root may have LAN_ID_limit branches, each of which can have interface_ID_limit child branches. We do not try to reserve space for every value in the first 48 bits because there would be too many. Since keys expire, space is continually freed for new entries.

Handling capacity limits
--------------------------

Many Internet users have dynamic IPs, so key-values need to migrate from one IP to another. The challenge arises when an IP's capacity is already full and the IPs owner changes. The new IP owner would otherwise inherit a fully used state.

Allowing the new owner to bump old values could work, but IPs can change at any time. Without precautions, key-values could be deleted before the original owner has a chance to migrate them. The solution is to split IP capacity into two portions:

- **Portion A (protected zone):** enforces a minimum lifespan, giving key-values time to migrate
- **Portion B (bumpable zone):** no minimum lifespan; keys can be bumped once the limit is reached

As space becomes available in Portion A, Portion B values are promoted to Portion A. This ensures new IP owners can write, while protecting key-values during IP churn.

Putting it all together
-------------------------

I've built a demo that implements everything except Portion B logic. It consists of a client anyone can use and I host the server. The package is available on PyPI.

.. code::

    # install: python3 -m pip install namebump
    # Note: I made the mistake of using timeouts too low so it
    # may time out if the network is slow. Will fix at a later date.

    import namebump
    import asyncio

    async def main():
        # Generate your public key
        kp = namebump.Keypair.generate()

        # Save a value at a unique name (must be unique)
        await namebump.put("your unique name", "value", kp)

        # Retrieve the value
        value = await namebump.get("your unique name")

        # Delete the value
        await namebump.delete("your unique name", kp)

    asyncio.run(main())

You can find the project on `GitHub <https://github.com/robertsdotpm/namebump>`_.

Future work
-------------

One may incentivise the preservation of resources by making Portion A expiry a function of usage. For example - less usage will grant higher expiry to values, dropping as
capacity is used.

I am sure there are better ways to structure this system. In general: the idea of
public access computing seems unexplored because of the perceived difficulty in
moderation and the human instinct to monetize everything. But this might not apply
for all things.

Maybe there are new public access systems to invent.