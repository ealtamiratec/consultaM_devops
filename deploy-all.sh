#!/bin/bash

###############################################################################
# Script Maestro de Despliegue Completo
# Despliega toda la solución DevOps con Jenkins en Kubernetes (Docker Desktop)
###############################################################################

set -e

# Colores para salida
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m'

# Variables globales
PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
NAMESPACE="consulta-medica"
DOCKER_REGISTRY="localhost:5000"
DOCKER_INSECURE_REGISTRY="true"
TERRAFORM_DIR="$PROJECT_ROOT/terraform"
JENKINS_DIR="$PROJECT_ROOT/jenkins"
K8S_DIR="$PROJECT_ROOT/k8s"
DOCKER_DIR="$PROJECT_ROOT/docker"
DASHBOARD_NAMESPACE="kubernetes-dashboard"
DASHBOARD_URL=""
DASHBOARD_TOKEN=""

# Funciones de logging
log_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[✓]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[!]${NC} $1"
}

log_error() {
    echo -e "${RED}[✗]${NC} $1"
}

log_header() {
    echo ""
    echo -e "${CYAN}╔════════════════════════════════════════════════════════════╗${NC}"
    echo -e "${CYAN}║${NC} $1"
    echo -e "${CYAN}╚════════════════════════════════════════════════════════════╝${NC}"
    echo ""
}

# Función para pausar
pause() {
    read -p "Presiona Enter para continuar..."
}

ensure_kubernetes_connection() {
    if kubectl cluster-info &>/dev/null; then
        log_success "Kubernetes disponible"
        return 0
    fi

    log_error "No se puede conectar a Kubernetes con el contexto actual"
    log_info "Contexto activo: $(kubectl config current-context 2>/dev/null || echo 'no definido')"
    log_info "Configura un clúster accesible y vuelve a ejecutar este script"
    return 1
}

# Verificar requisitos previos
check_requirements() {
    log_header "VERIFICACIÓN DE REQUISITOS"
    
    local missing_tools=0
    
    # Verificar Docker
    if ! command -v docker &> /dev/null; then
        log_error "Docker no está instalado"
        missing_tools=$((missing_tools + 1))
    else
        log_success "Docker instalado: $(docker --version)"
    fi
    
    # Verificar kubectl
    if ! command -v kubectl &> /dev/null; then
        log_error "kubectl no está instalado"
        missing_tools=$((missing_tools + 1))
    else
        log_success "kubectl instalado: $(kubectl version --client --short 2>/dev/null || echo 'versión desconocida')"
    fi
    
    # Verificar Kubernetes
    if ! ensure_kubernetes_connection; then
        missing_tools=$((missing_tools + 1))
    fi
    
    # Verificar Terraform
    if ! command -v terraform &> /dev/null; then
        log_error "Terraform no está instalado"
        missing_tools=$((missing_tools + 1))
    else
        log_success "Terraform instalado: $(terraform version -json 2>/dev/null | grep terraform_version | head -1 || echo 'versión desconocida')"
    fi
    
    # Verificar Git
    if ! command -v git &> /dev/null; then
        log_error "Git no está instalado"
        missing_tools=$((missing_tools + 1))
    else
        log_success "Git instalado: $(git --version)"
    fi
    
    if [ $missing_tools -gt 0 ]; then
        log_error "Faltan $missing_tools herramientas requeridas"
        exit 1
    fi
    
    log_success "Todos los requisitos están instalados"
}

