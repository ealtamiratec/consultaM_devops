pipeline {
    agent any

    triggers {
        pollSCM('H/2 * * * *')
    }

    environment {
        IMAGE_NAME = 'consulta-medica'
        IMAGE_TAG = "${BUILD_NUMBER}"
        FULL_IMAGE = "${IMAGE_NAME}:${IMAGE_TAG}"
        LATEST_IMAGE = "${IMAGE_NAME}:latest"
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

                    terraform state rm kubernetes_deployment.mysql >/dev/null 2>&1 || true
                    terraform state rm kubernetes_deployment.app >/dev/null 2>&1 || true
                    terraform state rm kubernetes_service.docker_registry >/dev/null 2>&1 || true

                    terraform import kubernetes_deployment.mysql "${NAMESPACE}/mysql" >/dev/null 2>&1 || true
                    terraform import kubernetes_deployment.app "${NAMESPACE}/consulta-medica" >/dev/null 2>&1 || true
                    terraform import kubernetes_service.docker_registry "${NAMESPACE}/docker-registry" >/dev/null 2>&1 || true

                                        terraform apply -auto-approve -input=false -parallelism=5 \
                                            -var='kubeconfig_path=' \
                                            -var='app_replicas=1' \
                                            -var='app_image=consulta-medica:latest'
                '''
            }
        }

        stage('Build imagen') {
            steps {
                sh '''
                    set -e
                    docker build -t ${FULL_IMAGE} -f docker/Dockerfile .
                    docker tag ${FULL_IMAGE} ${LATEST_IMAGE}
                '''
            }
        }

        stage('Registro local (omitido en modo local)') {
            steps {
                sh '''
                    echo "Modo local: se omite push al registry."
                    echo "Se usará imagen local ${LATEST_IMAGE} con imagePullPolicy IfNotPresent."
                '''
            }
        }

        stage('Deploy Kubernetes') {
            steps {
                sh '''
                    set -e
                    kubectl -n ${NAMESPACE} patch deployment consulta-medica --type=json \
                      -p='[{"op":"replace","path":"/spec/template/spec/containers/0/imagePullPolicy","value":"IfNotPresent"}]' || true
                    kubectl -n ${NAMESPACE} set image deployment/consulta-medica \
                        consulta-medica=${LATEST_IMAGE}
                    kubectl -n ${NAMESPACE} scale deployment/consulta-medica --replicas=1
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
