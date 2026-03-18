Forget Flags and Scripts: Just Rename the File
================================================

Programs usually get input from flags or scripts. But there’s another way: a program can read its **own filename**. That means the **entire configuration can live in the file name itself**, making programs self-contained, portable, and instantly shareable.

Why not just flags or scripts?
-------------------------------

Flags are ephemeral – you have to share the command line or wrap it in a script. Scripts depend on environment, which can break portability. Filenames solve both: the program describes itself, requires zero setup, and any configuration can be shared by simply renaming the file.

Example 1: Reusable installers
-------------------------------

Imagine ``install_PY3_MODULE_NAME.exe``. It reads the filename, extracts the Python module name, downloads dependencies, sets up Python if needed, and creates a launcher. **Rename it**, and you have a new installer for a different project. Icons, mirrors, or other metadata can also live in the file as resources – all self-contained, all shareable.

See my exploration: `here <https://github.com/robertsdotpm/win-auto-py3>`_

Example 2: AI experiment runners
---------------------------------

ML experiments usually require scripts, configs, and data setup. Imagine a single executable:

.. code::

    train---resnet50---lr0.001---batch32---cifar10.exe

It parses its filename, downloads datasets, sets hyperparameters, installs dependencies, and runs automatically. Rename it for a new model, dataset, or parameters.

**Share the file, reproduce instantly.**

Example 3: Ephemeral utilities
-------------------------------

Small ad-hoc tasks can also be encoded in filenames:

.. code::

    compress---photos---high.exe
    backup---home---encrypt-aes256.exe
    fetch---api.github.com---repos/owner/project---q=stars>100---o=json.exe

Each file does its job immediately. Rename to perform a new task. Share it. No setup, no instructions. Everything is self-contained in the name.

Example 4: Self-contained VPN tunnels
--------------------------------------

VPNs between friends are usually tricky to set up. Imagine:

.. code::

    lounge-room---friends-machine---t25565---minecraft.exe

- First two parts: machine names, automatically registered with a server using ECDSA keys.  
- Third part: ports to open.  
- Optional: description.  

Run the file, and the tunnel is up. Rename for new endpoints or ports. **Send to a friend, run, game on.** No config, no editing, zero friction.

Closing thoughts
----------------

This approach collapses configuration, portability, and reproducibility into a single artifact. It challenges conventional assumptions: using filenames as the interface. For installers, AI experiments, utilities, or even VPNs, **renaming the file is all it takes to change behavior**.