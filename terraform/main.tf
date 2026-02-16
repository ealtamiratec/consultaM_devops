terraform {
  required_version = ">= 1.0"
  required_providers {
    kubernetes = {
      source  = "hashicorp/kubernetes"
      version = "~> 2.0"
    }
  }
}

provider "kubernetes" {
}

variable "namespace" {
  description = "Namespace para todos los recursos del laboratorio"
  type        = string
  default     = "consulta-medica"
}

variable "app_image" {
  description = "Imagen de la aplicación que Jenkins actualizará en cada build"
  type        = string
  default     = "localhost:5000/consulta-medica:latest"
}

variable "service_type" {
  description = "Tipo de servicio para exposiciones locales"
  type        = string
  default     = "LoadBalancer"
}

variable "db_name" {
  type    = string
  default = "consulta_medica"
}

variable "db_user" {
  type    = string
  default = "consulta_app"
}

variable "db_password" {
  type    = string
  default = "TuContraseñaSegura"
}

variable "mysql_root_password" {
  type    = string
  default = "rootpassword"
}

resource "kubernetes_namespace" "consulta_medica" {
  metadata {
    name = var.namespace
  }
}

resource "kubernetes_config_map" "app_config" {
  metadata {
    name      = "app-config"
    namespace = kubernetes_namespace.consulta_medica.metadata[0].name
  }

  data = {
    APP_ENV       = "production"
    APP_URL       = "http://localhost"
    APP_BASE_PATH = ""
    DB_HOST       = "mysql"
    DB_PORT       = "3306"
    DB_DATABASE   = var.db_name
    DB_USER       = var.db_user
  }
}

resource "kubernetes_secret" "db_secret" {
  metadata {
    name      = "db-secret"
    namespace = kubernetes_namespace.consulta_medica.metadata[0].name
  }

  type = "Opaque"

  data = {
    MYSQL_ROOT_PASSWORD = var.mysql_root_password
    MYSQL_PASSWORD      = var.db_password
    DB_PASSWORD         = var.db_password
  }
}

resource "kubernetes_persistent_volume_claim" "mysql_pvc" {
  metadata {
    name      = "mysql-pvc"
    namespace = kubernetes_namespace.consulta_medica.metadata[0].name
  }

  spec {
    access_modes = ["ReadWriteOnce"]
    resources {
      requests = {
        storage = "10Gi"
      }
    }
  }
}

resource "kubernetes_deployment" "mysql" {
  metadata {
    name      = "mysql"
    namespace = kubernetes_namespace.consulta_medica.metadata[0].name
    labels = {
      app = "mysql"
    }
  }

  spec {
    replicas = 1

    selector {
      match_labels = {
        app = "mysql"
      }
    }

    template {
      metadata {
        labels = {
          app = "mysql"
        }
      }

      spec {
        container {
          name  = "mysql"
          image = "mysql:5.7"

          port {
            container_port = 3306
          }

          env {
            name = "MYSQL_ROOT_PASSWORD"
            value_from {
              secret_key_ref {
                name = kubernetes_secret.db_secret.metadata[0].name
                key  = "MYSQL_ROOT_PASSWORD"
              }
            }
          }

          env {
            name  = "MYSQL_DATABASE"
            value = var.db_name
          }

          env {
            name  = "MYSQL_USER"
            value = var.db_user
          }

          env {
            name = "MYSQL_PASSWORD"
            value_from {
              secret_key_ref {
                name = kubernetes_secret.db_secret.metadata[0].name
                key  = "MYSQL_PASSWORD"
              }
            }
          }

          volume_mount {
            name       = "mysql-storage"
            mount_path = "/var/lib/mysql"
          }
        }

        volume {
          name = "mysql-storage"
          persistent_volume_claim {
            claim_name = kubernetes_persistent_volume_claim.mysql_pvc.metadata[0].name
          }
        }
      }
    }
  }
}

