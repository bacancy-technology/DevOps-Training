pipeline {
	agent any
  stages {
    stage('S3download') {
      steps {
        withAWS(region: 'us-east-2',credentials:'awscredentials') {
            s3Download(file: 'composer.json', bucket: 'composor-json', path: 'composer.json',force:true)
            s3Download(file: '.env', bucket: 'composor-json', path: '.env',force:true)
          }
        }
    }
  	stage('Docker build') {
      steps {
      	sh 'docker build -t laravel-app .'
        sh 'docker stop backend'
        sh 'docker rm backend'
        sh 'docker run -d -p 9000:8000 --network=cod-network --name backend laravel-app'
        sh 'docker system prune -af'
      }
    }
  }
}
