Last year I started work on a new async networking library for Python. I'd never written any 'async' code before and wasn't sure how hard it would be. As it turns out: Python's async library is quite good -- but there's still room for improvement in my opinion. In this post I'm going to talk about async networking in Python. The features I like and the ones I don't. Then I'll talk about the library I created to try improve networking in Python.

# Python async networking

I started my async journey by looking through the examples in the online Python docs. These docs are great and remind me of how developer-friendly the PHP docs are. Eventually I found an example for a TCP server. The code looked like this:

```python3
class ProtocolClass(asyncio.Protocol):
	# ... callback methods here
	# e.g. data_receive(self, data) ...

# (There is a different function to call for UDP servers that does the same thing.)
server = await loop.create_server(
	# Factory for making Protocol objects for new clients.
	lambda: ProtocolClass(),
	
	# Details for the listen socket ...
	'127.0.0.1',
	8888
)
```

**What this code means is:** every time a new client connects -- create a Protocol object for them. The method that does this is the 'factory' lambda function. The class also has special callback methods that are run for various events. It's pretty simple. Unfortunately, the moment you try use async functions / coroutines with Protocol callbacks you're hit by an error. Believe me -- it is a frustrating part of Python networking and I'm not the only one who thinks so... But keep reading because the problems get worse.

As I learned: Python does indeed have async networking features. It turns out you can use the **loop.create_connection** function to return objects for use in coroutines. So if you want to write 'await connection() ... await send ... await recv' for TCP -- these objects let you do that. 

```python3
# Open a new TCP connection.
reader, writer = await asyncio.open_connection('127.0.0.1', 8888)

# Send some data down the socket.
writer.write(b"message")
await writer.drain()

# Read some data back.
data = await reader.read(100)

# Close the socket.
writer.close()
await writer.wait_closed()

# You may be wondering what the 'async' version for UDP looks like?
# ... and the answer is: there isn't one.
```

The main benefit to using async await is the program preserves its sequential control flow but 'blocking' operations don't stop the program. Instead, if a task needs to wait for a result, execution is given back to the event loop, and other tasks are free to run or be resumed when they're ready. One disadvantage to coroutines is you have to manually check for changes. E.g. if there's new data to be read from a stream it's your job to await it. With Protocol classes the event loop does that for you.

**From these examples -- a myriad of issues stands out to me:**

1. Protocol classes and async functions force you to choose between them. Neither one is compatible with the other.
2. The APIs for TCP and UDP are different. For Protocol classes even the method names vary.
3. There is no async equivalent of 'reader-writer streams' for UDP. This means you can't use async networking with UDP in Python.
4. The way server listen sockets bind to interfaces is poorly defined (if at all.)
5. IPv6 is painful to use in Python and requires specialised code to handle all address types (try it if you dare.)
6. Having to manage two objects for TCP (a reader and writer stream) is ... annoying. Developers like sockets because they encapsulate everything in one place. One item is easier to track and pass around a complex program.

My idea to solve these problems is to encapsulate everything in a well-designed, consistent API. The API should allow endpoints to be used with both async functions and Protocol-style callbacks; It should not have any transport-specific code (so the same code will work with TCP and UDP); It should support network interfaces well; And IPv6 should be as easy to use as IPv4.

Here's what I came up with.

#  P2PD async networking library

The first problem I wanted to solve was the lack of network interface support. In network programming it's common to see code that glosses over interface management. What makes this so appealing is the operating system supports default interfaces. So why bother choosing one? The problem is: if you write code that only uses the default interface then your program won't be able to utilise all routes -- possibly an issue for some software. It is for a networking library!

```python3
from p2pd import *

async def main():
	# Select interface and choose a route to bind to.
	# No interface name = default interface.
	i = await Interface()
	route = i.route()
	
	# You can also load the NAT details.
	await i.load_nat()
	print(i) # All addresses and NAT behaviour
```

Previously, I spoke about how the asyncio module only provides async functions for TCP. I thought this was a major limitation. So I added support for async UDP. Obviously UDP isn't reliable so sometimes recv calls time out. But importantly -- doing I/O isn't going to block the program so the event loop will be free to work on other tasks.

