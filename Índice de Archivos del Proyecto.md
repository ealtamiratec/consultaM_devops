# Índice de Archivos del Proyecto

## 📁 Estructura Completa del Proyecto

```
consulta_medica-devops/
├── 📄 Documentación Principal
│   ├── README.md                    # Guía principal del proyecto
│   ├── Guía de Inicio Rápido - 30 Minutos.md
│   ├── Guía de Inicio Rápido - Jenkins Local.md
│   ├── Guía de Inicio Rápido - Laboratorio DevOps.md
│   ├── Arquitectura del Laboratorio DevOps.md
│   ├── TROUBLESHOOTING.md           # Solución de problemas
│   ├── MAINTENANCE.md               # Operación y mantenimiento
│   ├── Cambios en Versión 2.0 - Migración a Jenkins Local.md
│   ├── Laboratorio DevOps End-to-End Local con Jenkins.md
│   └── Índice de Archivos del Proyecto.md  # Este archivo
│
├── 🚀 Scripts de Despliegue
│   ├── deploy-all.sh                # Despliegue automatizado completo
│   ├── cleanup.sh                   # Limpieza de recursos
│   └── verify-deployment.sh         # Verificación del despliegue
│
├── 🐳 Docker
│   └── Dockerfile                   # Imagen de la aplicación
│
├── ⚙️ Kubernetes (k8s/)
│   ├── jenkins-deployment.yaml      # Deployment de Jenkins
│   ├── docker-registry-deployment.yaml # Deployment de Docker Registry
│   ├── app-deployment.yaml          # Deployment de la aplicación
│   ├── app-service.yaml             # Service de la aplicación
│   ├── mysql-deployment.yaml        # Deployment de MySQL
│   ├── mysql-service.yaml           # Service de MySQL
│   ├── prometheus-deployment.yaml   # Deployment de Prometheus
│   └── grafana-deployment.yaml      # Deployment de Grafana
│
├── 🔧 Terraform (terraform/)
│   └── main.tf                      # Configuración principal de Terraform
│
├── 📋 Ansible (ansible/)
│   └── playbook.yml                 # Playbook de Ansible
│
├── 🔍 Jenkins (jenkins/)
│   ├── deploy-jenkins.sh            # Script de despliegue de Jenkins
│   └── setup-jenkins.sh             # Script de configuración de Jenkins
│
├── 📊 Observabilidad (observability/)
│   ├── prometheus/
│   │   └── prometheus.yaml          # Configuración de Prometheus
│   ├── grafana/
│   │   └── grafana-datasource.yaml  # Configuración de Grafana
│   └── resilience-test.sh           # Tests de resiliencia
│
├── 💾 Aplicación (app/)
│   └── consulta_medica/             # Código fuente de la aplicación
│       ├── public/
│       ├── app/
│       ├── config/
│       └── ...
│
├── ⚙️ Configuración
│   └── RESUMEN_PROYECTO.txt         # Resumen del proyecto
│
└── 📄 Otros
   └── Jenkinsfile                  # Pipeline de Jenkins (único, en raíz)
```

---

## 📄 Descripción de Archivos

### Documentación

| Archivo | Propósito | Cuándo Leer |
| :--- | :--- | :--- |
| **README.md** | Guía principal del proyecto | Primero |
| **Guía de Inicio Rápido - 30 Minutos.md** | Inicio rápido en 30 minutos | Antes de desplegar |
| **Arquitectura del Laboratorio DevOps.md** | Documentación técnica detallada | Para entender la arquitectura |
| **Guía de Inicio Rápido - Jenkins Local.md** | Guía específica de Jenkins | Cuando uses Jenkins |
| **TROUBLESHOOTING.md** | Solución de problemas | Cuando algo no funcione |
| **MAINTENANCE.md** | Operación y mantenimiento | Para operaciones diarias |
| **Cambios en Versión 2.0 - Migración a Jenkins Local.md** | Cambios en versión 2.0 | Para entender la migración |

### Scripts de Despliegue

| Script | Propósito | Cuándo Usar |
| :--- | :--- | :--- |
| **deploy-all.sh** | Despliegue automatizado completo | Despliegue inicial |
| **cleanup.sh** | Eliminar todos los recursos | Cuando quieras resetear |
| **verify-deployment.sh** | Verificar que todo funciona | Después de desplegar |
| **setup.sh** | Setup inicial | Antes del despliegue |

### Kubernetes Manifests

| Archivo | Componente | Propósito |
| :--- | :--- | :--- |
| **jenkins-deployment.yaml** | Jenkins | CI/CD |
| **docker-registry-deployment.yaml** | Docker Registry | Registro local |
| **app-deployment.yaml** | Aplicación | Consulta Médica |
| **mysql-deployment.yaml** | MySQL | Base de datos |
| **prometheus-deployment.yaml** | Prometheus | Monitoreo |
| **grafana-deployment.yaml** | Grafana | Visualización |

### Terraform

| Archivo | Propósito |
| :--- | :--- |
| **main.tf** | Configuración principal de IaC |

### Jenkins

| Archivo | Propósito |
| :--- | :--- |
| **Jenkinsfile** | Pipeline declarativo de CI/CD |
| **jenkins/deploy-jenkins.sh** | Despliegue de Jenkins |
| **jenkins/setup-jenkins.sh** | Configuración de Jenkins |

---

## 🚀 Flujo de Uso Recomendado

### 1. Primero (Lectura)
```
README.md → Guía de Inicio Rápido - 30 Minutos.md → Arquitectura del Laboratorio DevOps.md
```