resource "kubernetes_service" "mysql" {
  metadata {
    name      = "mysql"
    namespace = kubernetes_namespace.consulta_medica.metadata[0].name
  }

  spec {
    selector = {
      app = "mysql"
    }

    port {
      port        = 3306
      target_port = 3306
    }

    cluster_ip = "None"
  }
}

resource "kubernetes_deployment" "app" {
  metadata {
    name      = "consulta-medica"
    namespace = kubernetes_namespace.consulta_medica.metadata[0].name
    labels = {
      app = "consulta-medica"
    }
  }

  spec {
    replicas = 2

    selector {
      match_labels = {
        app = "consulta-medica"
      }
    }

    template {
      metadata {
        labels = {
          app = "consulta-medica"
        }
      }

      spec {
        container {
          name  = "consulta-medica"
          image = var.app_image

          port {
            container_port = 80
          }

          env_from {
            config_map_ref {
              name = kubernetes_config_map.app_config.metadata[0].name
            }
          }

          env {
            name = "DB_PASSWORD"
            value_from {
              secret_key_ref {
                name = kubernetes_secret.db_secret.metadata[0].name
                key  = "DB_PASSWORD"
              }
            }
          }

          liveness_probe {
            http_get {
              path = "/health.php"
              port = 80
            }
            initial_delay_seconds = 30
            period_seconds        = 10
          }

          readiness_probe {
            http_get {
              path = "/health.php"
              port = 80
            }
            initial_delay_seconds = 10
            period_seconds        = 5
          }

          resources {
            requests = {
              cpu    = "100m"
              memory = "128Mi"
            }
            limits = {
              cpu    = "500m"
              memory = "512Mi"
            }
          }
        }
      }
    }
  }
}

resource "kubernetes_service" "app" {
  metadata {
    name      = "consulta-medica"
    namespace = kubernetes_namespace.consulta_medica.metadata[0].name
  }

  spec {
    selector = {
      app = "consulta-medica"
    }

    port {
      port        = 80
      target_port = 80
    }

    type = var.service_type
  }
}

resource "kubernetes_config_map" "prometheus_config" {
  metadata {
    name      = "prometheus-config"
    namespace = kubernetes_namespace.consulta_medica.metadata[0].name
  }

  data = {
    "prometheus.yml" = file("${path.module}/../observability/prometheus/prometheus.yaml")
  }
}

resource "kubernetes_cluster_role_binding" "prometheus_admin" {
  metadata {
    name = "prometheus-admin-${var.namespace}"
  }

  subject {
    kind      = "ServiceAccount"
    name      = "default"
    namespace = kubernetes_namespace.consulta_medica.metadata[0].name
  }

  role_ref {
    kind      = "ClusterRole"
    name      = "cluster-admin"
    api_group = "rbac.authorization.k8s.io"
  }
}

resource "kubernetes_deployment" "prometheus" {
  metadata {
    name      = "prometheus"
    namespace = kubernetes_namespace.consulta_medica.metadata[0].name
    labels = {
      app = "prometheus"
    }
  }

  spec {
    replicas = 1

    selector {
      match_labels = {
        app = "prometheus"
      }
    }

    template {
      metadata {
        labels = {
          app = "prometheus"
        }
      }

      spec {
        container {
          name  = "prometheus"
          image = "prom/prometheus:latest"

          args = [
            "--config.file=/etc/prometheus/prometheus.yml",
            "--storage.tsdb.path=/prometheus"
          ]

          port {
            container_port = 9090
          }

          volume_mount {
            name       = "prometheus-config"
            mount_path = "/etc/prometheus/prometheus.yml"
            sub_path   = "prometheus.yml"
          }
        }

        volume {
          name = "prometheus-config"
          config_map {
            name = kubernetes_config_map.prometheus_config.metadata[0].name
          }
        }
      }
    }
  }
}

