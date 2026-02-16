#!/bin/bash

###############################################################################
# Script de Bootstrap de Jenkins y Docker Registry
# Aprovisiona Jenkins y Docker Registry vía Terraform
###############################################################################

set -e

# Colores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Funciones auxiliares
log_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Variables
NAMESPACE="consulta-medica"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
TERRAFORM_DIR="$PROJECT_ROOT/terraform"

# Verificar requisitos
check_requirements() {
    log_info "Verificando requisitos..."
    
    if ! command -v kubectl &> /dev/null; then
        log_error "kubectl no está instalado"
        exit 1
    fi
    
    if ! kubectl cluster-info &>/dev/null; then
        log_error "No se puede conectar a Kubernetes"
        exit 1
    fi
    
    log_success "Requisitos verificados"
}

# Crear namespace
create_namespace() {
    log_info "Creando namespace $NAMESPACE..."
    
    if kubectl get namespace "$NAMESPACE" &>/dev/null; then
        log_warning "Namespace $NAMESPACE ya existe"
    else
        kubectl create namespace "$NAMESPACE"
        log_success "Namespace creado"
    fi
}

# Aprovisionar Docker Registry y Jenkins con Terraform
deploy_registry() {
    log_info "Aprovisionando Docker Registry y Jenkins con Terraform..."

    cd "$TERRAFORM_DIR"
    terraform init -input=false
    terraform apply -auto-approve -input=false
    cd "$PROJECT_ROOT"

    log_success "Recursos aprovisionados por Terraform"
}

# Desplegar Jenkins
deploy_jenkins() {
    log_info "Esperando a que Jenkins esté listo..."
    kubectl wait --for=condition=ready pod -l app=jenkins -n "$NAMESPACE" --timeout=600s 2>/dev/null || true

    log_success "Jenkins desplegado"
}

# Obtener información de acceso
get_access_info() {
    log_info "Obteniendo información de acceso..."
    
    echo ""
    echo "╔════════════════════════════════════════════════════════════╗"
    echo "║   INFORMACIÓN DE ACCESO                                    ║"
    echo "╚════════════════════════════════════════════════════════════╝"
    echo ""
    
    # Jenkins
    log_info "Jenkins:"
    JENKINS_IP=$(kubectl get svc jenkins -n "$NAMESPACE" -o jsonpath='{.status.loadBalancer.ingress[0].ip}' 2>/dev/null || echo "localhost")
    JENKINS_PORT=$(kubectl get svc jenkins -n "$NAMESPACE" -o jsonpath='{.spec.ports[0].port}' 2>/dev/null || echo "8080")
    echo "  URL: http://$JENKINS_IP:$JENKINS_PORT"
    echo "  Usuario: admin"
    echo "  Contraseña: admin"
    echo ""
    
    # Docker Registry
    log_info "Docker Registry:"
    REGISTRY_IP=$(kubectl get svc docker-registry -n "$NAMESPACE" -o jsonpath='{.status.loadBalancer.ingress[0].ip}' 2>/dev/null || echo "localhost")
    REGISTRY_PORT=$(kubectl get svc docker-registry -n "$NAMESPACE" -o jsonpath='{.spec.ports[0].port}' 2>/dev/null || echo "5000")
    echo "  URL: http://$REGISTRY_IP:$REGISTRY_PORT"
    echo ""
    
    # Pods
    log_info "Estado de los pods:"
    kubectl get pods -n "$NAMESPACE" -l app=jenkins,app=docker-registry
    echo ""
}

# Configurar Docker para usar el registro local
configure_docker() {
    log_info "Configurando Docker para usar el registro local..."
    
    # Nota: En Docker Desktop, necesitas agregar el registro a la lista de registros inseguros
    log_warning "Asegúrate de configurar Docker Desktop para usar el registro local:"
    echo "  1. Abre Docker Desktop"
    echo "  2. Ve a Settings > Docker Engine"
    echo "  3. Agrega lo siguiente a la configuración JSON:"
    echo ""
    echo '  "insecure-registries": ["localhost:5000"]'
    echo ""
    echo "  4. Haz clic en Apply & Restart"
    echo ""
}

# Verificar despliegue
verify_deployment() {
    log_info "Verificando despliegue..."
    
    # Verificar Jenkins
    log_info "Verificando Jenkins..."
    if kubectl get pod -n "$NAMESPACE" -l app=jenkins &>/dev/null; then
        log_success "Jenkins está disponible"
    else
        log_error "Jenkins no está disponible"
        return 1
    fi
    
    # Verificar Docker Registry
    log_info "Verificando Docker Registry..."
    if kubectl get pod -n "$NAMESPACE" -l app=docker-registry &>/dev/null; then
        log_success "Docker Registry está disponible"
    else
        log_error "Docker Registry no está disponible"
        return 1
    fi
    
    log_success "Despliegue verificado"
}

# Función principal
main() {
    echo ""
    echo "╔════════════════════════════════════════════════════════════╗"
    echo "║   DESPLIEGUE - Jenkins y Docker Registry                  ║"
    echo "║   Laboratorio DevOps Local                                ║"
    echo "╚════════════════════════════════════════════════════════════╝"
    echo ""
    
    check_requirements
    create_namespace
    deploy_registry
    deploy_jenkins
    verify_deployment
    configure_docker
    get_access_info
    
    log_success "Despliegue completado exitosamente"
}

# Ejecutar
main "$@"
