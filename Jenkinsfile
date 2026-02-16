pipeline {
    agent any

    environment {
        REGISTRY = 'localhost:5000'
        IMAGE_NAME = 'consulta-medica'
        IMAGE_TAG = "${BUILD_NUMBER}"
        FULL_IMAGE = "${REGISTRY}/${IMAGE_NAME}:${IMAGE_TAG}"
        NAMESPACE = 'consulta-medica'
    }

    options {
        buildDiscarder(logRotator(numToKeepStr: '10'))
        timeout(time: 1, unit: 'HOURS')
        timestamps()
    }

    stages {
        stage('Checkout') {
            steps {
                checkout scm
            }
        }

        stage('Validaciones básicas') {
            steps {
                sh '''
                    set -e
                    find app -name "*.php" -type f | xargs -I {} php -l {}
                    test -f app/consulta_medica/public/index.php
                '''
            }
        }

        stage('Provisionar infraestructura (Terraform)') {
            steps {
                sh '''
                    set -e
                    cd terraform
                    terraform init -input=false
                    terraform apply -auto-approve -input=false
                '''
            }
        }

        stage('Build imagen') {
            steps {
                sh '''
                    set -e
                    docker build -t ${FULL_IMAGE} -f docker/Dockerfile .
                    docker tag ${FULL_IMAGE} ${REGISTRY}/${IMAGE_NAME}:latest
                '''
            }
        }

        stage('Push a registro local') {
            steps {
                sh '''
                    set -e
                    curl -fsS http://${REGISTRY}/v2/ > /dev/null
                    docker push ${FULL_IMAGE}
                    docker push ${REGISTRY}/${IMAGE_NAME}:latest
                '''
            }
        }

        stage('Deploy Kubernetes') {
            steps {
                sh '''
                    set -e
                    kubectl -n ${NAMESPACE} set image deployment/consulta-medica \
                        consulta-medica=${FULL_IMAGE}
                    kubectl -n ${NAMESPACE} rollout status deployment/consulta-medica --timeout=5m
                '''
            }
        }

        stage('Verificación') {
            steps {
                sh '''
                    set -e
                    kubectl get pods -n ${NAMESPACE}
                    kubectl get svc -n ${NAMESPACE}
                '''
            }
        }
    }

    post {
        success {
            echo "Pipeline completado. Imagen desplegada: ${FULL_IMAGE}"
        }
        failure {
            echo 'Pipeline falló. Revisa logs de Terraform, Docker y kubectl.'
        }
    }
}
