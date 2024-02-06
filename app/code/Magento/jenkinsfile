pipeline {
    agent { label 'JDK-17-MVN-3.6.3' }

    tools {
        // Install the Maven version configured as "M3" and add it to the path.
        maven "mvn-3.6.3"
    }
	pipeline {
    agent { label 'JDK-17-MVN-3.6.3' }

    tools {
        // Install the Maven version configured as "M3" and add it to the path.
        maven "mvn-3.6.3"
    }
    environment {
        SCANNER_HOME = tool 'sonar-scanner'
    }

    stages {
        stage('clean workspace') {
            steps {
                cleanWs()
            }
        }
        stage('Build') {
            steps {
                // Get some code from a GitHub repository
                git 'https://github.com/satya36-cpu/AquilaCMS.git'

                // Run Maven on a Unix agent.
                sh "mvn -Dmaven.test.failure.ignore=true clean package"

                // To run Maven on a Windows agent, use
                // bat "mvn -Dmaven.test.failure.ignore=true clean package"
            }
        } 
        stage('build && SonarQube analysis') {
            agent { label 'JDK-17-MVN-3.6.3' }
            steps {
                withSonarQubeEnv('sonar-server') {
                    // Optionally use a Maven environment you've configured already
                    withMaven(maven:'Maven 3.6') {
                        sh 'mvn clean package sonar:sonar'
                    }
                }
            }
        }
        stage("Quality Gate") {
            steps {
                timeout(time: 1, unit: 'HOURS') {
                    // Parameter indicates whether to set pipeline to UNSTABLE if Quality Gate fails
                    // true = set pipeline to UNSTABLE, false = don't
                    waitForQualityGate abortPipeline: true
                }
            }
        }
        stage('docker image build & push') {
            agent { label 'JDK-17-MVN-3.6.3' }
            steps {
                sh 'docker image build -t satyabrata36/aquila:1.0 .'
                sh 'docker image push satyabrata36/aquila:1.0'
            }
        }
        
    }
}           
        

            
        
    





    stages {
        stage('clean workspace') {
            steps {
                cleanWs()
            }
        }
        stage('Build') {
            steps {
                // Get some code from a GitHub repository
                git 'https://github.com/satya36-cpu/AquilaCMS.git'

                // Run Maven on a Unix agent.
                sh "mvn -Dmaven.test.failure.ignore=true clean package"

                // To run Maven on a Windows agent, use
                // bat "mvn -Dmaven.test.failure.ignore=true clean package"
            }
        } 
        stage("Sonarqube Analysis") {
            agent { label 'JDK-17-MVN-3.6.3' }
            steps {
                withSonarQubeEnv('sonar-server') {
                    sh '''$SCANNER_HOME/bin/sonar-scanner -Dsonar.projectName=netflix \
                    -Dsonar.projectKey=netflix'''
                }
            }
        }
        stage("quality gate") {
            steps {
                script {
                    waitForQualityGate abortPipeline: false, credentialsId: 'Sonar-token'
                }
            }
        }
        stage('docker image build & push') {
            agent { label 'JDK-17-MVN-3.6.3' }
            steps {
                sh 'docker image build -t satyabrata36/aquila:1.0 .'
                sh 'docker image push satyabrata36/aquila:1.0'
            }
        }
        
    }
}           
        

            
        
    



