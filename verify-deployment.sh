#!/bin/bash

###############################################################################
# Script de Verificación de Despliegue
# Valida que todos los componentes estén funcionando correctamente
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
CHECKS_PASSED=0
CHECKS_FAILED=0

# Funciones de logging
log_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[✓]${NC} $1"
    CHECKS_PASSED=$((CHECKS_PASSED + 1))
}

log_warning() {
    echo -e "${YELLOW}[!]${NC} $1"
}

log_error() {
    echo -e "${RED}[✗]${NC} $1"
    CHECKS_FAILED=$((CHECKS_FAILED + 1))
}

log_header() {
    echo ""
    echo -e "${CYAN}╔════════════════════════════════════════════════════════════╗${NC}"
    echo -e "${CYAN}║${NC} $1"
    echo -e "${CYAN}╚════════════════════════════════════════════════════════════╝${NC}"
    echo ""
}

# Verificar Kubernetes
check_kubernetes() {
    log_header "VERIFICACIÓN DE KUBERNETES"
    
    if kubectl cluster-info &>/dev/null; then
        log_success "Kubernetes está disponible"
        
        local nodes=$(kubectl get nodes --no-headers 2>/dev/null | wc -l)
        log_info "Nodos disponibles: $nodes"
    else
        log_error "No se puede conectar a Kubernetes"
        return 1
    fi
}

# Verificar namespace
check_namespace() {
    log_header "VERIFICACIÓN DE NAMESPACE"
    
    if kubectl get namespace "$NAMESPACE" &>/dev/null; then
        log_success "Namespace $NAMESPACE existe"
    else
        log_error "Namespace $NAMESPACE no existe"
        return 1
    fi
}

# Verificar pods
check_pods() {
    log_header "VERIFICACIÓN DE PODS"
    
    local pods=$(kubectl get pods -n "$NAMESPACE" --no-headers 2>/dev/null | wc -l)
    
    if [ "$pods" -gt 0 ]; then
        log_success "Se encontraron $pods pods"
        
        echo ""
        log_info "Estado de los pods:"
        kubectl get pods -n "$NAMESPACE" --no-headers | while read -r line; do
            local status=$(echo "$line" | awk '{print $3}')
            local pod_name=$(echo "$line" | awk '{print $1}')
            
            if [ "$status" = "Running" ]; then
                log_success "  $pod_name - Running"
            else
                log_warning "  $pod_name - $status"
            fi
        done
    else
        log_error "No hay pods en el namespace"
        return 1
    fi
}

# Verificar servicios
check_services() {
    log_header "VERIFICACIÓN DE SERVICIOS"
    
    local services=$(kubectl get svc -n "$NAMESPACE" --no-headers 2>/dev/null | wc -l)
    
    if [ "$services" -gt 0 ]; then
        log_success "Se encontraron $services servicios"
        
        echo ""
        log_info "Servicios disponibles:"
        kubectl get svc -n "$NAMESPACE" --no-headers | while read -r line; do
            local svc_name=$(echo "$line" | awk '{print $1}')
            local svc_type=$(echo "$line" | awk '{print $2}')
            local svc_ip=$(echo "$line" | awk '{print $3}')
            
            log_info "  $svc_name ($svc_type) - IP: $svc_ip"
        done
    else
        log_error "No hay servicios en el namespace"
        return 1
    fi
}

# Verificar Jenkins
check_jenkins() {
    log_header "VERIFICACIÓN DE JENKINS"
    
    local jenkins_pod=$(kubectl get pod -n "$NAMESPACE" -l app=jenkins -o jsonpath='{.items[0].metadata.name}' 2>/dev/null || echo "")
    
    if [ -z "$jenkins_pod" ]; then
        log_error "Pod de Jenkins no encontrado"
        return 1
    fi
    
    local jenkins_status=$(kubectl get pod -n "$NAMESPACE" "$jenkins_pod" -o jsonpath='{.status.phase}' 2>/dev/null || echo "Unknown")
    
    if [ "$jenkins_status" = "Running" ]; then
        log_success "Jenkins está corriendo"
        
        # Intentar conectar a Jenkins
        if curl -s http://localhost:8080/login > /dev/null 2>&1; then
            log_success "Jenkins es accesible en http://localhost:8080"
        else
            log_warning "No se puede acceder a Jenkins en http://localhost:8080"
        fi
    else
        log_error "Jenkins no está corriendo (estado: $jenkins_status)"
        return 1
    fi
}

