# Validación de Observabilidad y Resiliencia (sin afectar servicios críticos)

Fecha de ejecución: 2026-02-18 (UTC)
Entorno: Kubernetes namespace `consulta-medica` (Docker Desktop)
Objetivo: validar panel de métricas (Prometheus/Grafana) y ejecutar una prueba de resiliencia controlada.

## 1) Alcance y criterio de no afectación

Para no perjudicar los servicios operativos de negocio, la prueba de resiliencia se ejecutó **solo sobre el componente de observabilidad `prometheus`**.

No se reiniciaron ni escalaron estos componentes:
- `consulta-medica` (app)
- `mysql`
- `jenkins`
- `docker-registry`
- `grafana`

## 2) Línea base previa

Se validó estado de deployments/pods/services y health checks HTTP:

- Deployments en `READY`: `consulta-medica`, `docker-registry`, `grafana`, `jenkins`, `mysql`, `prometheus`.
- Endpoints HTTP antes de la prueba:
  - `app_http_status=302` (redirección esperada por login)
  - `prometheus_http_status=200`
  - `grafana_http_status=200`

## 3) Ejercicio de resiliencia ejecutado

### Acción controlada
Reinicio del pod de Prometheus por eliminación del pod activo (el Deployment lo recrea automáticamente):

- Pod previo: `prometheus-5b587fc886-jswrc`
- Inicio reinicio: `2026-02-18T02:25:07Z`
- Pod nuevo: `prometheus-5b587fc886-ttn65`
- Fin reinicio: `2026-02-18T02:25:10Z`

### Verificación inmediata post-reinicio
- `app_http_status=302`
- `prometheus_http_status=000` (ventana transitoria de arranque, esperada)
- `grafana_http_status=200`

## 4) Validación de recuperación

Se ejecutó ventana de verificación continua con sondeo HTTP.

Primer sondeo con recuperación completa:
- `2026-02-18T02:25:18Z app=302 prometheus=200 grafana=200`

Estado final del pod:
- `prometheus-5b587fc886-ttn65` en `Running` y `1/1 Ready`.

## 5) Resultado

✅ **Observabilidad operativa**: Prometheus y Grafana saludables (`200`).  
✅ **Resiliencia validada**: Prometheus se recuperó automáticamente tras reinicio controlado del pod.  
✅ **Sin impacto funcional en servicios críticos**: aplicación y Grafana permanecieron disponibles durante la prueba.

## 5.1) Dashboard operativo de monitoreo

Se dejó creado en Grafana el dashboard:

- **Título:** `Observabilidad - Prometheus y Grafana`
- **UID:** `obs-prom-grafana`
- **Ruta:** `/d/obs-prom-grafana/observabilidad-prometheus-y-grafana`

Acceso local:

- Grafana: `http://localhost:3000`
- Usuario: `admin`
- Contraseña: `admin`

Validación por API:

- Dashboard visible en búsqueda (`/api/search?query=Observabilidad`).
- Datasource Prometheus activo y por defecto.

Nota: la configuración de scrape de Prometheus se ajustó para objetivos válidos de monitoreo (`prometheus-self` y `grafana`) evitando targets con error `404`.

## 6) Comandos usados (registro)

```bash
kubectl -n consulta-medica get deploy,pods,svc -o wide
curl -s -o /dev/null -w 'app_http_status=%{http_code}\n' http://localhost/
curl -s -o /dev/null -w 'prometheus_http_status=%{http_code}\n' http://localhost:9090/-/ready
curl -s -o /dev/null -w 'grafana_http_status=%{http_code}\n' http://localhost:3000/api/health

NS=consulta-medica
OLD_POD=$(kubectl -n "$NS" get pod -l app=prometheus -o jsonpath='{.items[0].metadata.name}')
kubectl -n "$NS" delete pod "$OLD_POD" --wait=false
kubectl -n "$NS" wait --for=condition=ready pod -l app=prometheus --timeout=180s

# ventana de recuperación
for i in 1 2 3 4 5 6 7 8 9 10; do
  APP=$(curl -s -o /dev/null -w '%{http_code}' http://localhost/ || true)
  PRO=$(curl -s -o /dev/null -w '%{http_code}' http://localhost:9090/-/ready || true)
  GRA=$(curl -s -o /dev/null -w '%{http_code}' http://localhost:3000/api/health || true)
  echo "app=$APP prometheus=$PRO grafana=$GRA"
  [ "$PRO" = "200" ] && break
  sleep 3
done
```

## 7) Recomendación de siguiente prueba (opcional)

Para aumentar cobertura sin afectar operación:
1. Prueba de resiliencia de `grafana` (reinicio de 1 pod y recuperación).
2. Prueba de escalado horizontal de `consulta-medica` (1→2 réplicas) en ventana controlada.
3. Definir SLO simple de observabilidad (ej. recuperación < 30s).

## 8) Monitoreo de pods `consulta-medica` y `mysql`

Se habilitó `kube-state-metrics` para exponer métricas de estado de pods al Prometheus del namespace.

