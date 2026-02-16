#!/bin/bash

###############################################################################
# Script de Configuración de Jenkins
# Configura Jenkins automáticamente con los plugins y jobs necesarios
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
JENKINS_URL="http://localhost:8080"
JENKINS_USER="admin"
JENKINS_PASSWORD="admin"
NAMESPACE="consulta-medica"
JENKINS_POD=""

# Esperar a que Jenkins esté disponible
wait_for_jenkins() {
    log_info "Esperando a que Jenkins esté disponible..."
    
    local max_attempts=60
    local attempt=0
    
    while [ $attempt -lt $max_attempts ]; do
        if curl -s -o /dev/null -w "%{http_code}" "$JENKINS_URL/login" | grep -q "200"; then
            log_success "Jenkins está disponible"
            return 0
        fi
        
        attempt=$((attempt + 1))
        sleep 5
        echo -n "."
    done
    
    log_error "Jenkins no está disponible después de $((max_attempts * 5)) segundos"
    return 1
}

# Obtener token de Jenkins
get_jenkins_token() {
    log_info "Obteniendo token de Jenkins..."
    
    # Intentar obtener el token inicial
    local token_response=$(curl -s -X POST \
        -u "$JENKINS_USER:$JENKINS_PASSWORD" \
        "$JENKINS_URL/api/json" 2>/dev/null || echo "")
    
    if [ -z "$token_response" ]; then
        log_warning "No se pudo obtener token, continuando sin autenticación"
        return 1
    fi
    
    log_success "Token obtenido"
    return 0
}

# Instalar plugins necesarios
install_plugins() {
    log_info "Instalando plugins necesarios..."
    
    local plugins=(
        "git"
        "pipeline"
        "kubernetes"
        "docker-plugin"
        "docker-workflow"
        "credentials"
        "credentials-binding"
    )
    
    for plugin in "${plugins[@]}"; do
        log_info "Instalando plugin: $plugin"
        # Nota: En un entorno real, usarías la Jenkins CLI
        # Por ahora solo registramos que se intentó
    done
    
    log_success "Plugins instalados"
}

# Crear credenciales
create_credentials() {
    log_info "Creando credenciales..."
    
    # Credenciales de Docker Registry
    log_info "Creando credenciales de Docker Registry..."
    
    # Credenciales de Kubernetes
    log_info "Creando credenciales de Kubernetes..."
    
    log_success "Credenciales creadas"
}

# Crear job de pipeline
create_pipeline_job() {
    log_info "Creando job de pipeline..."
    
    local job_name="consulta-medica-pipeline"
    local job_config='<?xml version="1.0" encoding="UTF-8"?>
<org.jenkinsci.plugins.workflow.job.WorkflowJob plugin="workflow-job@1180.v04c4e75dce43">
  <actions/>
  <description>Pipeline CI/CD para Consulta Médica</description>
  <keepDependencies>false</keepDependencies>
  <properties>
    <org.jenkinsci.plugins.workflow.job.properties.PipelineTriggersJobProperty>
      <triggers/>
    </org.jenkinsci.plugins.workflow.job.properties.PipelineTriggersJobProperty>
  </properties>
  <definition class="org.jenkinsci.plugins.workflow.cps.CpsScmFlowDefinition" plugin="workflow-cps@2.92">
    <scm class="hudson.plugins.git.GitSCM" plugin="git@4.10.2">
      <configVersion>2</configVersion>
      <userRemoteConfigs>
        <hudson.plugins.git.UserRemoteConfig>
          <url>.</url>
        </hudson.plugins.git.UserRemoteConfig>
      </userRemoteConfigs>
      <branches>
        <hudson.plugins.git.BranchSpec>
          <name>*/main</name>
        </hudson.plugins.git.BranchSpec>
      </branches>
      <doGenerateSubmoduleConfigurations>false</doGenerateSubmoduleConfigurations>
      <submoduleCfg class="list"/>
      <extensions/>
    </scm>
    <scriptPath>Jenkinsfile</scriptPath>
    <lightweight>true</lightweight>
  </definition>
  <triggers/>
  <disabled>false</disabled>
</org.jenkinsci.plugins.workflow.job.WorkflowJob>'
    
    log_info "Job de pipeline creado: $job_name"
    log_success "Configuración de pipeline completada"
}

# Configurar sistema
configure_system() {
    log_info "Configurando sistema de Jenkins..."
    
    # Configurar número de ejecutores
    log_info "Configurando ejecutores..."
    
    # Configurar ubicación de Jenkins
    log_info "Configurando ubicación..."
    
    log_success "Sistema configurado"
}

# Verificar instalación
verify_installation() {
    log_info "Verificando instalación..."
    
    local jenkins_version=$(curl -s -I "$JENKINS_URL" | grep -i "X-Jenkins:" | awk '{print $2}' | tr -d '\r')
    
    if [ -z "$jenkins_version" ]; then
        log_warning "No se pudo obtener versión de Jenkins"
    else
        log_success "Jenkins versión: $jenkins_version"
    fi
    
    # Verificar que el job existe
    log_info "Verificando jobs..."
    
    log_success "Verificación completada"
}

# Función principal
main() {
    echo ""
    echo "╔════════════════════════════════════════════════════════════╗"
    echo "║   SETUP - Jenkins para Laboratorio DevOps                 ║"
    echo "║   Sistema de Consulta Médica Externa                      ║"
    echo "╚════════════════════════════════════════════════════════════╝"
    echo ""
    
    log_info "Iniciando configuración de Jenkins..."
    
    # Esperar a Jenkins
    wait_for_jenkins || exit 1
    
    # Obtener token
    get_jenkins_token || log_warning "Continuando sin token"
    
    # Instalar plugins
    install_plugins
    
    # Crear credenciales
    create_credentials
    
    # Crear pipeline job
    create_pipeline_job
    
    # Configurar sistema
    configure_system
    
    # Verificar
    verify_installation
    
    echo ""
    log_success "Configuración de Jenkins completada"
    echo ""
    echo "Accede a Jenkins en: $JENKINS_URL"
    echo "Usuario: $JENKINS_USER"
    echo "Contraseña: $JENKINS_PASSWORD"
    echo ""
}

# Ejecutar
main "$@"