# Configurar Docker para registro inseguro
configure_docker() {
    log_header "CONFIGURACIÓN DE DOCKER"
    
    log_info "Verificando configuración de Docker para registro inseguro..."
    
    # Obtener la ruta del daemon.json según el SO
    if [[ "$OSTYPE" == "darwin"* ]]; then
        # macOS - Docker Desktop
        log_warning "En macOS, debes configurar Docker Desktop manualmente:"
        echo "  1. Abre Docker Desktop"
        echo "  2. Ve a Settings > Docker Engine"
        echo "  3. Agrega: \"insecure-registries\": [\"localhost:5000\"]"
        echo "  4. Haz clic en Apply & Restart"
        pause
    elif [[ "$OSTYPE" == "linux-gnu"* ]]; then
        # Linux
        if [ ! -f /etc/docker/daemon.json ]; then
            log_info "Creando /etc/docker/daemon.json..."
            sudo tee /etc/docker/daemon.json > /dev/null <<EOF
{
  "insecure-registries": ["localhost:5000"]
}
EOF
        else
            log_warning "El archivo /etc/docker/daemon.json ya existe"
            log_info "Verifica que contiene: \"insecure-registries\": [\"localhost:5000\"]"
        fi
        
        log_info "Reiniciando Docker..."
        sudo systemctl restart docker || log_warning "No se pudo reiniciar Docker automáticamente"
    fi
    
    log_success "Configuración de Docker completada"
}

# Crear namespace
create_namespace() {
    log_header "CREACIÓN DE NAMESPACE"
    
    log_info "Verificando namespace $NAMESPACE..."
    
    if kubectl get namespace "$NAMESPACE" &>/dev/null; then
        log_warning "Namespace $NAMESPACE ya existe"
    else
        log_info "Creando namespace $NAMESPACE..."
        kubectl create namespace "$NAMESPACE"
        log_success "Namespace creado"
    fi
    
    # Etiquetar namespace
    kubectl label namespace "$NAMESPACE" name="$NAMESPACE" --overwrite 2>/dev/null || true
}

# Desplegar infraestructura base con Terraform
deploy_terraform() {
    log_header "DESPLIEGUE CON TERRAFORM"
    
    log_info "Navegando a directorio de Terraform: $TERRAFORM_DIR"
    cd "$TERRAFORM_DIR"
    
    log_info "Inicializando Terraform..."
    terraform init

    log_info "Saneando estado Terraform para recursos Kubernetes críticos..."
    terraform state rm kubernetes_deployment.mysql >/dev/null 2>&1 || true
    terraform state rm kubernetes_deployment.app >/dev/null 2>&1 || true
    terraform state rm kubernetes_service.docker_registry >/dev/null 2>&1 || true

    terraform import kubernetes_deployment.mysql "${NAMESPACE}/mysql" >/dev/null 2>&1 || true
    terraform import kubernetes_deployment.app "${NAMESPACE}/consulta-medica" >/dev/null 2>&1 || true
    terraform import kubernetes_service.docker_registry "${NAMESPACE}/docker-registry" >/dev/null 2>&1 || true
    
    log_info "Planificando despliegue..."
    terraform plan -out=tfplan
    
    log_info "Aplicando configuración..."
    terraform apply -parallelism=5 tfplan
    
    log_success "Despliegue de Terraform completado"
    cd "$PROJECT_ROOT"
}

# Desplegar Jenkins
deploy_jenkins() {
    log_header "JENKINS GESTIONADO POR TERRAFORM"
    log_info "No se aplica manifiesto YAML directo para Jenkins."
    log_info "Jenkins se aprovisiona únicamente desde terraform/main.tf."
}

# Desplegar Docker Registry
deploy_registry() {
    log_header "REGISTRY GESTIONADO POR TERRAFORM"
    log_info "No se aplica manifiesto YAML directo para Docker Registry."
    log_info "Docker Registry se aprovisiona únicamente desde terraform/main.tf."
}

# Desplegar aplicación y dependencias
deploy_application() {
    log_header "APP Y MYSQL GESTIONADOS POR TERRAFORM"
    log_info "No se aplican manifiestos YAML directos de app/mysql."
    log_info "App y MySQL se aprovisionan únicamente desde terraform/main.tf."
    log_info "Las actualizaciones de código de app se realizan por Jenkinsfile (build/push/set image)."
}