```python3
	# Open UDP endpoint.
	await route.bind() # port=0 ... 
	dest = await Address("p2pd.net", 7, route)
	pipe = await pipe_open(UDP, dest, route)
	pipe.subscribe()

	# Async networking.
	await pipe.send(b"echo back this message.", dest.tup)
	out = await pipe.recv(timeout=2)
	print(out)
	
	# Cleanup the endpoint.
	await pipe.close()
	
	# More info on the basics here:
	# https://p2pd.readthedocs.io/en/latest/python/basics.html
```

A key goal I had for this library was to provide the same APIs for most use-cases. No matter if the transport is TCP or UDP; IPv4 or IPv6; Server or client. The main abstraction I use is called a 'pipe.' Pipes allow developers to choose what programming model to use. They support async coroutines and event-based callbacks. My library fully supports using coroutines as callbacks or regular functions. In the past this was a major source of pain for developers. No longer!

```python3
	async def msg_cb(msg, client_tup, pipe):
		await pipe.send(msg, client_tup)

	# Adds a message handler before pipe creation.
	# Can use callbacks or awaits.
	pipe = pipe_open(TCP, route=route, msg_cb=msg_cb)
	
	# Alternatively you can use add_msg_cb.
	pipe.add_msg_cb(msg_cb)
```

Some servers need to support multiple protocols and address families. For this there is the Daemon class. There's not much to it. It simply handles creating pipes for you.

```python3
class EchoServer(Daemon):
    def __init__(self):
        super().__init__()

    async def msg_cb(self, msg, client_tup, pipe):
        await pipe.send(msg, client_tup)
		
async def main(route):
	await route.bind(port=12345)
	server = await EchoServer().listen_all(
		[route],
		[12345, 8080], 
		[TCP, UDP],
		af=AF_ANY
	)
```

You'll notice that the only way to build servers in Python is with callbacks. I think this is usually a good model. But what if you want to write an async version? That is -- what if you want to await accepting a new client? Well, now you can. Simply await the pipe and it will return a regular pipe for the next client that connects to the server. The pipe allows await for send and receive.

```python3
client = await pipe
await client.send(b"hello")
```

Currently, my examples have been 'push' and 'pull.' However, sometimes it's useful to be able to subscribe to certain messages. Consider UDP for a moment. In UDP messages may arrive in any order. Therefore, it's common to see protocols using unique IDs in response messages that mirror the IDs used in requests. What this means is ideally it should be possible to subscribe to certain patterns and await the results. That's how async recv works in P2PD.

You subscribe using a regex pattern and await a response. If you look at the async examples earlier you may see I called subscribe(). What this means is 'subscribe to all messages.' Any message matching that pattern will be added to their own queue. You can then await that queue using the recv() call. It's very flexible.

More info on that here: https://p2pd.readthedocs.io/en/latest/python/queues.html

# Peer-to-peer networking with P2PD

Having a library that works well is great and I've already used it to build many programs. But the reason I started this project was to make peer-to-peer connections easier.

Some of the coolest software today seems to use peer-to-peer networking. Bitcoin, Bittorrent, Skype, and any number of games all use peer-to-peer features. These services are powerful because they let their users be part of running them rather than relying on a trusted third-party. The downside is they're more complex. Routers, NATs, firewalls, and dynamic IPs all contribute to making the process difficult. To do P2P networking right involves a mishmash of esoteric ideas.

<details>
<summary>Show P2P connectivity methods</summary>

1. Direct connect -- If a peer's node port is open then a regular TCP connection can be used. When the node server is started the library handles IPv4 port forwarding and IPv6 pin hole rules.
2. Reverse connect -- If a host's node port is reachable then you can simply tell a peer to connect to you. Reverse connect means that connectivity is possible if either side to a connection is able to open a port.
3. TCP hole punching -- A little known feature of TCP allows for a new connection to be created if two peers connect at the same time. P2PD extensively enumerates NAT behaviour to optimise the chances of success with TCP hole punching.
4. TURN servers -- A protocol for proxy servers called 'TURN' can be used as a last resort. By default this method is not enabled as it uses UDP for the transport so packets may arrive out of order or get lost. Perhaps this will be improved in the future.
</details>

<details>
<summary>P2P direct connect example</summary>

