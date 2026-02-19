# Informe de Métricas DORA (Modo Laboratorio)

**Proyecto:** consultaM_devops  
**Fecha de cálculo:** 2026-02-18  
**Contexto:** entorno de laboratorio (no producción real)

---

## 1. Alcance y limitaciones

Este informe presenta métricas DORA estimadas en modo **laboratorio**.

Limitaciones explícitas:

1. No se utiliza telemetría de producción real.
2. Las mediciones se obtienen de evidencia local (Git + Kubernetes + pruebas controladas).
3. Para `Deployment Frequency` y `Change Failure Rate`, se usan proxies operativos del entorno de ensayo.
4. Los resultados son válidos para demostrar metodología y capacidad del pipeline, no para benchmark empresarial.

---

## 2. Resultados obtenidos

| Métrica DORA | Resultado (laboratorio) | Estado |
|---|---:|---|
| Lead Time for Changes | **163 s** (2.72 min) | Medido |
| Deployment Frequency | **2 despliegues / 24 h** (ventana de ensayo) | Medido |
| Change Failure Rate | **0.00%** | Estimado con proxy |
| MTTR | **11 s** (0.18 min) | Medido |

---

## 3. Método por métrica (cómo se obtuvo en la demo)

### 3.1 Lead Time for Changes

**Definición usada en demo:** tiempo desde el último commit hasta el despliegue exitoso en laboratorio.

**Fuente de datos:**
- Timestamp del último commit (`git log -1 --format=%cI`).
- Timestamp de creación del último ReplicaSet activo de `consulta-medica` (proxy de despliegue aplicado en K8s).

**Cálculo:**
- Commit: `2026-02-18T02:05:29Z`
- Despliegue (RS): `2026-02-18T02:08:12Z`
- Diferencia: `163 s`.

---

### 3.2 Deployment Frequency

**Definición usada en demo:** número de despliegues en una ventana de prueba.

**Fuente de datos:**
- ReplicaSets de `consulta-medica` creados en las últimas 24 horas (proxy de despliegues).

**Resultado:**
- `2` despliegues en `24 h` de ensayo.

---

### 3.3 Change Failure Rate (CFR)

**Definición usada en demo:** porcentaje de despliegues fallidos o con rollback en el entorno de laboratorio.

**Fuente de datos (proxy):**
- Estado actual del deployment (`readyReplicas == replicas`).
- Evidencia de rollback/fallo en historial de despliegue de laboratorio.

**Resultado:**
- `0.00%` en la ventana evaluada.

**Nota académica:**
- En un entorno productivo, esta métrica debe calcularse desde historial completo de runs CI/CD y eventos de rollback/incidentes.

---

### 3.4 MTTR

**Definición usada en demo:** tiempo desde fallo inducido hasta restauración del servicio.

**Fuente de datos:**
- Prueba de resiliencia documentada (reinicio controlado de pod).
- Timestamps del evento de falla y primera verificación de recuperación (`HTTP 200`).

**Cálculo:**
- Inicio de falla inducida: `2026-02-18T02:25:07Z`
- Recuperación observada: `2026-02-18T02:25:18Z`
- MTTR: `11 s`.

---

## 4. Conclusión

Las métricas DORA en modo laboratorio muestran desempeño favorable para el escenario de demostración:

- Lead time corto,
- frecuencia de despliegue observable,
- ausencia de fallos en la ventana medida,
- recuperación rápida ante falla inducida.

Se recomienda, como siguiente paso, instrumentar estas métricas con extracción automática desde el pipeline y almacenamiento histórico para análisis de tendencia.
