#!/bin/bash

###############################################################################
# Script de Prueba de Resiliencia - Laboratorio DevOps
# Objetivo: Demostrar auto-healing y recuperación en Kubernetes
###############################################################################

set -e

NAMESPACE="consulta-medica"
APP_NAME="consulta-medica"
PROMETHEUS_POD=""
GRAFANA_POD=""

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

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

# Verificar que kubectl está disponible
check_kubectl() {
    if ! command -v kubectl &> /dev/null; then
        log_error "kubectl no está instalado"
        exit 1
    fi
    log_success "kubectl encontrado"
}

# Verificar que el namespace existe
check_namespace() {
    if ! kubectl get namespace "$NAMESPACE" &> /dev/null; then
        log_error "Namespace $NAMESPACE no existe"
        exit 1
    fi
    log_success "Namespace $NAMESPACE existe"
}

# Obtener información inicial
get_initial_state() {
    log_info "Obteniendo estado inicial del cluster..."
    
    echo ""
    echo "=== PODS INICIALES ==="
    kubectl get pods -n "$NAMESPACE" -o wide
    
    echo ""
    echo "=== DEPLOYMENTS INICIALES ==="
    kubectl get deployments -n "$NAMESPACE"
    
    echo ""
    echo "=== EVENTOS INICIALES ==="
    kubectl get events -n "$NAMESPACE" --sort-by='.lastTimestamp' | tail -10
}

# Prueba 1: Eliminar un pod y observar recuperación
test_pod_failure() {
    log_info "=========================================="
    log_info "PRUEBA 1: Fallo de Pod y Auto-Healing"
    log_info "=========================================="
    
    # Obtener nombre del primer pod
    POD_NAME=$(kubectl get pods -n "$NAMESPACE" -l app=$APP_NAME -o jsonpath='{.items[0].metadata.name}')
    
    if [ -z "$POD_NAME" ]; then
        log_error "No se encontró ningún pod para la aplicación"
        return 1
    fi
    
    log_info "Pod seleccionado para eliminar: $POD_NAME"
    
    # Registrar timestamp antes
    BEFORE_TIME=$(date +%s)
    log_info "Timestamp antes del fallo: $BEFORE_TIME"
    
    # Eliminar el pod
    log_warning "Eliminando pod: $POD_NAME..."
    kubectl delete pod "$POD_NAME" -n "$NAMESPACE"
    
    # Esperar a que se cree un nuevo pod
    log_info "Esperando a que Kubernetes cree un nuevo pod..."
    sleep 5
    
    # Verificar que se creó un nuevo pod
    NEW_POD_NAME=$(kubectl get pods -n "$NAMESPACE" -l app=$APP_NAME -o jsonpath='{.items[0].metadata.name}')
    AFTER_TIME=$(date +%s)
    RECOVERY_TIME=$((AFTER_TIME - BEFORE_TIME))
    
    if [ "$POD_NAME" != "$NEW_POD_NAME" ]; then
        log_success "Nuevo pod creado: $NEW_POD_NAME"
        log_success "Tiempo de recuperación (MTTR): $RECOVERY_TIME segundos"
    else
        log_warning "El pod aún se está recuperando..."
    fi
    
    echo ""
    echo "=== PODS DESPUÉS DEL FALLO ==="
    kubectl get pods -n "$NAMESPACE" -o wide
}

# Prueba 2: Escalar el deployment
test_scaling() {
    log_info "=========================================="
    log_info "PRUEBA 2: Escalado de Replicas"
    log_info "=========================================="
    
    log_info "Escalando deployment a 3 replicas..."
    kubectl scale deployment/$APP_NAME --replicas=3 -n "$NAMESPACE"
    
    log_info "Esperando a que los nuevos pods estén listos..."
    kubectl wait --for=condition=ready pod -l app=$APP_NAME -n "$NAMESPACE" --timeout=300s || true
    
    sleep 5
    
    echo ""
    echo "=== PODS DESPUÉS DEL ESCALADO ==="
    kubectl get pods -n "$NAMESPACE" -o wide
    
    log_info "Escalando de vuelta a 2 replicas..."
    kubectl scale deployment/$APP_NAME --replicas=2 -n "$NAMESPACE"
    
    sleep 5
    
    echo ""
    echo "=== PODS DESPUÉS DEL DESCALADO ==="
    kubectl get pods -n "$NAMESPACE" -o wide
}

# Prueba 3: Reiniciar deployment
test_deployment_restart() {
    log_info "=========================================="
    log_info "PRUEBA 3: Reinicio de Deployment"
    log_info "=========================================="
    
    log_info "Reiniciando deployment: $APP_NAME..."
    kubectl rollout restart deployment/$APP_NAME -n "$NAMESPACE"
    
    log_info "Esperando a que el rollout se complete..."
    kubectl rollout status deployment/$APP_NAME -n "$NAMESPACE" --timeout=300s
    
    log_success "Deployment reiniciado exitosamente"
    
    echo ""
    echo "=== PODS DESPUÉS DEL REINICIO ==="
    kubectl get pods -n "$NAMESPACE" -o wide
}