# Desplegar observabilidad
deploy_observability() {
    log_header "OBSERVABILIDAD GESTIONADA POR TERRAFORM"
    log_info "No se aplican manifiestos YAML directos de observabilidad."
    log_info "Prometheus y Grafana se aprovisionan únicamente desde terraform/main.tf."
}

setup_kubernetes_dashboard() {
    log_header "AUTOMATIZACIÓN KUBERNETES DASHBOARD"

    local current_context
    current_context="$(kubectl config current-context 2>/dev/null || true)"

    if [ "$current_context" != "minikube" ] || ! command -v minikube >/dev/null 2>&1; then
        log_warning "El Dashboard está configurado únicamente para Minikube. Contexto actual: ${current_context:-no definido}"
        return 0
    fi

    log_info "Contexto Minikube detectado. Habilitando addons requeridos..."
    minikube addons enable dashboard >/dev/null 2>&1 || true
    minikube addons enable metrics-server >/dev/null 2>&1 || true

    kubectl -n "$DASHBOARD_NAMESPACE" rollout status deploy/kubernetes-dashboard --timeout=180s >/dev/null 2>&1 || true
    kubectl -n "$DASHBOARD_NAMESPACE" rollout status deploy/dashboard-metrics-scraper --timeout=180s >/dev/null 2>&1 || true

    if ! kubectl get namespace "$DASHBOARD_NAMESPACE" &>/dev/null; then
        log_warning "No se encontró el namespace $DASHBOARD_NAMESPACE. Se omite automatización de Dashboard."
        return 0
    fi

    log_info "Creando/actualizando credenciales de acceso al Dashboard..."
    kubectl -n "$DASHBOARD_NAMESPACE" create serviceaccount dashboard-admin --dry-run=client -o yaml | kubectl apply -f - >/dev/null
    kubectl create clusterrolebinding dashboard-admin --clusterrole=cluster-admin --serviceaccount=${DASHBOARD_NAMESPACE}:dashboard-admin >/dev/null 2>&1 || true

    DASHBOARD_TOKEN="$(kubectl -n "$DASHBOARD_NAMESPACE" create token dashboard-admin --duration=24h 2>/dev/null || true)"

    kubectl -n "$DASHBOARD_NAMESPACE" patch svc kubernetes-dashboard -p '{"spec":{"type":"NodePort"}}' >/dev/null 2>&1 || true

    local minikube_ip
    local dashboard_node_port
    minikube_ip="$(minikube ip 2>/dev/null || true)"

    for _ in {1..10}; do
        dashboard_node_port="$(kubectl -n "$DASHBOARD_NAMESPACE" get svc kubernetes-dashboard -o jsonpath='{.spec.ports[0].nodePort}' 2>/dev/null || true)"
        if [ -n "$dashboard_node_port" ]; then
            break
        fi
        sleep 1
    done

    if [ -n "$minikube_ip" ] && [ -n "$dashboard_node_port" ]; then
        DASHBOARD_URL="http://${minikube_ip}:${dashboard_node_port}"
    fi

    if [ -z "$DASHBOARD_URL" ]; then
        local minikube_service_log="/tmp/minikube-dashboard-service-url.log"
        pkill -f "minikube service -n ${DASHBOARD_NAMESPACE} kubernetes-dashboard --url" 2>/dev/null || true
        nohup minikube service -n "$DASHBOARD_NAMESPACE" kubernetes-dashboard --url > "$minikube_service_log" 2>&1 &

        for _ in {1..15}; do
            DASHBOARD_URL="$(grep -Eo 'http://127\.0\.0\.1:[0-9]+' "$minikube_service_log" | tail -n 1 || true)"
            if [ -n "$DASHBOARD_URL" ]; then
                break
            fi
            sleep 1
        done
    fi

    if [ -n "$DASHBOARD_URL" ]; then
        log_success "Dashboard de Minikube automatizado correctamente"
    else
        log_warning "No se pudo obtener URL de Dashboard en Minikube"
    fi
}

