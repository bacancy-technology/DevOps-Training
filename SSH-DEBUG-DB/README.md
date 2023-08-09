SSH

SSH is a secure protocol used to access remote servers and perform various tasks securely.

SSH Basics and Best Practices
1. Introduction to SSH:
   SSH is commonly used for tasks such as:

    - Logging into remote servers securely.
    - Transferring files between your local machine and the remote server.
    - Executing commands on the remote server.
    - Creating secure tunnels for port forwarding.

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

For SSH into the EC2 instance, we will need a .pem file created while the server creation. so the syntax to SSH into EC2 using pem file is :
ssh -i file_name.pem username@remote_server_ip

If we are facing any issue like the following then please perform solutions written in each scenario:
1. If your IP is not added to the security group of an EC2 instance, you might encounter a connection timeout or refusal error when trying to access the instance. This could happen when you try to connect via SSH or any other network protocol, depending on the service the instance is running.

   ![image](https://github.com/bacancy-technology/DevOps-Training/assets/84562701/bd0f9814-0b07-4228-a100-ae0071fe5ba6)

solution: To resolve this issue, you would need to modify the security group associated with your EC2 instance to allow incoming connections from your IP address or range.

2. If you find below errors, then please try solutions suggested here.
   ![image](https://github.com/bacancy-technology/DevOps-Training/assets/84562701/702b5aea-4920-4fd4-aaaf-b56310867b23)

   OR

   ![image](https://github.com/bacancy-technology/DevOps-Training/assets/84562701/01fa98aa-f0c1-4672-9df0-25906980aced)

   solution: These error messages indicate that the permissions on the private key file are too open, and SSH refuses to use the key because it's not secure enough. To fix this issue, you should change the permissions on the .pem file to be more restrictive.

   You can use the chmod command to adjust the permissions of the .pem file. For example, you can set the permissions to 400 to make the file readable only by the owner:
   chmod 400 your-key.pem
3. If you don't want to use the .pem file, it can be hacked, or sometimes it's a tedious task to pass the reference to the .pem file.
   Solution: add your ssh-keygen to server's .ssh/authorized_key folder. so from next time when you login to the server, you need to pass this command, not need to pass .pem file
   ssh username@server_ip


DBA:
   
    When connecting to a database, various issues can arise due to a variety of factors. Here are some common issues you might encounter while trying to establish a connection to a database:

    1. Incorrect Credentials: Providing incorrect username, password, or database name can prevent successful connection. so, please check creds at first place.

    2. Firewall or Network Issues: Network problems, firewalls, or security groups might block the connection between your application and the database server.some of the examples are as below:

      - Firewall Configuration: The database server is protected by a firewall that restricts incoming connections to specific IP addresses for security reasons.

      - No Inbound Rule: The firewall is configured to allow incoming connections only from a whitelist of IP addresses, but the IP address of your EC2 instance is not included in the whitelist.

      - Connection Attempt: When your application tries to establish a connection to the database server using the specified host and port, the database server's firewall denies the connection attempt because the source IP (your EC2 instance's IP) is not authorized.

      - Error Message: Your application receives an error message indicating that the connection was refused or timed out, depending on the firewall's behavior.

      Solutions:
      - Check Firewall Rules: Verify the firewall rules on the database server to ensure that your EC2 instance's IP address is allowed to connect.

      - Update Firewall Rules: If your EC2 instance's IP address is not in the allowed list, update the firewall rules to include it.

      - Security Group (EC2): If you're using security groups in AWS, make sure that the outbound rules for your EC2 instance allow the necessary outgoing traffic to the database server's IP and port.

      - Check Network Routes: Ensure that there are no network routing issues between your EC2 instance and the database server. Misconfigured routes could lead to connection problems.

      - Test Connection: Once the firewall rules are updated, test the database connection from your EC2 instance again.

    3. Hostname or IP Address: If the hostname or IP address of the database server is incorrect, your connection attempt might fail.

    4. Port Number: Specifying the wrong port number for the database can lead to connection failures.

    5. Database Server Unavailability: If the database server is down, undergoing maintenance, or experiencing high load, connections might be refused or timeout.

    6. Insufficient Permissions: The user you are connecting as might not have the necessary permissions to access the database.

    7. Resource Limitations: The database might have reached its connection limit, preventing new connections.

    Now, there are some benefits of RDS over traditional databases, its beneficial to use RDS, but we need to plan it according to the needs of the client.
    1. Automated Scaling: Let's say you run an e-commerce website and you experience a sudden surge in traffic during holiday sales. With Amazon RDS, you can easily scale up the database instance to handle the increased load without manually provisioning hardware or making extensive changes to configurations.

    2. Reduced Administrative Overhead: Imagine you're a small startup with limited resources. Instead of hiring a dedicated database administrator to manage backups, updates, and security patches, you can rely on RDS to handle these tasks, allowing your team to focus on developing your core product.

    3. Backup and Recovery: Suppose you're running a content management system. With RDS automated backups and point-in-time recovery, you can easily restore your database to a specific moment in time if data is accidentally deleted or corrupted.

    4. High Availability for Critical Applications: For a critical application that must be available 24/7, you can deploy your database using Multi-AZ deployments with RDS. In the event of a hardware failure or other issues, RDS automatically switches to a standby replica in a different Availability Zone.

    5. Security Compliance: If your application handles sensitive user data, RDS provides encryption at rest and in transit, helping you meet compliance requirements such as GDPR or HIPAA more easily.

    6. Cost Efficiency: For a small business or startup, the operational and maintenance costs of a self-managed database can be significant. With RDS, you can choose from different instance types and pay for only the resources you use, potentially leading to cost savings.

    Database Backup:
    1. MySQL:
    mysqldump -u username -p database_name > backup.sql
    2. PostgreSQL:
    pg_dump -U username -h hostname -p port -d database_name > backup.sql
    3. MongoDB:
    mongodump --host hostname --port port --db database_name --out /path/to/backup

    Database Restore:
    1. MySQL:
    mongodump --host hostname --port port --db database_name --out /path/to/backup
    2. PostgreSQL:
    pg_restore -U username -h hostname -p port -d database_name backup.sql
    3. MongoDB:
    mongorestore --host hostname --port port --db database_name /path/to/backup

    
