# Demo ROR Application Deployment with Docker in AWS

This repository contains demonstrates how to dockerize the ROR application and deploy on EC2 instance in AWS. 

## Prerequisites

- AWS account with AWS IAM user
- Amazon RDS Postgres DB created with following details
    - Engine : postgres
    - Version: 13.10
    - DB Instance Type: db.t3.micro

## Step 1: Create EC2 instance in AWS

Follow the link step by step given below to create ec2 instance.

[Get started with Amazon EC2 Linux instances](https://docs.aws.amazon.com/AWSEC2/latest/UserGuide/EC2_GetStarted.html)

## Step 2: Connect to the Server
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

## Step 3: Install Docker

Follow the link step by step given below to install docker in ec2 instance.

[How To Install and Use Docker on Ubuntu 20.04](https://www.digitalocean.com/community/tutorials/how-to-install-and-use-docker-on-ubuntu-20-04)

## Step 4: Clone the Repository

Clone the application repository which needs to be deployed. <br>
Use the `git` command to clone the repository. Replace `<Repo_URL>` with your actual Repository information. 
   ```bash
   git clone <Repo_URL>
   ```

## Step 5: Create database.yml configuration file

Note: This is only applicable when your application requires database. 

Go to the config directory and create database.yml file given below.<br>
Replace `<db_username>`, `<db_password>`, `<db_endpoint>` and `<db_name>` with your actual database information. Add the environments as much as needed.

   ```bash
   default: &default
    adapter: postgresql
    encoding: unicode
    pool: 5
    username: <db_username>
    password: <db_password>
    host: <db_endpoint>
    database: <db_name>
  development:
    <<: *default
  production:
    <<: *default
   ```
## Step 6: Create Dockerfile

Note: the packages and gems mentioned here can be adjusted to suit the specific requirements and dependencies as per the project.

Create dockerfile in the root directory of the project as given below. 
```bash
FROM ruby:3.0.0

RUN curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add -

RUN echo "deb https://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list

RUN apt-get update && apt-get install -y yarn 

RUN curl -LO https://nodejs.org/dist/v16.20.1/node-v16.20.1-linux-x64.tar.xz

RUN tar -C /usr/local --strip-components 1 -xf node-v16.20.1-linux-x64.tar.xz

WORKDIR /app

COPY Gemfile* .

RUN bundle install

RUN yarn cache clean

COPY package.json ./

RUN yarn install

RUN yarn upgrade

COPY . .

RUN rails db:migrate

RUN bundle exec rails assets:clobber 

RUN bundle exec rails assets:precompile 

EXPOSE 3000

CMD ["bundle", "exec", "rails", "server", "-b", "0.0.0.0", "-p", "3000"]

   ```

## Step 6: Build the Docker Image

Run the following command to build docker image.<br>
Replace `<Image_Name>` with required image name you want to setup and `<path>` with the path where dockerfile is created.
   ```bash
   docker build -t <Image_Name> <path>
   ```

## Step 7: Run the Docker Image

Run the following command to run the docker image. <br>
Replace `<Image_Name>` with image name created in above step.
   ```bash
   docker run -d -p 3000:3000 <Image_Name> 
   ```   

## Step 8: Checkout the Application Output

Go to the EC2 Management console, Click on the EC2 instance and copy the Public IPv4 address. 
Navigate to browser tab and Replace `<IP>`with the EC2 Instance's public IPv4 address and `<PORT>` with the application port. 

   ```bash
   <IP>:<PORT> 
   ```  