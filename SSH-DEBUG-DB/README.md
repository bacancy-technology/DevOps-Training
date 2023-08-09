SSH

SSH is a secure protocol used to access remote servers and perform various tasks securely.

SSH Basics and Best Practices
1. Introduction to SSH:
   SSH is commonly used for tasks such as:

    Logging into remote servers securely.
    Transferring files between your local machine and the remote server.
    Executing commands on the remote server.
    Creating secure tunnels for port forwarding.

   Key Components of SSH:

    Public Key: This is the key you share with others or store on servers. It's used to encrypt data that only the corresponding private key can decrypt.

    Private Key: This key is kept secret and is used to decrypt data encrypted with the corresponding public key.

    Passphrase: An optional passphrase adds an extra layer of security to your private key. You'll need to enter it each time you use the key.

    SSH Agent: An SSH agent stores your private key and manages authentication, allowing you to use your private key without typing the passphrase eve

2. Setting Up SSH:
    Step 1: Install OpenSSH (Linux/macOS) or PuTTY (Windows):

    If you're using Linux or macOS, you likely have OpenSSH already installed. You can check by running ssh -V in your terminal.
    For Windows users, you can download and install PuTTY from the official website.

    Step 2: Generate SSH Key Pair:

    Open a terminal or command prompt.

    To generate a new SSH key pair, use the following command:
         ssh-keygen -t rsa -b 4096 -C "your_email@example.com"

    You'll be prompted to choose a location to save the key pair. Press Enter to accept the default location (~/.ssh/id_rsa for the private key and ~/.ssh/id_rsa.pub for the public key).

    You can choose to set a passphrase for your private key. This is optional but recommended for added security. Enter a passphrase and confirm it.
   Step 3: Key Permissions:

    Ensure that the .ssh directory and the keys have secure permissions:
      chmod 700 ~/.ssh
      chmod 600 ~/.ssh/id_rsa
      chmod 644 ~/.ssh/id_rsa.pub

   Step 4: Add Your Public Key to Remote Servers:

    Use the following command to display your public key:
     cat ~/.ssh/id_rsa.pub
    Copy the entire contents of the public key (starting with ssh-rsa).

    Log in to the remote server you want to access using SSH.

    On the remote server, add your public key to the ~/.ssh/authorized_keys file. If the file doesn't exist, you can create it. Paste the public key contents and save the file.

   Step 5: Test SSH Connection:

    Try connecting to the remote server using your private key and passphrase (if set):
    ssh username@remote_server_ip

For SSH into EC2 instance, we will need .pem file created while the server creation.so the syntax to SSH into EC2 using pem file is :
ssh -i file_name.pem username@remote_server_ip

