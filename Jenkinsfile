pipeline {
    agent any

    parameters {
        choice(
            name: 'REGISTRY_TARGET',
            choices: ['local', 'dockerhub'],
            description: 'Destino de imagen: registry local del cluster o Docker Hub'
        )
        string(
            name: 'DOCKERHUB_NAMESPACE',
            defaultValue: 'ealtamiratec',
            description: 'Namespace/usuario de Docker Hub (si REGISTRY_TARGET=dockerhub)'
        )
        string(
            name: 'DOCKERHUB_CREDENTIALS_ID',
            defaultValue: 'dockerhub-creds',
            description: 'Credentials ID en Jenkins para Docker Hub'
        )
    }

    triggers {
        pollSCM('H/5 * * * *')
    }

    environment {
        APP_NAME = 'consulta-medica'
        NAMESPACE = 'consulta-medica'
        DEPLOYMENT_NAME = 'consulta-medica'
        DOCKERFILE_PATH = 'docker/Dockerfile'
        LOCAL_REGISTRY = 'consulta-medica'
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

        stage('Build + Tag imagen') {
            steps {
                script {
                    env.SHORT_SHA = sh(script: 'git rev-parse --short=7 HEAD', returnStdout: true).trim()
                    env.BUILD_TAG = "build-${env.BUILD_NUMBER}"
                    env.SHA_TAG = "sha-${env.SHORT_SHA}"

                    if (params.REGISTRY_TARGET == 'dockerhub') {
                        env.IMAGE_REPO = "docker.io/${params.DOCKERHUB_NAMESPACE}/${env.APP_NAME}"
                    } else {
                        env.IMAGE_REPO = "${env.LOCAL_REGISTRY}"
                    }

                    env.DEPLOY_IMAGE = "${env.IMAGE_REPO}:${env.SHA_TAG}"

                    sh '''
                        set -e
                        echo "Building ${IMAGE_REPO}:${BUILD_TAG}"
                        docker build -f ${DOCKERFILE_PATH} -t ${IMAGE_REPO}:${BUILD_TAG} .
                        docker tag ${IMAGE_REPO}:${BUILD_TAG} ${IMAGE_REPO}:${SHA_TAG}
                        docker tag ${IMAGE_REPO}:${BUILD_TAG} ${IMAGE_REPO}:latest
                    '''
                }
            }
        }

        stage('Push imagen') {
            steps {
                script {
                    if (params.REGISTRY_TARGET == 'dockerhub') {
                        withCredentials([usernamePassword(credentialsId: params.DOCKERHUB_CREDENTIALS_ID, usernameVariable: 'DOCKERHUB_USER', passwordVariable: 'DOCKERHUB_PASS')]) {
                            sh '''
                                set -e
                                echo "$DOCKERHUB_PASS" | docker login -u "$DOCKERHUB_USER" --password-stdin docker.io
                                docker push ${IMAGE_REPO}:${BUILD_TAG}
                                docker push ${IMAGE_REPO}:${SHA_TAG}
                                docker push ${IMAGE_REPO}:latest
                                docker logout docker.io || true
                            '''
                        }
                    } else {
                        sh '''
                            set -e
                            echo "Modo local: sin push remoto. Se usará imagen local ${IMAGE_REPO}:${SHA_TAG}."
                        '''
                    }
                }
            }
        }

        stage('Deploy Kubernetes (solo app)') {
            steps {
                sh '''
                    set -e
                    kubectl -n ${NAMESPACE} get deployment ${DEPLOYMENT_NAME}
                    kubectl -n ${NAMESPACE} set image deployment/${DEPLOYMENT_NAME} ${APP_NAME}=${DEPLOY_IMAGE}
                    kubectl -n ${NAMESPACE} rollout status deployment/${DEPLOYMENT_NAME} --timeout=5m
                '''
            }
        }

        stage('Verificación app') {
            steps {
                sh '''
                    set -e
                    kubectl -n ${NAMESPACE} get deployment ${DEPLOYMENT_NAME} -o wide
                    kubectl -n ${NAMESPACE} get pods -l app=${APP_NAME} -o wide
                '''
            }
        }
    }

    post {
        success {
            echo "Pipeline completado. Imagen desplegada: ${DEPLOY_IMAGE}"
        }
        failure {
            echo 'Pipeline falló. Revisa logs de build/push/deploy.'
        }
        always {
            sh 'docker image prune -f || true'
        }
    }
}
