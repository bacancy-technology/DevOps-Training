# Docker and GitHub Actions Deployment for Node.js Applications

## Introduction

This README provides an overview of deploying a Node.js application using Docker and GitHub Actions. This is a comprehensive guide for developers who want to learn how to containerize a Node.js application and set up CI/CD using GitHub Actions.

## Prerequisites

Before getting started, make sure you have the following:

- Basic knowledge of Node.js
- A GitHub account
- Docker installed on your local machine (Command for ubuntu: sudo apt-get update && sudo apt-get install -y curl && curl -fsSL https://get.docker.com/ | sh && sudo usermod -aG docker $USER && newgrp docker)
- A Node.js application you want to deploy

## Step 1: Create a Node.js Application

If you don't already have a Node.js application, you can create a simple one for this demonstration.

```bash
mkdir node-app
cd node-app
npm init -y
npm install express
```

Create an `app.js` file with a basic Express.js application:

```javascript
const express = require('express');
const app = express();
const port = process.env.PORT || 3000;

app.get('/', (req, res) => {
  res.send('Hello, Docker!');
});

app.listen(port, () => {
  console.log(`Server is running on port ${port}`);
});
```

## Step 2: Dockerize Your Application

1. Create a Dockerfile for your Node.js application:

```Dockerfile
# Use an official Node.js runtime as a parent image
FROM node:18

# Set the working directory inside the container
WORKDIR /usr/src/app

# Copy package.json and package-lock.json to the container
COPY package*.json ./

# Install application dependencies
RUN npm install

# Bundle your source code into the Docker image
COPY . .

# Expose a port for the application to listen on (change as needed)
EXPOSE 3000

# Define the command to start your application
CMD ["node", "app.js"]
```

2. Build a Docker image from your Dockerfile:

```bash
docker build -t bacancy-devops-training .
```

3. Run a Docker container from your image:

```bash
docker run -d -p 3000:3000 bacancy-devops-training
```

## Step 3: GitHub Actions CI/CD

1. Create a `.github/workflows` directory in your GitHub repository.

2. Inside this directory, create a YAML file (e.g., `nodejs-docker.yml`) with the following content:

```yaml
name: Deploy to Server
on:
  push:
    branches:
      - main
jobs:
  deploy:
    runs-on: ubuntu-latest
    
    env:
      CONTAINER_NAME: bacancy-devops-training
      
    permissions:
      contents: read
      packages: write
       
    steps:
    # https://github.com/marketplace/actions
    
    - name: Checkout code
      uses: actions/checkout@v2
      
    - name: Set up QEMU
      uses: docker/setup-qemu-action@v3
      
    - name: Set up Docker Buildx
      uses: docker/setup-buildx-action@v3
      
    - name: Log in to GitHub Container Registry
      run: echo ${{ secrets.GITHUB_TOKEN }} | docker login ghcr.io -u ${{ github.repository_owner }} --password-stdin
      
    - name: Build and publish a Docker image for ${{ github.repository }}
      uses: macbre/push-to-ghcr@master
      with:
        image_name: ${{ github.repository }}  # it will be lowercased internally
        github_token: ${{ secrets.GITHUB_TOKEN }}
         
    - name: Deploy to server via ssh
      uses: appleboy/ssh-action@v1.0.0
      with:
        host: ${{ secrets.HOST }}
        username: ${{ secrets.USERNAME }}
        key: ${{ secrets.KEY }}
        port: ${{ secrets.PORT }}
        script: |
         
          # Log in to GitHub Container Registry
            echo ${{ secrets.GITHUB_TOKEN }} | docker login ghcr.io -u ${{ github.repository_owner }} --password-stdin
            
          # Check if container is already running
          if docker ps -a --format "{{.Names}}" | grep -q "${{env.CONTAINER_NAME}}"; then
             echo "Container is already running, stopping and removing it..."
             docker stop ${{env.CONTAINER_NAME}}
             docker rm ${{env.CONTAINER_NAME}}
             echo "Container stopped and removed."
             echo "Proceeding to pull and run a new container..."
          else
             echo "Container is not running. Proceeding to pull and run a new container..."
          fi          

          # Pull Docker image
          docker pull ghcr.io/${{ github.repository }}:latest

          # Run Docker container
          docker run -d -p 80:3000 --name ${{env.CONTAINER_NAME}} ghcr.io/${{ github.repository }}:latest

```

3. Set up secrets for your GitHub repository:

   - `HOST`: Your server's hostname or IP address.
   - `USERNAME`: Your server's username.
   - `KEY`: Your SSH private key for authentication.
   - `PORT`: The SSH port (usually 22).
      ###### environment variables
   - `CONTAINER_NAME`: The Docker image name (e.g., `bacancy-devops-training`).

## Conclusion

With this setup, every time you push to the `main` branch, GitHub Actions will build a Docker image, push it to a registry (e.g., GitHub Container Registry), and deploy the updated image to your server. This enables automated CI/CD for your Node.js application.

Happy coding and deploying!