resource "kubernetes_service" "prometheus" {
  metadata {
    name      = "prometheus"
    namespace = kubernetes_namespace.consulta_medica.metadata[0].name
  }

  spec {
    selector = {
      app = "prometheus"
    }

    port {
      port        = 9090
      target_port = 9090
    }

    type = var.service_type
  }
}

resource "kubernetes_config_map" "grafana_datasource" {
  metadata {
    name      = "grafana-datasource"
    namespace = kubernetes_namespace.consulta_medica.metadata[0].name
  }

  data = {
    "prometheus.yaml" = file("${path.module}/../observability/grafana/grafana-datasource.yaml")
  }
}

resource "kubernetes_deployment" "grafana" {
  metadata {
    name      = "grafana"
    namespace = kubernetes_namespace.consulta_medica.metadata[0].name
    labels = {
      app = "grafana"
    }
  }

  spec {
    replicas = 1

    selector {
      match_labels = {
        app = "grafana"
      }
    }

    template {
      metadata {
        labels = {
          app = "grafana"
        }
      }

      spec {
        container {
          name  = "grafana"
          image = "grafana/grafana:latest"

          port {
            container_port = 3000
          }

          env {
            name  = "GF_SECURITY_ADMIN_PASSWORD"
            value = "admin"
          }

          volume_mount {
            name       = "grafana-datasource"
            mount_path = "/etc/grafana/provisioning/datasources/prometheus.yaml"
            sub_path   = "prometheus.yaml"
          }
        }

        volume {
          name = "grafana-datasource"
          config_map {
            name = kubernetes_config_map.grafana_datasource.metadata[0].name
          }
        }
      }
    }
  }
}

resource "kubernetes_service" "grafana" {
  metadata {
    name      = "grafana"
    namespace = kubernetes_namespace.consulta_medica.metadata[0].name
  }

  spec {
    selector = {
      app = "grafana"
    }

    port {
      port        = 3000
      target_port = 3000
    }

    type = var.service_type
  }
}

resource "kubernetes_persistent_volume_claim" "registry_pvc" {
  metadata {
    name      = "docker-registry-pvc"
    namespace = kubernetes_namespace.consulta_medica.metadata[0].name
  }

  spec {
    access_modes = ["ReadWriteOnce"]
    resources {
      requests = {
        storage = "20Gi"
      }
    }
  }
}

resource "kubernetes_deployment" "docker_registry" {
  metadata {
    name      = "docker-registry"
    namespace = kubernetes_namespace.consulta_medica.metadata[0].name
    labels = {
      app = "docker-registry"
    }
  }

  spec {
    replicas = 1

    selector {
      match_labels = {
        app = "docker-registry"
      }
    }

    template {
      metadata {
        labels = {
          app = "docker-registry"
        }
      }

      spec {
        container {
          name  = "docker-registry"
          image = "registry:2"

          port {
            container_port = 5000
          }

          env {
            name  = "REGISTRY_STORAGE_DELETE_ENABLED"
            value = "true"
          }

          volume_mount {
            name       = "registry-storage"
            mount_path = "/var/lib/registry"
          }
        }

        volume {
          name = "registry-storage"
          persistent_volume_claim {
            claim_name = kubernetes_persistent_volume_claim.registry_pvc.metadata[0].name
          }
        }
      }
    }
  }
}

resource "kubernetes_service" "docker_registry" {
  metadata {
    name      = "docker-registry"
    namespace = kubernetes_namespace.consulta_medica.metadata[0].name
  }

  spec {
    selector = {
      app = "docker-registry"
    }

    port {
      port        = 5000
      target_port = 5000
    }

    type = var.service_type
  }
}

resource "kubernetes_persistent_volume_claim" "jenkins_pvc" {
  metadata {
    name      = "jenkins-pvc"
    namespace = kubernetes_namespace.consulta_medica.metadata[0].name
  }

  spec {
    access_modes = ["ReadWriteOnce"]
    resources {
      requests = {
        storage = "20Gi"
      }
    }
  }
}

