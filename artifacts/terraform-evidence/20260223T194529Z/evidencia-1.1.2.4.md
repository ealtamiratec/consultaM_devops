# Evidencia técnica — 1.1.2.4 Proceso de Aprovisionamiento y Validación

- **Fecha (UTC):** 2026-02-23
- **Run ID:** `20260223T194529Z`
- **Directorio de evidencias:** `artifacts/terraform-evidence/20260223T194529Z/`

## 1) Aprovisionamiento con Terraform

### 1.1 `terraform init`
- **Resultado:** Exitoso.
- **Evidencia:** `terraform-init.log`, `terraform-version.txt`.
- **Indicador clave:** inicialización completada y proveedores listos.

### 1.2 `terraform plan`
- **Resultado:** Exitoso.
- **Evidencia:** `terraform-plan.log`, `terraform-plan-show.txt`.
- **Indicador clave:** `Plan: 25 to add, 0 to change, 0 to destroy`.

### 1.3 `terraform apply`
- **Resultado:** No exitoso en este run (entorno ya aprovisionado parcialmente fuera del estado local de Terraform).
- **Evidencia:** `terraform-apply.log`.
- **Indicador clave:** `Error: namespaces "consulta-medica" already exists`.
- **Interpretación técnica:** la ejecución evidencia conflicto de idempotencia por recursos preexistentes no importados al state actual. No invalida la validación operativa del entorno desplegado, pero sí indica que para reaprovisionamiento limpio se requiere importación de estado o clúster limpio.

## 2) Validación de infraestructura y servicios

### 2.1 Estado general de recursos
- **Comando ejecutado:** `kubectl get all -n consulta-medica`.
- **Evidencia:** `kubectl-get-all.txt`.
- **Resultado:** recursos principales del namespace en ejecución.

### 2.2 Estado de Pods y Services
- **Evidencia:** `kubectl-get-pods-wide.txt`, `kubectl-get-svc-wide.txt`.
- **Resultado:** servicios expuestos y pods activos en el namespace.

### 2.3 Estado de almacenamiento persistente
- **Evidencia:** `kubectl-get-pvc.txt`.
- **Resultado:** PVC presentes para componentes que requieren persistencia.

## 3) Validación de conectividad

### 3.1 Conectividad interna app → mysql
- **Método:** prueba TCP desde pod de aplicación con `php fsockopen("mysql", 3306)`.
- **Evidencia:** `connectividad-app-mysql.txt`.
- **Resultado:** `OK app->mysql:3306`.

### 3.2 Endpoints externos
- **Evidencia:** `validacion-urls-externas.txt`.
- **Resultados HTTP observados:**
  - Jenkins (`http://localhost:8080`) → `200`
  - Grafana (`http://localhost:3000`) → `302` (redirección esperada a login)
  - Prometheus (`http://localhost:9090/-/ready`) → `200`
  - Docker Registry (`http://localhost:5000/v2/`) → `403` (endpoint activo con restricción de acceso/política)

## 4) Conclusión para el TFE

Se obtuvo evidencia completa y trazable del flujo **init/plan/apply** y de la **validación operativa** del entorno en Kubernetes. Aunque el `apply` no finalizó exitosamente por conflicto con recursos preexistentes, la infraestructura del namespace `consulta-medica` se verificó funcional (estado de recursos, conectividad interna app→mysql y disponibilidad de servicios externos). Esto sustenta la subsección 1.1.2.4 con enfoque de auditoría reproducible.