# Verificar Docker Registry
check_registry() {
    log_header "VERIFICACIÓN DE DOCKER REGISTRY"
    
    local registry_pod=$(kubectl get pod -n "$NAMESPACE" -l app=docker-registry -o jsonpath='{.items[0].metadata.name}' 2>/dev/null || echo "")
    
    if [ -z "$registry_pod" ]; then
        log_error "Pod de Docker Registry no encontrado"
        return 1
    fi
    
    local registry_status=$(kubectl get pod -n "$NAMESPACE" "$registry_pod" -o jsonpath='{.status.phase}' 2>/dev/null || echo "Unknown")
    
    if [ "$registry_status" = "Running" ]; then
        log_success "Docker Registry está corriendo"
        
        # Intentar conectar al registry
        if curl -s http://localhost:5000/v2/ > /dev/null 2>&1; then
            log_success "Docker Registry es accesible en http://localhost:5000"
        else
            log_warning "No se puede acceder a Docker Registry en http://localhost:5000"
        fi
    else
        log_error "Docker Registry no está corriendo (estado: $registry_status)"
        return 1
    fi
}

# Verificar aplicación
check_application() {
    log_header "VERIFICACIÓN DE APLICACIÓN"
    
    local app_pod=$(kubectl get pod -n "$NAMESPACE" -l app=consulta-medica -o jsonpath='{.items[0].metadata.name}' 2>/dev/null || echo "")
    
    if [ -z "$app_pod" ]; then
        log_warning "Pod de aplicación no encontrado (puede no estar desplegado aún)"
        return 0
    fi
    
    local app_status=$(kubectl get pod -n "$NAMESPACE" "$app_pod" -o jsonpath='{.status.phase}' 2>/dev/null || echo "Unknown")
    
    if [ "$app_status" = "Running" ]; then
        log_success "Aplicación está corriendo"
        
        # Intentar conectar a la aplicación
        if curl -s http://localhost > /dev/null 2>&1; then
            log_success "Aplicación es accesible en http://localhost"
        else
            log_warning "No se puede acceder a la aplicación en http://localhost"
        fi
    else
        log_warning "Aplicación no está corriendo (estado: $app_status)"
    fi
}

# Verificar MySQL
check_mysql() {
    log_header "VERIFICACIÓN DE MYSQL"
    
    local mysql_pod=$(kubectl get pod -n "$NAMESPACE" -l app=mysql -o jsonpath='{.items[0].metadata.name}' 2>/dev/null || echo "")
    
    if [ -z "$mysql_pod" ]; then
        log_warning "Pod de MySQL no encontrado (puede no estar desplegado aún)"
        return 0
    fi
    
    local mysql_status=$(kubectl get pod -n "$NAMESPACE" "$mysql_pod" -o jsonpath='{.status.phase}' 2>/dev/null || echo "Unknown")
    
    if [ "$mysql_status" = "Running" ]; then
        log_success "MySQL está corriendo"
    else
        log_warning "MySQL no está corriendo (estado: $mysql_status)"
    fi
}

# Verificar Prometheus
check_prometheus() {
    log_header "VERIFICACIÓN DE PROMETHEUS"
    
    local prometheus_pod=$(kubectl get pod -n "$NAMESPACE" -l app=prometheus -o jsonpath='{.items[0].metadata.name}' 2>/dev/null || echo "")
    
    if [ -z "$prometheus_pod" ]; then
        log_warning "Pod de Prometheus no encontrado (puede no estar desplegado aún)"
        return 0
    fi
    
    local prometheus_status=$(kubectl get pod -n "$NAMESPACE" "$prometheus_pod" -o jsonpath='{.status.phase}' 2>/dev/null || echo "Unknown")
    
    if [ "$prometheus_status" = "Running" ]; then
        log_success "Prometheus está corriendo"
        
        # Intentar conectar a Prometheus
        if curl -s http://localhost:9090 > /dev/null 2>&1; then
            log_success "Prometheus es accesible en http://localhost:9090"
        else
            log_warning "No se puede acceder a Prometheus en http://localhost:9090"
        fi
    else
        log_warning "Prometheus no está corriendo (estado: $prometheus_status)"
    fi
}

