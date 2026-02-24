# Evidencia 4.6.2.1 — Implementación de infraestructura como código (Terraform)

- **Fecha (UTC):** 2026-02-24
- **Run ID:** `20260224T002124Z`
- **Paquete de evidencia:** `artifacts/iac-evidence/20260224T002124Z/`
- **Fuente IaC:** `terraform/main.tf`

## 1) Namespace `consulta-medica`

**Evidencia IaC**
- Declaración del recurso namespace en Terraform (línea reportada en evidencia): `terraform-recursos-lineas.txt`.

**Evidencia en clúster**
- Objeto namespace existente: `k8s-namespace.yaml`.

## 2) ConfigMaps y Secrets (desacople config/código)

**Evidencia IaC**
- ConfigMaps declarados: `app-config`, `mysql-init-sql`, `prometheus-config`, `grafana-datasource`.
- Secret declarado: `db-secret`.
- Referencias con línea en `terraform-recursos-lineas.txt`.

**Evidencia en clúster**
- ConfigMaps desplegados: `k8s-configmaps.yaml`.
- Secret desplegado: `k8s-secret-db-secret.yaml`.
- Datos de secret en Base64 (muestra): `k8s-secret-base64.txt`.
- Inyección a la app desde ConfigMap + Secret: `k8s-app-env-inyeccion.txt`.

## 3) Deployments

**Evidencia IaC**
- Deployments declarados para: `consulta-medica`, `mysql`, `prometheus`, `grafana`, `jenkins`.
- Referencias con línea en `terraform-recursos-lineas.txt`.

**Evidencia en clúster**
- Deployments existentes y activos: `k8s-deployments.yaml`.
- Resumen de réplicas/estrategia/ready: `k8s-deployments-resumen.txt`.
- Resultado observado: estrategia `RollingUpdate` para los deployments listados.

## 4) Services (ClusterIP y LoadBalancer)

**Evidencia IaC**
- Services declarados para `mysql`, `consulta-medica`, `prometheus`, `grafana`, `jenkins`, `docker-registry` en `terraform-recursos-lineas.txt`.

**Evidencia en clúster**
- Servicios en ejecución con tipo y puertos: `k8s-services.yaml` y `k8s-services-wide.txt`.
- Servicios expuestos externamente como `LoadBalancer` (localhost) para app, Jenkins, Prometheus y Grafana.
- Servicio de MySQL con tipo `ClusterIP` y modo headless (`clusterIP: None`): `k8s-mysql-service-dns.txt`.
- Resolución DNS interna estable de MySQL: `k8s-dns-mysql-resolucion.txt` (ej.: `mysql.consulta-medica.svc.cluster.local`).

## 5) PersistentVolumeClaims (PVC)

**Evidencia IaC**
- PVC declarados: `mysql-pvc`, `jenkins-pvc`, `docker-registry-pvc` en `terraform-recursos-lineas.txt`.

**Evidencia en clúster**
- PVC creados y vinculados: `k8s-pvcs.yaml`.
- Uso de PVC en deployments (montajes): `k8s-deployments.yaml` y evidencia puntual de MySQL en `k8s-mysql-env-volumen.txt`.

## Conclusión técnica

La infraestructura se encuentra definida declarativamente en `terraform/main.tf` y materializada en el clúster Kubernetes con trazabilidad auditable. Se verifican de forma objetiva los cinco bloques solicitados (namespace, config/secret, deployments, services y PVC), incluyendo evidencia de inyección de configuración y resolución DNS interna para conectividad entre componentes.
