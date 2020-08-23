The type of work that I do can be divided into two main categories:

1. Proof-of-concepts (PoCs) and
2. Production projects

When I'm working on a proof-of-concept the goal is simply to create a system that demonstrates a certain idea is possible. Usually this will be in the context of working on an idea that hasn't been tried before or demonstrates that a solution to a problem is possible. For a proof-of-concept that is all about novelty: the result of the work is far more important than the means used to produce it.

The flip-side to the PoC are production projects. Most production projects I've worked on are simple variations on tried-and-tested designs where there is already a well-established list of guidelines to follow. The purpose to these guidelines is to ensure the work is of a high quality; Is easy to maintain; And won't cause problems later on. If it's code being created for a software project then it should have good tests; Have easy to understand documentation; And be modular and well-engineered.

It's a common source of confusion for people to look at my work and assume that I intended for a proof-of-concept to be meant as a production project. When I'm working on projects in my own time I like to take risks and have fun with it. I'm a curious person so most of my side projects tend to be proof-of-concepts. These projects are intended to be interesting but they don't include the best development practices! Bellow I'll included a list of some of the projects I've worked on and whether they were designed as PoCs or not.

# Pyp2p

Status: proof-of-concept

[![articles/work/1.png](articles/work/1.png)](articles/work/1.png)

I created Pyp2p as a way to do P2P networking in Python for my decentralised exchange PoC at a time when there were few other options (LibP2P did not exist.) Pyp2p has some novel features that aren't available in similar systems. It supports 'TCP hole punching' which is a way to bypass NATs without setting up port forwarding rules. The most interesting part about Pyp2p is it's address format.

Addresses of nodes in Pyp2p describe many pieces of information about the host network which allows Pyp2p to use multiple techniques to attempt to establish direct connectivity between two peers. It will attempt to first port forward with UPnP or NATPMP. If either side in a connection request have open ports then the connection can proceed. Otherwise Pyp2p has more tricks up its sleeve.

It profiles the type of NAT that peers are behind and then uses that to schedule TCP hole punching between the two peers. By combining different connection strategies it is possible to vastly increase the chances of achieving a direct connection between peers -- even if both are behind strong NATs and firewalls. 

## What I learned

Working on this project I learned in-depth knowledge about networks and how messy P2P networking can be. I had to develop my own methodology for testing complex, time-sensitive protocols. I simulated networks with virtual interfaces and built fully functional simulations of peers behind routers using OpenWRT and VMware ESXi.

## What I would change

I came to this project with a heavy background in BSD sockets and C programming. It gave me a strong bias towards thinking in terms of threads, blocking and non-blocking sockets, and timeouts. In Python because it has the 'global interpreter lock' (GIL.) Every time you do a slow operation it stops the program until that operation has finished. In Python using I/O bound operations is a very, very bad idea and if I were to do this again I'd make everything async.

The other part of the program I'd change is how the hole punching works. You can say the GIL is concurrent but single-core. If you're doing TCP hole-punching the whole process is ridiculously time-sensitive. Instead of trusting the GIL to multi-plex between a bunch of pending operations and starting the hole-punching code at the right time I would instead spawn a new Python process solely dedicated to hole-punching. The new process would occupy its own core and reduce the chances of competition tasks interrupting the scheduling.

It would also avoid slowing down the GIL of the main process by mistake and even allow for the priority of the new process to be increased which would further improve the accuracy of scheduling the hole punching. Finally, if it were a production project and not just a proof-of-concept, I'd completely re-write the code to avoid using god objects and stick to using small, loosely-coupled functions.

# h.h

Status: production project

[![articles/work/2.png](articles/work/2.png)](articles/work/2.png)

Intel processors contain a special feature called an 'enclave' that allows running code that can be isolated from the main host and hardware. The technology is very impressive but it can be quite complicated to use. To take advantage of its main features requires the use of expensive, non-commodity, server processors, and the main software libraries for interacting with these enclaves are written in C++ which comes with problems of their own.

Writing code in C++ wouldn't be a problem but one of the things I wanted was to write software that would be highly portable and run on other platforms. Some of the platforms I had in mind were 32 bit web assembly and mobile apps via native support. To top it off I'd like for my software to provide a good experience for other developers. They should not have to spend 9 hours acquiring dependencies to build my code. 

My solution was to build a single header library in standard C. There would be no C++ and especially no use of the standard template library because web assembly has a hard time compiling this without errors! For those who haven't written much software in C the standard library is bare bones. There are no big numbers, no 'dictionaries' / hash tables, no 'lists', no regex, no JSON... or crypto support, in fact most of what you would expect from a modern language is missing. Still though... C is surprisingly elegant and far more beautiful than people give it credit for. So I added what was missing to the library.

