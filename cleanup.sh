#!/bin/bash

###############################################################################
# Script de Limpieza
# Elimina todos los recursos desplegados en Kubernetes
###############################################################################

set -e

# Colores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m'

# Variables
NAMESPACE="consulta-medica"

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

# Función principal
main() {
    clear
    
    echo -e "${CYAN}"
    cat << "EOF"
╔════════════════════════════════════════════════════════════╗
║                                                            ║
║     LIMPIEZA - Eliminación de Recursos                    ║
║     Laboratorio DevOps                                    ║
║                                                            ║
╚════════════════════════════════════════════════════════════╝
EOF
    echo -e "${NC}"
    
    echo ""
    log_warning "ADVERTENCIA: Esta acción eliminará todos los recursos"
    echo "  • Namespace: $NAMESPACE"
    echo "  • Todos los Pods, Deployments, Services"
    echo "  • Volúmenes persistentes"
    echo ""
    
    read -p "¿Estás seguro de que deseas continuar? (s/n) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Ss]$ ]]; then
        log_info "Limpieza cancelada"
        exit 0
    fi
    
    read -p "¿Deseas eliminar el namespace completo? (s/n) " -n 1 -r
    echo
    local delete_namespace=false
    if [[ $REPLY =~ ^[Ss]$ ]]; then
        delete_namespace=true
    fi
    
    log_header "INICIANDO LIMPIEZA"
    
    # Verificar conexión a Kubernetes
    log_info "Verificando conexión a Kubernetes..."
    if ! kubectl cluster-info &>/dev/null; then
        log_error "No se puede conectar a Kubernetes"
        exit 1
    fi
    log_success "Conexión a Kubernetes establecida"
    
    # Eliminar recursos del namespace
    log_info "Eliminando recursos del namespace $NAMESPACE..."
    
    # Eliminar deployments
    log_info "Eliminando Deployments..."
    kubectl delete deployment -n "$NAMESPACE" --all 2>/dev/null || log_warning "No hay deployments para eliminar"
    
    # Eliminar services
    log_info "Eliminando Services..."
    kubectl delete service -n "$NAMESPACE" --all 2>/dev/null || log_warning "No hay services para eliminar"
    
    # Eliminar PVCs
    log_info "Eliminando PersistentVolumeClaims..."
    kubectl delete pvc -n "$NAMESPACE" --all 2>/dev/null || log_warning "No hay PVCs para eliminar"
    
    # Eliminar ConfigMaps
    log_info "Eliminando ConfigMaps..."
    kubectl delete configmap -n "$NAMESPACE" --all 2>/dev/null || log_warning "No hay ConfigMaps para eliminar"
    
    # Eliminar Secrets
    log_info "Eliminando Secrets..."
    kubectl delete secret -n "$NAMESPACE" --all 2>/dev/null || log_warning "No hay Secrets para eliminar"
    
    # Esperar a que se eliminen los recursos
    log_info "Esperando a que se eliminen los recursos..."
    sleep 5
    
    # Eliminar namespace si se solicita
    if [ "$delete_namespace" = true ]; then
        log_info "Eliminando namespace $NAMESPACE..."
        kubectl delete namespace "$NAMESPACE" 2>/dev/null || log_warning "No se pudo eliminar el namespace"
        
        log_info "Esperando a que se elimine el namespace..."
        sleep 10
    fi
    
    # Limpiar Terraform
    log_info "¿Deseas limpiar también los recursos de Terraform? (s/n)"
    read -p "" -n 1 -r
    echo
    if [[ $REPLY =~ ^[Ss]$ ]]; then
        log_info "Navegando a directorio de Terraform..."
        cd terraform
        
        log_info "Destruyendo infraestructura de Terraform..."
        terraform destroy -auto-approve || log_warning "Error al destruir infraestructura de Terraform"
        
        cd ..
        log_success "Infraestructura de Terraform destruida"
    fi
    
    log_header "LIMPIEZA COMPLETADA"
    
    log_success "Recursos eliminados exitosamente"
    echo ""
    echo "Estado actual:"
    kubectl get namespace "$NAMESPACE" 2>/dev/null || log_info "Namespace $NAMESPACE no existe"
}

# Ejecutar
main "$@"
