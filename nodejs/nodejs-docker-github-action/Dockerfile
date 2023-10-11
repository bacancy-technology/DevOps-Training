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
CMD ["npm", "start"]