resource "kubernetes_service_account" "jenkins" {
  metadata {
    name      = "jenkins"
    namespace = kubernetes_namespace.consulta_medica.metadata[0].name
  }
}

resource "kubernetes_cluster_role_binding" "jenkins_admin" {
  metadata {
    name = "jenkins-admin-${var.namespace}"
  }

  subject {
    kind      = "ServiceAccount"
    name      = kubernetes_service_account.jenkins.metadata[0].name
    namespace = kubernetes_namespace.consulta_medica.metadata[0].name
  }

  role_ref {
    kind      = "ClusterRole"
    name      = "cluster-admin"
    api_group = "rbac.authorization.k8s.io"
  }
}

resource "kubernetes_deployment" "jenkins" {
  metadata {
    name      = "jenkins"
    namespace = kubernetes_namespace.consulta_medica.metadata[0].name
    labels = {
      app = "jenkins"
    }
  }

  spec {
    replicas = 1

    selector {
      match_labels = {
        app = "jenkins"
      }
    }

    template {
      metadata {
        labels = {
          app = "jenkins"
        }
      }

      spec {
        service_account_name = kubernetes_service_account.jenkins.metadata[0].name

        container {
          name  = "jenkins"
          image = "jenkins/jenkins:lts-jdk17"

          security_context {
            run_as_user = 0
          }

          command = ["/bin/bash", "-lc"]
          args = [
            <<-EOT
            apt-get update && apt-get install -y curl unzip docker.io && \
            curl -fsSLo /tmp/terraform.zip https://releases.hashicorp.com/terraform/1.6.6/terraform_1.6.6_linux_amd64.zip && \
            unzip -o /tmp/terraform.zip -d /usr/local/bin && chmod +x /usr/local/bin/terraform && \
            curl -fsSLo /usr/local/bin/kubectl https://dl.k8s.io/release/v1.29.1/bin/linux/amd64/kubectl && chmod +x /usr/local/bin/kubectl && \
            /usr/bin/tini -- /usr/local/bin/jenkins.sh
            EOT
          ]

          port {
            container_port = 8080
          }

          port {
            container_port = 50000
          }

          env {
            name  = "JAVA_OPTS"
            value = "-Xmx1024m -Xms512m"
          }

          env {
            name  = "JENKINS_OPTS"
            value = "--argumentsRealm.passwd.admin=admin --argumentsRealm.roles.admin=admin"
          }

          volume_mount {
            name       = "jenkins-home"
            mount_path = "/var/jenkins_home"
          }

          volume_mount {
            name       = "docker-sock"
            mount_path = "/var/run/docker.sock"
          }
        }

        volume {
          name = "jenkins-home"
          persistent_volume_claim {
            claim_name = kubernetes_persistent_volume_claim.jenkins_pvc.metadata[0].name
          }
        }

        volume {
          name = "docker-sock"
          host_path {
            path = "/var/run/docker.sock"
            type = "Socket"
          }
        }
      }
    }
  }
}

resource "kubernetes_service" "jenkins" {
  metadata {
    name      = "jenkins"
    namespace = kubernetes_namespace.consulta_medica.metadata[0].name
  }

  spec {
    selector = {
      app = "jenkins"
    }

    port {
      name        = "http"
      port        = 8080
      target_port = 8080
    }

    port {
      name        = "jnlp"
      port        = 50000
      target_port = 50000
    }

    type = var.service_type
  }
}

output "namespace" {
  value       = kubernetes_namespace.consulta_medica.metadata[0].name
  description = "Namespace desplegado"
}

output "app_image_default" {
  value       = var.app_image
  description = "Imagen por defecto para la app"
}

output "access_urls" {
  value = {
    app        = "http://localhost"
    prometheus = "http://localhost:9090"
    grafana    = "http://localhost:3000"
    jenkins    = "http://localhost:8080"
    registry   = "http://localhost:5000"
  }
  description = "URLs locales sugeridas (usando port-forward o LoadBalancer local)"
}
