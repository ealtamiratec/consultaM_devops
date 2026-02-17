FROM jenkins/jenkins:lts-jdk17

USER root

RUN apt-get update && apt-get install -y --no-install-recommends \
    ca-certificates \
    curl \
    git \
    unzip \
    php-cli \
    && rm -rf /var/lib/apt/lists/*

RUN ARCH="$(dpkg --print-architecture)" \
    && case "$ARCH" in \
         amd64) DARCH="x86_64" ;; \
         arm64) DARCH="aarch64" ;; \
         *) echo "Arquitectura no soportada: $ARCH" && exit 1 ;; \
       esac \
    && DOCKER_VERSION="27.3.1" \
    && curl -fsSL "https://download.docker.com/linux/static/stable/${DARCH}/docker-${DOCKER_VERSION}.tgz" -o /tmp/docker.tgz \
    && tar -xzf /tmp/docker.tgz -C /tmp \
    && mv /tmp/docker/docker /usr/local/bin/docker \
    && chmod +x /usr/local/bin/docker \
    && rm -rf /tmp/docker /tmp/docker.tgz

RUN ARCH="$(dpkg --print-architecture)" \
    && case "$ARCH" in \
         amd64) KARCH="amd64" ;; \
         arm64) KARCH="arm64" ;; \
         *) echo "Arquitectura no soportada: $ARCH" && exit 1 ;; \
       esac \
    && curl -fsSL "https://dl.k8s.io/release/v1.34.2/bin/linux/${KARCH}/kubectl" -o /usr/local/bin/kubectl \
    && chmod +x /usr/local/bin/kubectl

RUN ARCH="$(dpkg --print-architecture)" \
    && case "$ARCH" in \
         amd64) TARCH="amd64" ;; \
         arm64) TARCH="arm64" ;; \
         *) echo "Arquitectura no soportada: $ARCH" && exit 1 ;; \
       esac \
    && TF_VERSION="1.14.4" \
    && curl -fsSL "https://releases.hashicorp.com/terraform/${TF_VERSION}/terraform_${TF_VERSION}_linux_${TARCH}.zip" -o /tmp/terraform.zip \
    && unzip -q /tmp/terraform.zip -d /usr/local/bin \
    && chmod +x /usr/local/bin/terraform \
    && rm -f /tmp/terraform.zip

USER jenkins
