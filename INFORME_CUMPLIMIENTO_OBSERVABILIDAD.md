# Informe Académico de Cumplimiento
## Criterio: Observabilidad y Validación

**Proyecto:** consultaM_devops  
**Fecha de evaluación:** 2026-02-18  
**Criterio evaluado:**

> "Observabilidad y validación: panel de métricas (Prometheus/Grafana) y un ejercicio de resiliencia (por ejemplo, reinicio de un pod/servicio y ver la recuperación). Documenta el procedimiento y resultados."

---

## 1. Introducción

Este informe presenta una evaluación técnica del proyecto respecto al criterio de observabilidad y validación. La revisión se centra en tres aspectos: (a) disponibilidad de paneles de métricas, (b) ejecución de una prueba de resiliencia y (c) documentación del procedimiento y resultados.

---

## 2. Metodología de evaluación

La evaluación se realizó mediante verificación de configuración, validación operativa de servicios y revisión documental en repositorio.

Se comprobaron los siguientes elementos:

1. Operación de Prometheus y Grafana en entorno de laboratorio.
2. Existencia de dashboards de monitoreo accesibles.
3. Monitoreo de componentes críticos (`consulta-medica` y `mysql`).
4. Ejecución de una prueba de resiliencia controlada.
5. Registro formal del procedimiento y de los resultados obtenidos.

---

## 3. Resultados

### 3.1 Observabilidad (Prometheus/Grafana)

Se verificó que la plataforma de observabilidad se encuentra operativa:

- Prometheus disponible para consulta de reglas y alertas:
  - `http://localhost:9090/rules`
  - `http://localhost:9090/alerts`
- Grafana disponible en `http://localhost:3000`.
- Dashboards funcionales:
  - General: `/d/obs-prom-grafana/observabilidad-prometheus-y-grafana`
  - Pods críticos: `/d/pods-consulta-mysql/pods-monitor-consulta-medica-y-mysql`

Además, el monitoreo de pods críticos fue implementado con métricas de disponibilidad y estabilidad (estado `ready` y reinicios de contenedores), utilizando `kube-state-metrics` como fuente de estado Kubernetes.

### 3.2 Validación de resiliencia

Se ejecutó una prueba de resiliencia controlada mediante reinicio de un pod de observabilidad (Prometheus), verificando:

- recreación automática por parte del Deployment,
- recuperación del servicio en ventana corta,
- continuidad de los servicios de negocio sin impacto operativo relevante.

### 3.3 Alertas de validación operativa

Se incorporaron reglas de alerta para condiciones críticas de operación:

- indisponibilidad de pods de `consulta-medica`,
- indisponibilidad de pods de `mysql`,
- aumento anómalo de reinicios de contenedores.

Estas reglas se cargan en Prometheus y quedan disponibles para seguimiento en tiempo real.

---

## 4. Evidencia documental

La evidencia técnica del proceso se encuentra consolidada en:

- `observability/VALIDACION_OBSERVABILIDAD_RESILIENCIA.md`

Dicho documento contiene línea base, pasos ejecutados, resultados posteriores a la prueba, validación de dashboards y validación de alertas.

---

## 5. Discusión

Desde una perspectiva académica, el criterio no exige únicamente la instalación de herramientas, sino la demostración de su uso efectivo. En este proyecto se observa una cadena completa de validación: instrumentación, visualización, prueba de falla controlada y registro reproducible de resultados.

---

## 6. Conclusión

Con base en la revisión técnica y documental, **el proyecto CUMPLE** el criterio de **Observabilidad y Validación**.

Se confirma la existencia de paneles de métricas en Prometheus/Grafana, la ejecución de un ejercicio de resiliencia con recuperación satisfactoria y la documentación del procedimiento y de los resultados en el repositorio.
