#!/bin/bash

set -euo pipefail

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

log_info() { echo -e "${BLUE}[INFO]${NC} $1"; }
log_success() { echo -e "${GREEN}[SUCCESS]${NC} $1"; }
log_warning() { echo -e "${YELLOW}[WARNING]${NC} $1"; }
log_error() { echo -e "${RED}[ERROR]${NC} $1"; }

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"

JENKINS_URL="${JENKINS_URL:-http://localhost:8080}"
JENKINS_PUBLIC_URL="${JENKINS_PUBLIC_URL:-$JENKINS_URL}"
JENKINS_USER="${JENKINS_USER:-admin}"
JENKINS_PASSWORD="${JENKINS_PASSWORD:-admin}"
JOB_NAME="${JOB_NAME:-consulta-medica-pipeline}"
BRANCH_NAME="${BRANCH_NAME:-main}"
SCM_POLL_SCHEDULE="${SCM_POLL_SCHEDULE:-H/2 * * * *}"
GIT_REPO_URL="${GIT_REPO_URL:-}"

CRUMB_HEADER=""
COOKIE_JAR="/tmp/jenkins_cookie_$$.txt"
EFFECTIVE_JOB_NAME="$JOB_NAME"

wait_for_jenkins() {
    log_info "Esperando Jenkins en ${JENKINS_URL}..."
    for _ in {1..90}; do
        code="$(curl -s -o /dev/null -w "%{http_code}" "${JENKINS_URL}/login" || true)"
        if [[ "$code" == "200" ]]; then
            log_success "Jenkins disponible"
            return 0
        fi
        sleep 2
    done
    log_error "Jenkins no respondió a tiempo"
    return 1
}

detect_origin_url() {
    if [[ -n "$GIT_REPO_URL" ]]; then
        log_info "Usando GIT_REPO_URL provista por entorno"
        return
    fi

    if git -C "$PROJECT_ROOT" remote get-url origin >/dev/null 2>&1; then
        GIT_REPO_URL="$(git -C "$PROJECT_ROOT" remote get-url origin)"
        log_info "Origin detectado: ${GIT_REPO_URL}"
    else
        log_error "No existe remoto 'origin' en este repositorio."
        echo ""
        echo "Configura primero el remoto de GitHub y vuelve a correr este script:"
        echo "  git remote add origin https://github.com/USUARIO/REPO.git"
        echo ""
        echo "Alternativa: exporta GIT_REPO_URL y vuelve a ejecutar."
        return 1
    fi
}

