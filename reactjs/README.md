
# Deploy ReactJS App with s3 static Hosting 

This repository contains demonstrates how to use PM2 for process management and Nginx as a reverse proxy. Follow the steps below to set up the environment and run the application.

* Create a simple react app.
* Configure an S3 bucket for static web hosting.
* Deploy!

## Prerequisites

AWS Account created
AWS IAM User
AWS CLI installed (and additional dependencies)
AWS Credentials Locally Setup


<!-- GETTING STARTED -->
## Getting Started

## Step 1: create or deploy a simple react app. 

1. create a react application
 ```sh
 npm install -g create-react-app 
 ```

2. give name to your application 
```sh
npx create-react-app react-demo
```

3. go to project directory 
```sh
cd react-demo
```

4. Build the react application
```sh
npm run build 
```

## Step 2:Configure an S3 bucket for static web hosting.

1. login to your aws account 
   https://aws.amazon.com/console/

2. Open s3 bucket 
- Get into AWS and search for S3 in the search bar.

![Alt text](image.png)

Click on the ‘Create Bucket’ button

![Alt text](image-1.png)

Fill the bucket name, and untick ‘Block all public access’.
You’ll want to allow public access so everyone can access your website.
After that, you have to acknowledge the change you did by ticking the acknowledge warning on the bottom of this area.

![Alt text](image-2.png)

Click on the ‘Create Bucket’ at the bottom of the creation page.
Get into your newly created bucket and click on the ‘Properties’ on the bucket’s navbar on the top.


![Alt text](image-3.png)


Scroll to the bottom and click ‘Edit’ on the right side of ‘Static Web Hosting’, and copy the following settings:

![Alt text](image-5.png)

Get back to the main bucket’s navbar and click on ‘Permissions’, and fill the following piece of code into the Bucket Policy area:
Make sure to change ‘your_bucket_name_here’ to your bucket name.

```sh
{
    "Version": "2012-10-17",
    "Statement": [
        {
            "Sid": "PublicReadGetObject",
            "Effect": "Allow",
            "Principal": "*",
            "Action": "s3:GetObject",
            "Resource": "arn:aws:s3:::your_bucket_name_here/*"
        }
    ]
}
```




Congratulations! You’ve successfully deployed your React application to the AWS S3 Static Web Hosting service.

And that’s how it’s done. Thanks for reading.