### 2. Despliegue
```
./deploy-all.sh
./verify-deployment.sh
```

### 3. Configuración de Jenkins
```
Guía de Inicio Rápido - Jenkins Local.md → Crear job en Jenkins → Ejecutar pipeline
```

### 4. Operación Diaria
```
verify-deployment.sh → Revisar logs → Monitorear en Grafana
```

### 5. Solución de Problemas
```
TROUBLESHOOTING.md → Ejecutar diagnósticos → Revisar logs
```

### 6. Mantenimiento
```
MAINTENANCE.md → Backups → Actualizaciones → Escalado
```

---

## 📊 Resumen de Componentes

| Componente | Puerto | URL | Usuario | Contraseña |
| :--- | :--- | :--- | :--- | :--- |
| **Jenkins** | 8080 | http://localhost:8080 | admin | admin |
| **Docker Registry** | 5000 | http://localhost:5000 | - | - |
| **Aplicación** | 80 | http://localhost | - | - |
| **MySQL** | 3306 | localhost:3306 | consulta_user | consulta_pass |
| **Prometheus** | 9090 | http://localhost:9090 | - | - |
| **Grafana** | 3000 | http://localhost:3000 | admin | admin |

---

## 🔄 Ciclo de Vida del Proyecto

```
1. LECTURA
   ↓
2. DESPLIEGUE (deploy-all.sh)
   ↓
3. VERIFICACIÓN (verify-deployment.sh)
   ↓
4. CONFIGURACIÓN (Jenkins)
   ↓
5. DESARROLLO (Modificar código)
   ↓
6. CI/CD (Pipeline de Jenkins)
   ↓
7. MONITOREO (Prometheus + Grafana)
   ↓
8. MANTENIMIENTO (Backups, actualizaciones)
   ↓
9. TROUBLESHOOTING (Si hay problemas)
   ↓
Volver a 5 (Ciclo continuo)
```

---

## 💾 Archivos Importantes

### Para Desplegar
- `deploy-all.sh` - Script principal
- `k8s/*.yaml` - Manifests de Kubernetes
- `terraform/main.tf` - Configuración de IaC

### Para Configurar
- `.env.example` - Variables de entorno
- `terraform/terraform.tfvars.example` - Variables de Terraform
- `Jenkinsfile` - Pipeline de CI/CD

### Para Operar
- `verify-deployment.sh` - Verificación
- `cleanup.sh` - Limpieza
- `observability/resilience-test.sh` - Tests

### Para Aprender
- `README.md` - Guía principal
- `Arquitectura del Laboratorio DevOps.md` - Documentación técnica
- `Jenkinsfile` - Pipeline comentado

---

## 🔐 Archivos Sensibles

Estos archivos contienen información sensible y NO deben ser commiteados a Git:

- `.env` (copiar de `.env.example`)
- `terraform/terraform.tfvars` (copiar de `.env.example`)
- Backups de Jenkins y MySQL
- Credenciales de Docker Registry

Asegúrate de que `.gitignore` está configurado correctamente.

---

## 📈 Tamaño del Proyecto

```
Total de archivos: ~50+
Líneas de código: ~10,000+
Documentación: ~5,000+ líneas
Scripts: ~2,000+ líneas
Configuración: ~3,000+ líneas
```

---

## 🎯 Objetivos de Cada Archivo

### Despliegue Automatizado
- `deploy-all.sh` - Automatizar todo el proceso
- `cleanup.sh` - Limpiar recursos
- `verify-deployment.sh` - Validar que funciona

### Infraestructura
- `k8s/*.yaml` - Definir recursos en Kubernetes
- `terraform/main.tf` - Definir infraestructura como código
- `ansible/playbook.yml` - Automatizar configuración

### CI/CD
- `Jenkinsfile` - Definir pipeline de despliegue
- `docker/Dockerfile` - Empaquetar la aplicación
- `jenkins/*.sh` - Configurar Jenkins

### Observabilidad
- `observability/prometheus/prometheus.yaml` - Recolectar métricas
- `observability/grafana/grafana-datasource.yaml` - Visualizar datos
- `observability/resilience-test.sh` - Probar resiliencia

### Documentación
- `README.md` - Guía principal
- `Guía de Inicio Rápido - 30 Minutos.md` - Inicio rápido
- `TROUBLESHOOTING.md` - Solución de problemas
- `MAINTENANCE.md` - Operación diaria

---

## ✅ Checklist de Archivos

Verifica que tienes todos estos archivos:

- [ ] README.md
- [ ] Guía de Inicio Rápido - 30 Minutos.md
- [ ] Arquitectura del Laboratorio DevOps.md
- [ ] Guía de Inicio Rápido - Jenkins Local.md
- [ ] TROUBLESHOOTING.md
- [ ] MAINTENANCE.md
- [ ] Cambios en Versión 2.0 - Migración a Jenkins Local.md
- [ ] Índice de Archivos del Proyecto.md (este archivo)
- [ ] deploy-all.sh
- [ ] cleanup.sh
- [ ] verify-deployment.sh
- [ ] Jenkinsfile
- [ ] docker/Dockerfile
- [ ] k8s/*.yaml (8 archivos)
- [ ] terraform/main.tf
- [ ] ansible/playbook.yml
- [ ] jenkins/*.sh (2 archivos)
- [ ] observability/*.yaml (2+ archivos)
- [ ] observability/resilience-test.sh

---

**Total: 40+ archivos listos para usar**

¡Todo está listo para desplegar! 🚀
