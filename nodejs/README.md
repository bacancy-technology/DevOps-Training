# Demo Node.js Application with PM2 and Nginx

This repository contains demonstrates how to use PM2 for process management and Nginx as a reverse proxy. Follow the steps below to set up the environment and run the application.

## Prerequisites

- Ubuntu 22.04
- Node.js 16 (installed via nvm)
- PM2
- Nginx

#### Recorded terminal sessions
[![Recorded terminal sessions](https://asciinema.org/a/598343.svg)](https://asciinema.org/a/598343)

## Step 1: Connect to the Server
To access the server remotely:
Open a terminal or command prompt on your local machine.
Use the `ssh` command to connect to the server. Replace `<Key_name>`, `<User>`, and `<Public ip of server>` with your actual server information.
   ```bash
   ssh -i <Key_name> <User>@<Public ip of server>
   ```

   For example:

   ```bash
   ssh -i my_private_key.pem ubuntu@123.45.67.89
   ```
Press Enter, and you will be prompted to enter the passphrase for your private key (if applicable).
Once you provide the correct passphrase, you will be connected to the server via SSH.
You are now logged in to the server and can start executing commands remotely. Remember to use caution and only perform authorized actions on the server.
## Step 2: Install Node.js via nvm
#### Use nvm (Node Version Manager) to install Node.js version 16:

Update your repository index
```bash
sudo apt-get update
```
Install nvm - Node Version Manager
```bash
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.3/install.sh | bash
```
Load nvm script
```bash
source ~/.bashrc
```
Install Node.js 16
```bash
nvm install 16
```
## Step 3: Install PM2
Install the latest version of PM2 globally:
```bash
npm install pm2@latest -g
```
## Step 4: Clone the Node.js Demo Application
Clone the Node.js Hello World demo application from GitHub:
```bash
git clone https://github.com/johnpapa/node-hello.git
```
```bash
cd node-hello
```
## Step 5: Start the Application with PM2
Use PM2 to start the Node.js application:
```bash
pm2 start --name demo npm -- start
```
## Step 6: Enable PM2 Startup on Reboot
Simply use the following command to generate an active startup script:
```bash
pm2 startup
```
The above command will output a command to execute, thus please use that command to run whenever you need to run a task for startup:
```bash
sudo env PATH=$PATH:/home/ubuntu/.nvm/versions/node/v16.20.1/bin /home/ubuntu/.nvm/versions/node/v16.20.1/lib/node_modules/pm2/bin/pm2 startup systemd -u ubuntu --hp /home/ubuntu
```
## Step 7: Install and Configure Nginx
Install Nginx on your system:
```bash
sudo apt-get install nginx
```
Edit the default Nginx configuration file:
```bash
sudo nano /etc/nginx/sites-available/default
```
Add the following configuration to the file to set up Nginx as a reverse proxy for the Node.js application:
```bash
server {
    listen 80;
    listen [::]:80;

    server_name _;
        
    location / {
        proxy_pass http://localhost:3000;
        include proxy_params;
    }
}
```
Check the Nginx configuration for any syntax errors:
```bash
sudo nginx -t
```
If the configuration is valid, reload Nginx to apply the changes:
```bash
sudo nginx -s reload
```

Node.js application should now be accessible through Nginx on port 80.

## Step 8: Deploy New Version of Node.js Application
Follow these steps to deploy a new version of your Node.js application using PM2:
 Change Directory to Your Project:
   ```bash
   cd node-hello/
   ```
 Pull Latest Changes from the Repository:
   ```bash
   git pull origin master
   ```
Install Dependencies and Build the Project:
   * Run any necessary npm commands to install dependencies and build the project as required.
  
 Restart the PM2 Process:
   ```bash
   pm2 restart demo
   ```

   This command will restart the PM2-managed process named "demo," and the new changes will be applied to your application. The website will now show the updated site with the latest changes.

- Remember to follow your application's specific deployment steps and any additional configurations needed for your project.


Please refer to the provided reference links below for more detailed instructions on each step:

- [NVM - Node Version Manager](https://github.com/nvm-sh/nvm)
- [PM2 Quick Start](https://pm2.keymetrics.io/docs/usage/quick-start/)
- [Node.js Hello World](https://github.com/johnpapa/node-hello)
- [Configure Nginx as a Reverse Proxy on Ubuntu 22.04](https://www.digitalocean.com/community/tutorials/how-to-configure-nginx-as-a-reverse-proxy-on-ubuntu-22-04)

Enjoy demo Node.js application with PM2 and Nginx! If you have any questions or encounter any issues, feel free to reach out for assistance.
