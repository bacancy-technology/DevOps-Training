# Docker and GitHub Actions Deployment for VueJS Applications

## Introduction

This README provides an overview of deploying a VueJS application using Docker. This is a comprehensive guide for developers who want to learn how to containerize a Vuejs application.

## Prerequisites

Before getting started, make sure you have the following:

- Basic knowledge of VueJS
- Docker installed on your local machine (Command for ubuntu: sudo apt-get update && sudo apt-get install -y curl && curl -fsSL https://get.docker.com/ | sh && sudo usermod -aG docker $USER && newgrp docker)
- A VueJS application you want to deploy

## Step 1: Create a VueJS Application

If you don't already have a VueJS application, you can clone a simple one for this demonstration.

```bash
git clone https://github.com/ripalshah710/VueJS.git
```

## Step 2: Dockerize Your Application

1. Create a Dockerfile for your VueJS application:

```Dockerfile

# Choose the Image which has Node installed already
FROM node:lts-alpine

# install simple http server for serving static content
RUN npm install -g http-server

# make the 'app' folder the current working directory
WORKDIR /app

# copy both 'package.json' and 'package-lock.json' (if available)
COPY package*.json ./

# install project dependencies
RUN npm install

# copy project files and folders to the current working directory (i.e. 'app' folder)
COPY . .

# build app for production with minification
RUN npm run build

EXPOSE 8080
CMD [ "http-server", "dist" ]
```

2. Build a Docker image from your Dockerfile:

```bash
docker build -t vuejsApp .
```

3. Run a Docker container from your image:

```bash
docker run -d -p 8080:80 vuejsApp
```