# Mostrar información de acceso
show_access_info() {
    log_header "INFORMACIÓN DE ACCESO"
    
    echo ""
    echo -e "${CYAN}Servicios disponibles:${NC}"
    echo ""
    
    # Jenkins
    log_info "Jenkins"
    echo "  URL: http://localhost:8080"
    echo "  Usuario: admin"
    echo "  Contraseña: admin"
    echo ""
    
    # Docker Registry
    log_info "Docker Registry"
    echo "  URL: http://localhost:5000"
    echo "  Comando: docker push localhost:5000/imagen:tag"
    echo ""
    
    # Aplicación
    log_info "Aplicación Consulta Médica"
    echo "  URL: http://localhost"
    echo ""
    
    # MySQL
    log_info "MySQL"
    echo "  Host: mysql.consulta-medica.svc.cluster.local"
    echo "  Puerto: 3306"
    echo "  Usuario: consulta_user"
    echo "  Contraseña: consulta_pass"
    echo ""
    
    # Prometheus
    log_info "Prometheus"
    echo "  URL: http://localhost:9090"
    echo ""
    
    # Grafana
    log_info "Grafana"
    echo "  URL: http://localhost:3000"
    echo "  Usuario: admin"
    echo "  Contraseña: admin"
    echo ""

    if [ -n "$DASHBOARD_URL" ]; then
        log_info "Kubernetes Dashboard"
        echo "  URL: $DASHBOARD_URL"
        if [ -n "$DASHBOARD_TOKEN" ]; then
            echo "  Token:"
            echo "  $DASHBOARD_TOKEN"
        else
            echo "  Token: kubectl -n $DASHBOARD_NAMESPACE create token dashboard-admin --duration=24h"
        fi
        echo ""
    fi
}

# Verificar estado del despliegue
verify_deployment() {
    log_header "VERIFICACIÓN DE DESPLIEGUE"
    
    log_info "Estado de los pods:"
    kubectl get pods -n "$NAMESPACE"
    
    echo ""
    log_info "Estado de los servicios:"
    kubectl get svc -n "$NAMESPACE"
    
    echo ""
    log_info "Eventos recientes:"
    kubectl get events -n "$NAMESPACE" --sort-by='.lastTimestamp' | tail -10
}

# Función principal
main() {
    clear
    
    echo -e "${CYAN}"
    cat << "EOF"
╔════════════════════════════════════════════════════════════╗
║                                                            ║
║     DESPLIEGUE COMPLETO - LABORATORIO DEVOPS              ║
║     Sistema de Consulta Médica con Jenkins Local          ║
║                                                            ║
║     Kubernetes (Minikube/Docker Desktop) + Jenkins        ║
║                                                            ║
╚════════════════════════════════════════════════════════════╝
EOF
    echo -e "${NC}"
    
    echo ""
    log_info "Este script desplegará:"
    echo "  • Infraestructura completa con Terraform"
    echo "  • Jenkins Pipeline como único camino para actualizar la app"
    echo "  • Verificación final de estado de servicios"
    echo ""
    
    read -p "¿Deseas continuar? (s/n) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Ss]$ ]]; then
        log_warning "Despliegue cancelado"
        exit 0
    fi
    
    # Ejecutar pasos de despliegue
    check_requirements
    configure_docker
    create_namespace
    deploy_terraform
    setup_kubernetes_dashboard
    verify_deployment
    show_access_info
    
    log_header "DESPLIEGUE COMPLETADO EXITOSAMENTE"
    
    log_success "La solución DevOps está lista"
    echo ""
    echo "Próximos pasos:"
    echo "  1. Abre Jenkins en http://localhost:8080"
    echo "  2. Crea un nuevo Job de tipo Pipeline"
    echo "  3. Configura el SCM con tu repositorio Git"
    echo "  4. Asegúrate de que el Script Path sea 'Jenkinsfile'"
    echo "  5. Haz clic en 'Build Now' para ejecutar el pipeline"
    echo ""
}

# Ejecutar
main "$@"