There's a lot of code in the library that was written by other people and either modified for my needs or merged in. It includes code that was released under the public domain and various licenses that were either released under the GPL or are directly compatible with it. There is a credits file at the top of the library that explains exactly who is responsible for what, the license of the original code, and where it came from.

## What I learned

When it comes to learning standard skills like software engineering there are countless books you can refer to. But the more specialised and cutting-edge work, almost research topics, you're left to make sense of everything on your own. I found the learning curve for working with Intel enclaves very steep (though Microsoft certain made it easier to get started with their confidential compute VMs!)

What I found myself doing is reading the Open Enclave SDK like it was a novel. I'd start from the build process and read all the way through to a working example project. Whenever I came across anything I didn't understand I'd go off and learn exactly how that worked. Doing this I'd keep a notebook alongside it that eventually became detailed enough to describe the full system which contained information that wasn't published anywhere else.

Breaking everything down step-by-step eventually allowed me to learn enough to work with the SDK. One of the first things I built was a program that provided signed proof or 'attestations' that it is running inside an enclave. What's cool about this is you can use this to securely exchange data and do computations between programs running on different hosts even if both of them are completely compromised. Such a possibility could lead to fundamental changes in the way we design software. 

```json

{
    "status": "success",
    "enclave": {
        "identity": {
            "id_version": 0,
            "security_version": 1,
            "product_id": 1,
            "unique_id": "162557E2547620EE9272546420F0353ABF0AD13F4F29C721BE8055C73470078C",
            "attributes": "07000000000000000700000000000000",
            "signer_id": "6C1F3D9B1303484398C5F71C118351309111165348311D0D83A5FFA4C0BDBFC1",
            "signer_pub": "..."
        },
        "binary": "/enclave.signed"
    },
    "report": {
        "header": {
            "version": 1,
            "type": "remote",
            "size": 4579
        },
        "quote": {
            "version": 3,
            "sign_type": 2,
            "qe_svn": 2,
            "pce_svn": 7,
            "uuid": "939A7233F79C4CA9940A0DB3957F0607",
            "sig_len": 4143,
            "user_data": "2E974C10644E147715A8A2D59A78BD8400000000"
        },
        "body": {
            "cpusvn": "0D0D0205FF8003000000000000000000",
            "miscselect": 0,
            "isvprodid": 1,
            "isvsvn": 1,
            "attributes": "07000000000000000700000000000000",
            "mrenclave": "162557E2547620EE9272546420F0353ABF0AD13F4F29C721BE8055C73470078C",
            "mrsigner": "6C1F3D9B1303484398C5F71C118351309111165348311D0D83A5FFA4C0BDBFC1",
            "report_data": "rand_nosdfsdf",
            "report_data_sha256": "58D6939DA37987E3E67944D21538E9A35874BC64A3D07C0BD6F3FD83B9EA5F2B0000000000000000000000000000000000000000000000000000000000000000"
        },
        "sig": "...",
        "pubs": "..."
    },
    "hex": "...",
    "notes": "The hex field above contains the raw remote attestation report as returned from oe_get_remote_report from the enclave. Public keys and signatures for the report are from Intel. The enclaves pub key and signature match the authors who produced enclave.signed. The enclave is running smoothly on this host. To verify its operations on the host you would need the verification client, enclave source, claimed enclave binary, and enclave meta data."
}


```

I learned a lot about enclaves but also C++ build toolchains. Plus, how to design simple C software. Overall, it was a highly valuable experience from a security engineering and R & D perspective!

## What I would change

One mistake I made with this header library is not using a unique prefix for the names of functions. What this means is that if someone were to include this library and there were already a function name in their code that shares the same name as a function in the library then they would get compile errors.

It's not a huge problem to fix this but it is a task that seems very tedious. I can't think of a simple way to fix this safely without having to manually rename every function. But maybe there's a tool to match all functions and usages across the whole project and replace them at once. It could well be worth writing a script for this if there's not. Just branch off and see if the code can be patched in place. Stage 1 is finding the definitions. Stage 2 is replacing their usage. A single namespace might actually make this easier.

Other than that I'm honestly happy with this library. There's a bunch of other financial code that I've written that uses this header and thus shares all of the same benefits that I'll be releasing soon too.

# Coinbend

Status: proof-of-concept