```python3
from p2pd import *

# Put your custom protocol code here.
async def msg_cb(msg, client_tup, pipe):
    # E.G. add a ping feature to your protocol.
    if b"PING" in msg:
        await pipe.send(b"PONG")

async def make_p2p_con():
    # Initalize p2pd.
    netifaces = await init_p2pd()
    #
    # Start our main node server.
    # The node implements your protocol.
    node = await start_p2p_node(netifaces=netifaces)
    node.add_msg_cb(msg_cb)
    #
    # Spawn a new pipe from a P2P con.
    # Connect to our own node server.
    pipe = await node.connect(node.addr_bytes)
    pipe.subscribe(SUB_ALL)
    #
    # Test send / receive.
    msg = b"test send"
    await pipe.send(b"ECHO " + msg)
    out = await pipe.recv()
    #
    # Cleanup.
    assert(msg in out)
    await pipe.close()
    await node.close()

# Run the coroutine.
# Or await make_p2p_con() if in async REPL.
async_test(make_p2p_con)
```
</details>

In P2PD there are nodes who run their own TCP servers that implement one or more protocol handlers. These are the msg_cb functions listed earlier. Nodes have their own address that can be given out to connect to them. The address includes a lot of information like what interfaces the node has, it's NAT configurations, information on signalling servers, and so on. The Node object has a connect function to handle making P2P connections. You need a node's address to be able to use it.

More details on peer-to-peer networking here: https://p2pd.readthedocs.io/en/latest/python/index.html

# How P2PD compares to Libp2p

Libp2p is currently the most popular library for peer-to-peer networking. There are implementations of Libp2p in many languages and the Go version appears to be the most complete. A question I see arising is 'how does P2PD compare to Libp2p?' I won't write a full essay here but here are the cliff notes. Feel free to skip this section if you don't know about Libp2p.

<details>
<summary>Show comparison</summary>
  
1. **Libp2p's implementation of TCP hole punching is poor** and will result in lower success for direct connections. The reason for this is it's NAT enumeration code doesn't account for a basic range of NAT behaviours used by routers. Furthermore, there appears to be no way for nodes to synchronise hole punching which will result in higher failure rates for low latency connections.

2. **Libp2p's address format has insufficient detail for common scenarios.** For example, if someone hosts a server in the same LAN, a Libp2p address cannot identify that server. Should a server's port be closed -- the address provides no information on a node's NAT details to help with NAT traversal.

3. **Libp2p reinvents the wheel for common protocols.** Libp2p uses custom protocols for address lookup, signalling, and relaying. Consequently, they cannot take advantage of public infrastructure or the open source software that already exists. All of Libp2p's custom protocols have established alternatives: STUN, MQTT, and TURN.

4. **Libp2p glosses over network interface management.** As Libp2p nodes were not designed with multiple interfaces in mind they are not able to outright use alternative NICs that may have more favourable NAT configurations for peer-to-peer networking.

5. **Libp2p seems to have a weak culture around testing.** Libp2p has a small number of unit tests and seems to have virtually no infrastructure in place for live testing. Networking code needs to interact with real systems to know it works. So running your own echo servers; protocol test nodes; debug servers, and so on; is part of the process.

TL; DR: It seems that every aspect of Libp2p that ought to not have been over-engineered -- has been -- while the things that would have benefited from added attention (like a robust address format and NAT traversal techniques) have been the most neglected. I am not a fan of Libp2p and think many of it's approaches show a poor understanding of networking.
</details>

# Support for other languages?

P2PD is written in Python and targets Python version 3.6 or higher (3.5 and higher on non-Windows.) It supports most platforms. But what about other languages? Is it possible to use P2PD from languages that aren't Python? What I came up with was a special REST server for doing P2P networking. The server let's you lookup information on your interfaces, make peer-to-peer connections, push/pull/pub/sub, and more. Everything you would need to do P2P direct connections from other languages well.

More details on that here: https://p2pd.readthedocs.io/en/latest/rest_api.html

I think it would be possible to use APE's cross-platform build of Python to have a distribution of P2PD that could easily be packaged for any device. You would then only have to execute a file and point your code at an API and the library would do the rest. For software that needed even more control over sockets -- I think it would be possible to share sockets with another process.

# Outro

If you made it this far then thanks for reading! If you liked this post you can check out P2PD here:

https://github.com/robertsdotpm/p2pd
https://pypi.org/project/p2pd/
https://p2pd.readthedocs.io

I'm also looking for my next software engineering role. So if you need someone who ships hit me up (matthew@roberts.pm).