### Componentes habilitados

- Deployment/Service: `kube-state-metrics` en namespace `consulta-medica`.
- Job de Prometheus: `kube-state-metrics` (`kube-state-metrics.consulta-medica.svc.cluster.local:8080`).

### Dashboard de Grafana

- **Título:** `Pods Monitor - Consulta Medica y MySQL`
- **UID:** `pods-consulta-mysql`
- **Ruta:** `/d/pods-consulta-mysql/pods-monitor-consulta-medica-y-mysql`

Incluye paneles para:

- Pods `Ready` de `consulta-medica`
- Pods `Ready` de `mysql`
- Serie temporal de `Ready (0/1)` por pod
- Reinicios de contenedor por pod

### Validación técnica

- Targets de Prometheus en `up`: `grafana`, `kube-state-metrics`, `prometheus-self`.
- Métricas disponibles para consulta:
  - `kube_pod_status_ready{namespace="consulta-medica",pod=~"consulta-medica.*",condition="true"}`
  - `kube_pod_status_ready{namespace="consulta-medica",pod=~"mysql.*",condition="true"}`
  - `kube_pod_container_status_restarts_total{namespace="consulta-medica",pod=~"consulta-medica.*|mysql.*"}`

## 9) Alertas configuradas

Se habilitaron reglas de alerta para disponibilidad y estabilidad de pods críticos:

- `ConsultaMedicaPodNotReady`
  - Expresión: `sum(kube_pod_status_ready{namespace="consulta-medica",pod=~"consulta-medica.*",condition="true"}) < 1`
  - Ventana: `for: 2m`
  - Severidad: `warning`

- `MySQLPodNotReady`
  - Expresión: `sum(kube_pod_status_ready{namespace="consulta-medica",pod=~"mysql.*",condition="true"}) < 1`
  - Ventana: `for: 2m`
  - Severidad: `critical`

- `HighContainerRestartsConsultaMysql`
  - Expresión: `increase(kube_pod_container_status_restarts_total{namespace="consulta-medica",pod=~"consulta-medica.*|mysql.*"}[10m]) > 1`
  - Ventana: `for: 5m`
  - Severidad: `warning`

### Verificación

- Reglas cargadas en Prometheus: `3`
- Estado actual: `inactive` (sin incidentes activos)

### Dónde verlas

- Prometheus Rules: `http://localhost:9090/rules`
- Prometheus Alerts: `http://localhost:9090/alerts`

## 10) Dashboard de consumo de recursos (`consulta-medica` y `mysql`)

Se creó un dashboard dedicado para consumo/capacidad de recursos de los pods críticos:

- **Título:** `Consumo de Recursos - Consulta Medica y MySQL`
- **UID:** `consumo-recursos-consulta`
- **Ruta:** `/d/consumo-recursos-consulta/consumo-de-recursos-consulta-medica-y-mysql`

Paneles incluidos:

- CPU uso real (cores) por pod
- Memoria uso real (MiB) por pod
- CPU requests (cores) por pod
- Memoria requests (MiB) por pod
- CPU limits (cores) por pod
- Memoria limits (MiB) por pod

Validación técnica posterior:

- Job `kubernetes-cadvisor` en estado `up` en Prometheus.
- Consultas con datos de uso real por pod en namespace `consulta-medica`:
  - `sum by (pod) (rate(container_cpu_usage_seconds_total{namespace="consulta-medica",pod=~"consulta-medica.*|mysql.*"}[5m]))`
  - `sum by (pod) (container_memory_working_set_bytes{namespace="consulta-medica",pod=~"consulta-medica.*|mysql.*"})`

Resultado observado: series activas para pods `consulta-medica-*` y `mysql-*`, por lo que el dashboard muestra consumo real de CPU/memoria además de `requests/limits`.

## 11) Validación de logs entre Prometheus y Grafana

Se incorporó verificación cruzada de logs y conectividad entre ambos componentes para robustecer la observabilidad.

### 11.1 Comprobaciones realizadas

- Revisión de logs recientes de `grafana` con foco en `datasource/prometheus/error/warn`.
- Revisión de logs recientes de `prometheus` con foco en `scrape/discovery/error/warn`.
- Consulta de prueba desde Grafana hacia Prometheus mediante proxy de datasource:
  - `GET /api/datasources/proxy/uid/<PROM_UID>/api/v1/query?query=up`

### 11.2 Resultados

- **Conectividad Grafana → Prometheus: OK** (respuesta `status: success` con serie `up`).
- En logs de Prometheus no se observaron errores críticos de integración con Grafana; se registran mensajes informativos de carga de configuración y advertencias de deprecación de Endpoints.
- En logs de Grafana se observaron eventos `401 session.token.rotate` durante consultas de panel, asociados a rotación de sesión/autenticación del cliente, no a caída del datasource Prometheus.

### 11.3 Conclusión de la validación de logs

La integración Prometheus-Grafana se considera **operativa**: Grafana consulta correctamente métricas en Prometheus y no se detectan fallos de enlace entre ambos servicios.