[![coinbend exchange demo](http://img.youtube.com/vi/h7maCX8XKbg/0.jpg)](http://www.youtube.com/watch?v=h7maCX8XKbg "Coinbend exchange demo")

In 2013 I started a project to build a 'decentralized exchange' / 'DEX' with an old school friend of mine. The main goal of the project was to develop a trading system for exchanging Bitcoin and other crypto-assets without the need for a third-party to take receipt of deposits - a type of exchange that later became known as 'non-custodial.'

The exchange used quite an interesting way to move money between peers. It incrementally increased the recipient amount by small chunks so that a transfer was broken into many micro-payments. The individual risk of losing funds had a max cost limited to the transfer size. So if one were to transfer chunks less than the cost of transaction fees there would be a very high chance of success in most cases.

There were a few good technical reasons for choosing such an odd design. First, at the time smart contract support across blockchains was patchy so one could not rely on the existence of complex hashing OP_CODES to be available. And second: the Bitcoin blockchain was still very limited in what it considered 'standard transactions.' So even if it did support certain smart contracts there was no guarantee that the 'miners' in the network would accept the transactions and add them to the chain. 

By using micro-payments I was able to use standard transactions that worked on all blockchains. Since at the time the assets people wanted to trade were all forks of Bitcoin -- this was a relatively easy task with the python-bitcoin library. The more difficult task was the lack of support for debugging low-level Bitcoin-style transactions and running into occasional errors into the libraries themselves due to so few people having used them at the time. Having to also write a P2P networking stack from scratch was also a massive task that today developers don't have to worry about due to high quality libraries. There is definitely such a thing as being too early to do something...

## What I learned

Coinbend was like my master class in designing smart contracts. It taught me all about trust and how complex distributed systems are to secure. In many ways the techniques I was learning (and sometimes inventing) have become commonplace. For instance: my conception of 'green addresses' described in the white paper was something I invented to reduce leverage in multi-sig deposits prior to opening a micro-payment channel and now forms the basis for some of the most high-security key-management systems in the blockchain space (like keys.casa). 

The Coinbend project taught me many difficult attack scenarios in distributed systems. I now notice many problems with using 'daps' (most often race conditions.) I learned all about applied cryptography, too - even inventing my own techniques for creating a passive key recovery system (timechains.) 

I had decentralized alt-coin trading working across blockchains at a time when Ethereum didn't even exist yet and Lightning was barely a twinkle in anyone's eyes. In fact, it was probably the first decentralized exchange at the time that was actually trustless.

I might have been the first person to implement cross-chain contracts, too. I've seen many people claim to have done this over the years but there's several good reasons to believe why these claims are false:

1. The updates that were made to the cross-chain contract specification were made in response to the questions I sent TierNolan in PMs on Bitcointalk about standard TXs, transaction mutability, and other issues with the contract. I was the first person to have these issues because I was the first person to use the contract beyond theory.
2. The only good Bitcoin TX library for building complex contracts was the one maintained by Peter Todd. When I wrote the original (and necessary) conditional transaction Script code to execute a cross-chain contract the library had a bug that prevented success. So if I was the first person to experience this bug it also implies I was probably the first person to try implement the contract (at least in Python.)
3. I was active for years on Bitcointalk and literally no one else had software to do a cross-chain contract at the time (on existing blockchains.) The first project that did was developed by Matt Bell (https://github.com/mappum) and it was called 'Mercury Exchange', but even before that was released I had already implemented and abandoned cross-chain contracts for technical reasons as I believed (and still do) that micropayment channels are superior. My original prototype cross-chain contract code is here: https://github.com/robertsdotpm/crosschain_contract (messy code.)

Fast forward to today and decentralized exchanges are some of the most popular trading systems around – offering superior security, UX, reduced fees, and even in some cases - speed! 

# What I would do differently

Coinbend was such a complex and experimental project that I was honestly just happy to get it to work. But in terms of usability: this was the wrong approach. Coinbend had several problems that are evident by its amateurish design - worsened by a lack of options available at the time.

It used full node software for making contracts. So you had to download the chain for every coin you wanted to use. The setup process was horrible. Configuring ports and everything yourself. Waiting for the chains to update... No one is going to use software like that. To make matters worse: the order book contained race conditions so only direct client-to-client trading would have been semi-practical.

Although this prototype was far ahead of its time - the standards that users expect of software is high and this prototype didn't meet them. Coinbend was a massive undertaking requiring software to be written that spanned network programming, financial engineering, front end UX, data scraping, applied cryptography, and much more. In the end I decided it was all too much for one engineer (my friend wasn't a developer) and left to work a normal job. 