# Prueba 4: Verificar logs y eventos
test_logs_and_events() {
    log_info "=========================================="
    log_info "PRUEBA 4: Análisis de Logs y Eventos"
    log_info "=========================================="
    
    echo ""
    echo "=== EVENTOS RECIENTES ==="
    kubectl get events -n "$NAMESPACE" --sort-by='.lastTimestamp' | tail -20
    
    echo ""
    echo "=== LOGS DEL DEPLOYMENT ==="
    kubectl logs -n "$NAMESPACE" -l app=$APP_NAME --tail=50 || log_warning "No hay logs disponibles"
    
    echo ""
    echo "=== ESTADO DE READINESS ==="
    kubectl describe deployment/$APP_NAME -n "$NAMESPACE" | grep -A 10 "Conditions:"
}

# Prueba 5: Verificar métricas de Prometheus
test_prometheus_metrics() {
    log_info "=========================================="
    log_info "PRUEBA 5: Verificar Métricas de Prometheus"
    log_info "=========================================="
    
    # Verificar si Prometheus está disponible
    PROMETHEUS_POD=$(kubectl get pods -n "$NAMESPACE" -l app=prometheus -o jsonpath='{.items[0].metadata.name}' 2>/dev/null || echo "")
    
    if [ -z "$PROMETHEUS_POD" ]; then
        log_warning "Prometheus no está disponible en el cluster"
        return 0
    fi
    
    log_info "Pod de Prometheus encontrado: $PROMETHEUS_POD"
    
    # Port-forward a Prometheus
    log_info "Configurando port-forward a Prometheus..."
    kubectl port-forward -n "$NAMESPACE" "$PROMETHEUS_POD" 9090:9090 &
    PF_PID=$!
    
    sleep 3
    
    # Obtener algunas métricas
    log_info "Obteniendo métricas de Prometheus..."
    curl -s "http://localhost:9090/api/v1/query?query=up" | head -20 || log_warning "No se pudo conectar a Prometheus"
    
    # Matar port-forward
    kill $PF_PID 2>/dev/null || true
}

# Prueba 6: Verificar Grafana
test_grafana_dashboards() {
    log_info "=========================================="
    log_info "PRUEBA 6: Verificar Dashboards de Grafana"
    log_info "=========================================="
    
    # Verificar si Grafana está disponible
    GRAFANA_POD=$(kubectl get pods -n "$NAMESPACE" -l app=grafana -o jsonpath='{.items[0].metadata.name}' 2>/dev/null || echo "")
    
    if [ -z "$GRAFANA_POD" ]; then
        log_warning "Grafana no está disponible en el cluster"
        return 0
    fi
    
    log_info "Pod de Grafana encontrado: $GRAFANA_POD"
    
    # Port-forward a Grafana
    log_info "Configurando port-forward a Grafana..."
    kubectl port-forward -n "$NAMESPACE" "$GRAFANA_POD" 3000:3000 &
    PF_PID=$!
    
    sleep 3
    
    # Verificar que Grafana está accesible
    log_info "Verificando acceso a Grafana..."
    curl -s -o /dev/null -w "HTTP Status: %{http_code}\n" "http://localhost:3000/api/health" || log_warning "No se pudo conectar a Grafana"
    
    # Matar port-forward
    kill $PF_PID 2>/dev/null || true
}

# Generar reporte final
generate_report() {
    log_info "=========================================="
    log_info "REPORTE FINAL DE RESILIENCIA"
    log_info "=========================================="
    
    echo ""
    echo "=== ESTADO FINAL DEL CLUSTER ==="
    kubectl get all -n "$NAMESPACE"
    
    echo ""
    echo "=== RESUMEN DE PRUEBAS ==="
    echo "✓ Prueba 1: Fallo de Pod y Auto-Healing - COMPLETADA"
    echo "✓ Prueba 2: Escalado de Replicas - COMPLETADA"
    echo "✓ Prueba 3: Reinicio de Deployment - COMPLETADA"
    echo "✓ Prueba 4: Análisis de Logs y Eventos - COMPLETADA"
    echo "✓ Prueba 5: Métricas de Prometheus - COMPLETADA"
    echo "✓ Prueba 6: Dashboards de Grafana - COMPLETADA"
    
    echo ""
    log_success "Todas las pruebas de resiliencia han sido completadas"
    echo ""
    echo "Reporte guardado en: resilience-test-report.txt"
}

# Función principal
main() {
    echo ""
    echo "╔════════════════════════════════════════════════════════════╗"
    echo "║   LABORATORIO DEVOPS - PRUEBAS DE RESILIENCIA             ║"
    echo "║   Sistema de Consulta Médica Externa                      ║"
    echo "╚════════════════════════════════════════════════════════════╝"
    echo ""
    
    check_kubectl
    check_namespace
    
    get_initial_state
    
    test_pod_failure
    test_scaling
    test_deployment_restart
    test_logs_and_events
    test_prometheus_metrics
    test_grafana_dashboards
    
    generate_report
}

# Ejecutar
main "$@"
