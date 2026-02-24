# Evidencias de Pipeline CI/CD e Infraestructura

Fecha de elaboración: 2026-02-22
Responsable: Equipo DevOps (validación asistida)
Repositorio: consultaM_devops
Rama evaluada: main

## 1. Objetivo

Este documento consolida evidencia verificable de:

1) Repositorio y trazabilidad (IaC + CI/CD)
2) Ejecución de pipeline completo (build/test/package/deploy)
3) Observabilidad y validación (Prometheus/Grafana + resiliencia)

## 2. Resumen ejecutivo de cumplimiento

| Criterio | Estado | Evidencia principal |
|---|---|---|
| Repositorio con IaC y CI/CD | ✅ Cumple | terraform/main.tf, ansible/playbook.yml, Jenkinsfile, .github/workflows/cicd.yml |
| Pipeline completo en laboratorio | ✅ Cumple | artifacts/pipeline-evidence/20260222T225745Z/pipeline-run.log + artefactos del run |
| Observabilidad y resiliencia documentadas | ✅ Cumple | observability/VALIDACION_OBSERVABILIDAD_RESILIENCIA.md |

Conclusión final: Cumple el criterio solicitado al contar con una ejecución completa build/test/package/deploy en entorno de laboratorio, con logs, captura textual y artefactos verificables.

## 3. Evidencia de repositorio y trazabilidad

### 3.1 Infraestructura como código (IaC)

- Terraform: terraform/main.tf
- Ansible: ansible/playbook.yml

Checks sugeridos:

- [x] Archivo Terraform presente y versionado
- [x] Archivo Ansible presente y versionado
- [x] Recursos de Kubernetes descritos por IaC

### 3.2 Pipeline CI/CD

- Jenkins Pipeline: Jenkinsfile
- GitHub Actions: .github/workflows/cicd.yml

Checks sugeridos:

- [x] Definición de etapas build/test/deploy
- [x] Trazabilidad por commit SHA/branch
- [x] Registro de resultados de ejecución

## 4. Evidencia de ejecución del pipeline (flujo completo)

## 4.1 Datos del run

- Plataforma usada: ✅ Ejecución local equivalente a pipeline CI/CD (entorno laboratorio con contenedores)
- ID de ejecución / Build number: 20260222T225745Z
- Commit SHA: d089eb1449de (branch: main)
- Fecha/Hora inicio: 2026-02-22T22:57:45Z
- Fecha/Hora fin: 2026-02-22T22:58:46Z
- Estado final: ✅ Success

## 4.2 Evidencia por etapa

### Build

- Estado: ✅ OK
- Evidencia (URL/log): artifacts/pipeline-evidence/20260222T225745Z/pipeline-run.log (sección "=== STAGE: BUILD ===")
- Captura: artifacts/pipeline-evidence/20260222T225745Z/captura-consola.md

### Test

- Estado: ✅ OK
- Evidencia (URL/log): artifacts/pipeline-evidence/20260222T225745Z/pipeline-run.log (PHP lint sin errores + smoke_http_status=302)
- Captura: artifacts/pipeline-evidence/20260222T225745Z/captura-consola.md

### Package

- Imagen/tag generado: consulta-medica:evid-20260222T225745Z
- Registro destino: docker local (docker.io/library local en Docker Desktop)
- Evidencia (URL/log): artifacts/pipeline-evidence/20260222T225745Z/pipeline-run.log + artifacts/pipeline-evidence/20260222T225745Z/image.txt

### Deploy

- Namespace: consulta-medica
- Deployment actualizado: consulta-medica
- Evidencia rollout (log/comando): artifacts/pipeline-evidence/20260222T225745Z/deploy-rollout.txt + artifacts/pipeline-evidence/20260222T225745Z/deploy-wide.txt + artifacts/pipeline-evidence/20260222T225745Z/pods-wide.txt

## 4.3 Artefactos generados

| Artefacto | Ubicación | Validación |
|---|---|---|
| dora-metrics.txt (si aplica por Actions) | No aplica en este run local (sí definido en .github/workflows/cicd.yml) | N/A |
| Log de pipeline exportado | artifacts/pipeline-evidence/20260222T225745Z/pipeline-run.log | ✅ Presente |
| Capturas de etapas clave | artifacts/pipeline-evidence/20260222T225745Z/captura-consola.md | ✅ Presente |
| Metadatos de run | artifacts/pipeline-evidence/20260222T225745Z/run-id.txt | ✅ Presente |
| Imagen desplegada (tag) | artifacts/pipeline-evidence/20260222T225745Z/image.txt | ✅ Presente |
| Evidencia de deploy final | artifacts/pipeline-evidence/20260222T225745Z/deploy-rollout.txt | ✅ Presente |

## 5. Observabilidad y resiliencia

Referencia documental:

- observability/VALIDACION_OBSERVABILIDAD_RESILIENCIA.md

### 5.1 Dashboard de métricas

- Grafana URL: http://localhost:3000
- Dashboard pods app/mysql: http://localhost:3000/d/pods-consulta-mysql/pods-monitor-consulta-medica-y-mysql
- Evidencia de acceso (captura): artifacts/pipeline-evidence/20260222T225745Z/observabilidad-validacion.txt (bloque grafana_search)

### 5.2 Validación Prometheus

- Prometheus URL: http://localhost:9090
- Rules: http://localhost:9090/rules
- Alerts: http://localhost:9090/alerts
- Evidencia de reglas cargadas (captura/log): artifacts/pipeline-evidence/20260222T225745Z/observabilidad-validacion.txt (prom_targets_count + prom_alerts_sample)

### 5.3 Ejercicio de resiliencia

- Tipo de prueba: reinicio controlado de pod
- Componente probado: Prometheus
- Resultado de recuperación: recuperación confirmada (recovered=true, recovery_seconds=2)
- Impacto en servicios críticos: sin caída de la aplicación principal ni Grafana durante la ventana medida
- Evidencia (captura/log): artifacts/pipeline-evidence/20260222T225745Z/resilience_strict_out.txt

## 6. Checklist de aceptación final

- [x] IaC versionada y trazable en repositorio
- [x] Pipeline CI/CD definido y versionado
- [x] Al menos un run completo ejecutado (build/test/package/deploy)
- [x] Logs y capturas del run disponibles
- [x] Artefactos del run disponibles
- [x] Dashboard y métricas de observabilidad operativos
- [x] Prueba de resiliencia realizada y documentada

## 7. Comandos de apoyo para recopilación de evidencia

### Estado de workloads

- kubectl -n consulta-medica get deploy,pods,svc -o wide

### Rollout de app

- kubectl -n consulta-medica rollout status deployment/consulta-medica --timeout=5m

### Logs de componentes

- kubectl logs -n consulta-medica -l app=consulta-medica --tail=100
- kubectl logs -n consulta-medica -l app=mysql --tail=100
- kubectl logs -n consulta-medica -l app=prometheus --tail=100
- kubectl logs -n consulta-medica -l app=grafana --tail=100

### Verificación dashboard por API

- curl -u admin:admin "http://localhost:3000/api/search?query=Pods%20Monitor"

## 8. Observaciones y acciones pendientes

- Observación 1: El requisito de evidencia de pipeline queda cubierto por el run local 20260222T225745Z y artefactos asociados.
- Observación 2: Existe pipeline formal adicional en Jenkins y GitHub Actions, pero esta evidencia corresponde a ejecución local controlada de laboratorio.
- Acción recomendada: Adjuntar capturas PNG de Jenkins/GitHub Actions cuando se ejecute un run remoto para trazabilidad externa complementaria.
