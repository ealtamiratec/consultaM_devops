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
    if ! kubectl cluster-info &>/dev/null; then
        log_error "No se puede conectar a Kubernetes"
        missing_tools=$((missing_tools + 1))
    else
        log_success "Kubernetes disponible"
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
    
    log_info "Planificando despliegue..."
    terraform plan -out=tfplan
    
    log_info "Aplicando configuración..."
    terraform apply tfplan
    
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
║     Kubernetes (Docker Desktop) + Jenkins + Registry      ║
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