# Verificar Grafana
check_grafana() {
    log_header "VERIFICACIÓN DE GRAFANA"
    
    local grafana_pod=$(kubectl get pod -n "$NAMESPACE" -l app=grafana -o jsonpath='{.items[0].metadata.name}' 2>/dev/null || echo "")
    
    if [ -z "$grafana_pod" ]; then
        log_warning "Pod de Grafana no encontrado (puede no estar desplegado aún)"
        return 0
    fi
    
    local grafana_status=$(kubectl get pod -n "$NAMESPACE" "$grafana_pod" -o jsonpath='{.status.phase}' 2>/dev/null || echo "Unknown")
    
    if [ "$grafana_status" = "Running" ]; then
        log_success "Grafana está corriendo"
        
        # Intentar conectar a Grafana
        if curl -s http://localhost:3000 > /dev/null 2>&1; then
            log_success "Grafana es accesible en http://localhost:3000"
        else
            log_warning "No se puede acceder a Grafana en http://localhost:3000"
        fi
    else
        log_warning "Grafana no está corriendo (estado: $grafana_status)"
    fi
}

# Verificar volúmenes
check_volumes() {
    log_header "VERIFICACIÓN DE VOLÚMENES"
    
    local pvcs=$(kubectl get pvc -n "$NAMESPACE" --no-headers 2>/dev/null | wc -l)
    
    if [ "$pvcs" -gt 0 ]; then
        log_success "Se encontraron $pvcs volúmenes persistentes"
        
        echo ""
        log_info "Volúmenes disponibles:"
        kubectl get pvc -n "$NAMESPACE" --no-headers | while read -r line; do
            local pvc_name=$(echo "$line" | awk '{print $1}')
            local pvc_status=$(echo "$line" | awk '{print $2}')
            local pvc_size=$(echo "$line" | awk '{print $4}')
            
            if [ "$pvc_status" = "Bound" ]; then
                log_success "  $pvc_name - $pvc_size (Bound)"
            else
                log_warning "  $pvc_name - $pvc_size ($pvc_status)"
            fi
        done
    else
        log_warning "No hay volúmenes persistentes"
    fi
}

# Mostrar resumen
show_summary() {
    log_header "RESUMEN DE VERIFICACIÓN"
    
    local total=$((CHECKS_PASSED + CHECKS_FAILED))
    
    echo ""
    log_success "Verificaciones pasadas: $CHECKS_PASSED"
    
    if [ $CHECKS_FAILED -gt 0 ]; then
        log_error "Verificaciones fallidas: $CHECKS_FAILED"
    fi
    
    echo ""
    echo "Total de verificaciones: $total"
    echo ""
    
    if [ $CHECKS_FAILED -eq 0 ]; then
        log_success "¡TODAS LAS VERIFICACIONES PASARON!"
    else
        log_warning "Algunas verificaciones fallaron. Revisa los errores arriba."
    fi
}

# Función principal
main() {
    clear
    
    echo -e "${CYAN}"
    cat << "EOF"
╔════════════════════════════════════════════════════════════╗
║                                                            ║
║     VERIFICACIÓN DE DESPLIEGUE                            ║
║     Laboratorio DevOps con Jenkins                        ║
║                                                            ║
╚════════════════════════════════════════════════════════════╝
EOF
    echo -e "${NC}"
    
    # Ejecutar verificaciones
    check_kubernetes || true
    check_namespace || true
    check_pods || true
    check_services || true
    check_jenkins || true
    check_registry || true
    check_application || true
    check_mysql || true
    check_prometheus || true
    check_grafana || true
    check_volumes || true
    
    # Mostrar resumen
    show_summary
}

# Ejecutar
main "$@"
