terraform {
  required_version = ">= 1.0"
  required_providers {
    kubernetes = {
      source  = "hashicorp/kubernetes"
      version = "~> 2.0"
    }
  }
}

variable "kubeconfig_path" {
  description = "Ruta del kubeconfig para ejecución local. Vacío para usar credenciales in-cluster."
  type        = string
  default     = "~/.kube/config"
}

variable "kubeconfig_context" {
  description = "Contexto opcional del kubeconfig"
  type        = string
  default     = ""
}

provider "kubernetes" {
  config_path    = trimspace(var.kubeconfig_path) != "" ? pathexpand(var.kubeconfig_path) : null
  config_context = trimspace(var.kubeconfig_context) != "" ? var.kubeconfig_context : null
}

variable "namespace" {
  description = "Namespace para todos los recursos del laboratorio"
  type        = string
  default     = "consulta-medica"
}

variable "app_image" {
  description = "Imagen de la aplicación. En entorno local usar imagen disponible en el daemon de Docker Desktop"
  type        = string
  default     = "consulta-medica:fix-mbstring"
}

variable "app_image_pull_policy" {
  description = "Política de pull para la imagen de la app"
  type        = string
  default     = "IfNotPresent"
}

variable "app_replicas" {
  description = "Réplicas iniciales de la app"
  type        = number
  default     = 1
}

variable "service_type" {
  description = "Tipo de servicio para exposiciones locales"
  type        = string
  default     = "LoadBalancer"
}

variable "wait_for_load_balancer" {
  description = "Espera a que el Service tipo LoadBalancer obtenga endpoint externo durante apply"
  type        = bool
  default     = false
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
  wait_for_rollout = true

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
          image = "mysql:8.0"

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
  wait_for_load_balancer = var.wait_for_load_balancer

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

resource "kubernetes_config_map" "mysql_init_sql" {
  metadata {
    name      = "mysql-init-sql"
    namespace = kubernetes_namespace.consulta_medica.metadata[0].name
  }

  data = {
    "database.sql" = file("${path.module}/../app/consulta_medica/sql/database.sql")
  }
}

resource "kubernetes_job" "mysql_seed" {
  metadata {
    name      = "mysql-seed"
    namespace = kubernetes_namespace.consulta_medica.metadata[0].name
  }

  spec {
    backoff_limit = 3

    template {
      metadata {
        labels = {
          app = "mysql-seed"
        }
      }

      spec {
        restart_policy = "OnFailure"

        container {
          name  = "mysql-seed"
          image = "mysql:8.0"

          env {
            name  = "DB_HOST"
            value = kubernetes_service.mysql.metadata[0].name
          }

          env {
            name  = "DB_NAME"
            value = var.db_name
          }

          env {
            name  = "DB_USER"
            value = var.db_user
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

          command = ["/bin/sh", "-c"]
          args = [
            <<-EOT
              set -e
              echo "Esperando MySQL en $${DB_HOST}:3306..."
              MYSQL_CMD="mysql --default-character-set=utf8mb4 -h\"$${DB_HOST}\" -u\"$${DB_USER}\" -p\"$${DB_PASSWORD}\""

              until sh -lc "$${MYSQL_CMD} -Nse 'SELECT 1'" >/dev/null 2>&1; do
                sleep 3
              done

              TABLE_EXISTS=$(sh -lc "$${MYSQL_CMD} -Nse \"SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$${DB_NAME}' AND table_name='usuarios';\"")

              if [ "$${TABLE_EXISTS}" = "0" ]; then
                echo "Schema no encontrado. Importando /seed/database.sql..."
                sh -lc "$${MYSQL_CMD} \"$${DB_NAME}\" < /seed/database.sql"
                echo "Inicialización completada"
              else
                echo "Schema ya inicializado. No se requiere import"
              fi
            EOT
          ]

          volume_mount {
            name       = "seed-sql"
            mount_path = "/seed"
          }
        }

        volume {
          name = "seed-sql"
          config_map {
            name = kubernetes_config_map.mysql_init_sql.metadata[0].name
          }
        }
      }
    }
  }

  wait_for_completion = true

  depends_on = [
    kubernetes_deployment.mysql,
    kubernetes_service.mysql
  ]
}

resource "kubernetes_deployment" "app" {
  wait_for_rollout = false

  metadata {
    name      = "consulta-medica"
    namespace = kubernetes_namespace.consulta_medica.metadata[0].name
    labels = {
      app = "consulta-medica"
    }
  }

  spec {
    replicas = var.app_replicas

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
          image_pull_policy = var.app_image_pull_policy

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
  wait_for_load_balancer = var.wait_for_load_balancer

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

resource "kubernetes_role_binding" "prometheus_admin" {
  metadata {
    name      = "prometheus-admin-${var.namespace}"
    namespace = kubernetes_namespace.consulta_medica.metadata[0].name
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
  wait_for_load_balancer = var.wait_for_load_balancer

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
  wait_for_load_balancer = var.wait_for_load_balancer

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
  wait_for_load_balancer = var.wait_for_load_balancer

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

resource "kubernetes_role_binding" "jenkins_admin" {
  metadata {
    name      = "jenkins-admin-${var.namespace}"
    namespace = kubernetes_namespace.consulta_medica.metadata[0].name
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
  wait_for_load_balancer = var.wait_for_load_balancer

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