normalize_repo_url() {
    if [[ "$GIT_REPO_URL" =~ ^git@github.com:(.*)$ ]]; then
        GIT_REPO_URL="https://github.com/${BASH_REMATCH[1]}"
    fi
    if [[ "$GIT_REPO_URL" =~ ^ssh://git@github.com/(.*)$ ]]; then
        GIT_REPO_URL="https://github.com/${BASH_REMATCH[1]}"
    fi
}

get_crumb() {
    local crumb_line
    crumb_line="$(curl -s -c "$COOKIE_JAR" -b "$COOKIE_JAR" -u "${JENKINS_USER}:${JENKINS_PASSWORD}" "${JENKINS_URL}/crumbIssuer/api/xml?xpath=concat(//crumbRequestField,\":\",//crumb)" || true)"
    if [[ -n "$crumb_line" ]] && [[ "$crumb_line" == *:* ]]; then
        CRUMB_HEADER="$crumb_line"
        log_success "Crumb obtenido"
    else
        log_warning "Crumb no disponible, se intentará sin crumb"
        CRUMB_HEADER=""
    fi
}

jenkins_api_post() {
    local url="$1"
    local data_file="${2:-}"
    local content_type="${3:-application/xml}"
    local http_code

    if [[ -n "$data_file" ]]; then
        if [[ -n "$CRUMB_HEADER" ]]; then
            http_code="$(curl -sS -o /tmp/jenkins_api_post.out -w "%{http_code}" -c "$COOKIE_JAR" -b "$COOKIE_JAR" -u "${JENKINS_USER}:${JENKINS_PASSWORD}" -H "$CRUMB_HEADER" -H "Content-Type: ${content_type}" --data-binary "@$data_file" "$url")"
        else
            http_code="$(curl -sS -o /tmp/jenkins_api_post.out -w "%{http_code}" -c "$COOKIE_JAR" -b "$COOKIE_JAR" -u "${JENKINS_USER}:${JENKINS_PASSWORD}" -H "Content-Type: ${content_type}" --data-binary "@$data_file" "$url")"
        fi
    else
        if [[ -n "$CRUMB_HEADER" ]]; then
            http_code="$(curl -sS -o /tmp/jenkins_api_post.out -w "%{http_code}" -c "$COOKIE_JAR" -b "$COOKIE_JAR" -u "${JENKINS_USER}:${JENKINS_PASSWORD}" -H "$CRUMB_HEADER" -X POST "$url")"
        else
            http_code="$(curl -sS -o /tmp/jenkins_api_post.out -w "%{http_code}" -c "$COOKIE_JAR" -b "$COOKIE_JAR" -u "${JENKINS_USER}:${JENKINS_PASSWORD}" -X POST "$url")"
        fi
    fi

    if [[ "$http_code" == "403" ]]; then
        get_crumb
        if [[ -n "$data_file" ]]; then
            http_code="$(curl -sS -o /tmp/jenkins_api_post.out -w "%{http_code}" -c "$COOKIE_JAR" -b "$COOKIE_JAR" -u "${JENKINS_USER}:${JENKINS_PASSWORD}" -H "$CRUMB_HEADER" -H "Content-Type: ${content_type}" --data-binary "@$data_file" "$url")"
        else
            http_code="$(curl -sS -o /tmp/jenkins_api_post.out -w "%{http_code}" -c "$COOKIE_JAR" -b "$COOKIE_JAR" -u "${JENKINS_USER}:${JENKINS_PASSWORD}" -H "$CRUMB_HEADER" -X POST "$url")"
        fi
    fi

    if [[ "$http_code" != 2* && "$http_code" != "302" && "$http_code" != "201" ]]; then
        log_error "POST Jenkins falló (${http_code}) en ${url}"
        head -c 500 /tmp/jenkins_api_post.out || true
        echo ""
        return 1
    fi

    return 0
}

create_or_update_pipeline_job() {
    log_info "Creando/actualizando job CI/CD ${JOB_NAME}..."

    local tmp_xml
    tmp_xml="$(mktemp)"

        cat > "$tmp_xml" <<EOF
<?xml version='1.1' encoding='UTF-8'?>
<flow-definition plugin="workflow-job">
    <actions/>
    <description>CI/CD automático para ${BRANCH_NAME} (GitHub - Jenkins - Docker - Kubernetes)</description>
    <keepDependencies>false</keepDependencies>
    <properties>
        <org.jenkinsci.plugins.workflow.job.properties.PipelineTriggersJobProperty>
            <triggers>
                <com.cloudbees.jenkins.GitHubPushTrigger plugin="github">
                    <spec></spec>
                </com.cloudbees.jenkins.GitHubPushTrigger>
                <hudson.triggers.SCMTrigger>
                    <spec>${SCM_POLL_SCHEDULE}</spec>
                    <ignorePostCommitHooks>false</ignorePostCommitHooks>
                </hudson.triggers.SCMTrigger>
            </triggers>
        </org.jenkinsci.plugins.workflow.job.properties.PipelineTriggersJobProperty>
    </properties>
    <definition class="org.jenkinsci.plugins.workflow.cps.CpsScmFlowDefinition" plugin="workflow-cps">
        <scm class="hudson.plugins.git.GitSCM" plugin="git">
            <configVersion>2</configVersion>
            <userRemoteConfigs>
                <hudson.plugins.git.UserRemoteConfig>
                    <url>${GIT_REPO_URL}</url>
                </hudson.plugins.git.UserRemoteConfig>
            </userRemoteConfigs>
            <branches>
                <hudson.plugins.git.BranchSpec>
                    <name>*/${BRANCH_NAME}</name>
                </hudson.plugins.git.BranchSpec>
            </branches>
            <doGenerateSubmoduleConfigurations>false</doGenerateSubmoduleConfigurations>
            <submoduleCfg class="empty-list"/>
            <extensions>
                <hudson.plugins.git.extensions.impl.WipeWorkspace/>
            </extensions>
        </scm>
        <scriptPath>Jenkinsfile</scriptPath>
        <lightweight>true</lightweight>
    </definition>
    <triggers/>
    <disabled>false</disabled>
</flow-definition>
EOF

    local code
    code="$(curl -s -c "$COOKIE_JAR" -b "$COOKIE_JAR" -o /dev/null -w "%{http_code}" -u "${JENKINS_USER}:${JENKINS_PASSWORD}" "${JENKINS_URL}/job/${JOB_NAME}/api/json" || true)"

    EFFECTIVE_JOB_NAME="$JOB_NAME"
    if [[ "$code" == "200" ]]; then
        if jenkins_api_post "${JENKINS_URL}/job/${JOB_NAME}/config.xml" "$tmp_xml"; then
            log_success "Job actualizado"
        else
            log_warning "No se pudo actualizar el job existente (posible Freestyle). Se recreará como Pipeline."
            jenkins_api_post "${JENKINS_URL}/job/${JOB_NAME}/doDelete" >/dev/null
            sleep 2
            jenkins_api_post "${JENKINS_URL}/createItem?name=${JOB_NAME}" "$tmp_xml"
            log_success "Job recreado como Pipeline"
        fi
    else
        jenkins_api_post "${JENKINS_URL}/createItem?name=${JOB_NAME}" "$tmp_xml"
        log_success "Job creado"
    fi

    local verify_code
    verify_code="$(curl -s -c "$COOKIE_JAR" -b "$COOKIE_JAR" -o /tmp/jenkins_job_verify.out -w "%{http_code}" -u "${JENKINS_USER}:${JENKINS_PASSWORD}" "${JENKINS_URL}/job/${EFFECTIVE_JOB_NAME}/api/json" || true)"
    if [[ "$verify_code" != "200" ]]; then
        log_error "El job ${EFFECTIVE_JOB_NAME} no quedó disponible (HTTP ${verify_code})"
        head -c 500 /tmp/jenkins_job_verify.out || true
        echo ""
        rm -f "$tmp_xml"
        return 1
    fi

    rm -f "$tmp_xml"
}

trigger_first_build() {
    log_info "Disparando build inicial para validar pipeline..."
    jenkins_api_post "${JENKINS_URL}/job/${EFFECTIVE_JOB_NAME}/build" >/dev/null
    log_success "Build disparado"
}

print_webhook_instructions() {
    local webhook_url
    webhook_url="${JENKINS_PUBLIC_URL%/}/github-webhook/"

    echo ""
    log_info "Configura este Webhook en GitHub para disparo inmediato por push:"
    echo "  URL: ${webhook_url}"
    echo "  Content type: application/json"
    echo "  Events: Just the push event"
    echo ""
}

main() {
    echo ""
    echo "╔════════════════════════════════════════════════════════════╗"
    echo "║   SETUP Jenkins CI/CD automático (main)                   ║"
    echo "╚════════════════════════════════════════════════════════════╝"
    echo ""

    wait_for_jenkins
    detect_origin_url
    normalize_repo_url
    get_crumb
    create_or_update_pipeline_job
    trigger_first_build
    print_webhook_instructions

    echo ""
    log_success "Jenkins listo para CI/CD automático"
    echo "Repo: ${GIT_REPO_URL}"
    echo "Rama: ${BRANCH_NAME}"
    echo "Polling SCM: ${SCM_POLL_SCHEDULE}"
    echo "Job: ${JENKINS_URL}/job/${EFFECTIVE_JOB_NAME}/"
    echo ""
}

trap 'rm -f "$COOKIE_JAR" /tmp/jenkins_api_post.out /tmp/jenkins_job_verify.out 2>/dev/null || true' EXIT

main "$@"
