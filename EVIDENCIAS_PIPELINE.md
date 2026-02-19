# Evidencias de Pipeline CI/CD e Infraestructura

Fecha de elaboración: ____
Responsable: ____
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
| Repositorio con IaC y CI/CD | ☐ Cumple / ☐ Parcial / ☐ No cumple | terraform/main.tf, ansible/playbook.yml, Jenkinsfile, .github/workflows/cicd.yml |
| Pipeline completo en laboratorio | ☐ Cumple / ☐ Parcial / ☐ No cumple | Logs de ejecución + artefactos generados |
| Observabilidad y resiliencia documentadas | ☐ Cumple / ☐ Parcial / ☐ No cumple | observability/VALIDACION_OBSERVABILIDAD_RESILIENCIA.md |

Conclusión final: ____

## 3. Evidencia de repositorio y trazabilidad

### 3.1 Infraestructura como código (IaC)

- Terraform: terraform/main.tf
- Ansible: ansible/playbook.yml

Checks sugeridos:

- [ ] Archivo Terraform presente y versionado
- [ ] Archivo Ansible presente y versionado
- [ ] Recursos de Kubernetes descritos por IaC

### 3.2 Pipeline CI/CD

- Jenkins Pipeline: Jenkinsfile
- GitHub Actions: .github/workflows/cicd.yml

Checks sugeridos:

- [ ] Definición de etapas build/test/deploy
- [ ] Trazabilidad por commit SHA/branch
- [ ] Registro de resultados de ejecución

## 4. Evidencia de ejecución del pipeline (flujo completo)

## 4.1 Datos del run

- Plataforma usada: ☐ Jenkins / ☐ GitHub Actions
- ID de ejecución / Build number: ____
- Commit SHA: ____
- Fecha/Hora inicio: ____
- Fecha/Hora fin: ____
- Estado final: ☐ Success / ☐ Failed

## 4.2 Evidencia por etapa

### Build

- Estado: ☐ OK / ☐ Error
- Evidencia (URL/log): ____
- Captura: ____

### Test

- Estado: ☐ OK / ☐ Error
- Evidencia (URL/log): ____
- Captura: ____

### Package

- Imagen/tag generado: ____
- Registro destino: ____
- Evidencia (URL/log): ____

### Deploy

- Namespace: consulta-medica
- Deployment actualizado: consulta-medica
- Evidencia rollout (log/comando): ____

## 4.3 Artefactos generados

| Artefacto | Ubicación | Validación |
|---|---|---|
| dora-metrics.txt (si aplica por Actions) | ____ | ☐ Presente |
| Log de pipeline exportado | ____ | ☐ Presente |
| Capturas de etapas clave | ____ | ☐ Presente |

## 5. Observabilidad y resiliencia

Referencia documental:

- observability/VALIDACION_OBSERVABILIDAD_RESILIENCIA.md

### 5.1 Dashboard de métricas

- Grafana URL: http://localhost:3000
- Dashboard pods app/mysql: http://localhost:3000/d/pods-consulta-mysql/pods-monitor-consulta-medica-y-mysql
- Evidencia de acceso (captura): ____

### 5.2 Validación Prometheus

- Prometheus URL: http://localhost:9090
- Rules: http://localhost:9090/rules
- Alerts: http://localhost:9090/alerts
- Evidencia de reglas cargadas (captura/log): ____

### 5.3 Ejercicio de resiliencia

- Tipo de prueba: reinicio controlado de pod
- Componente probado: ____
- Resultado de recuperación: ____
- Impacto en servicios críticos: ____
- Evidencia (captura/log): ____

## 6. Checklist de aceptación final

- [ ] IaC versionada y trazable en repositorio
- [ ] Pipeline CI/CD definido y versionado
- [ ] Al menos un run completo ejecutado (build/test/package/deploy)
- [ ] Logs y capturas del run disponibles
- [ ] Artefactos del run disponibles
- [ ] Dashboard y métricas de observabilidad operativos
- [ ] Prueba de resiliencia realizada y documentada

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

- Observación 1: ____
- Observación 2: ____
- Acción recomendada: ____
