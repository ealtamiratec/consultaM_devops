# Guía de Inicio Rápido - 30 Minutos

Despliega el laboratorio DevOps completo en 30 minutos.

## ⏱️ Tiempo Estimado

- Verificación de requisitos: 2 minutos
- Configuración de Docker: 3 minutos
- Despliegue automático: 15 minutos
- Verificación final: 5 minutos
- Configuración de Jenkins: 5 minutos

**Total: 30 minutos**

---

## 📋 Requisitos Previos

Verifica que tienes instalado:

```bash
# Docker Desktop (con Kubernetes habilitado)
docker --version
kubectl version --client

# Git
git --version

# Terraform
terraform version
```

Si falta algo, instálalo antes de continuar.

---

## 🚀 Paso 1: Configurar Docker (3 minutos)

### En macOS/Windows (Docker Desktop):

1. Abre **Docker Desktop**
2. Ve a **Settings** → **Docker Engine**
3. Busca el JSON de configuración
4. Agrega esta línea (si no existe):
   ```json
   "insecure-registries": ["localhost:5000"]
   ```
5. Haz clic en **Apply & Restart**

### En Linux:

```bash
# Editar el archivo de configuración
sudo nano /etc/docker/daemon.json

# Agregar:
{
  "insecure-registries": ["localhost:5000"]
}

# Guardar y reiniciar Docker
sudo systemctl restart docker
```

**✓ Completado:** Docker está configurado para usar el registro local.

---

## 🚀 Paso 2: Despliegue Automático (15 minutos)

```bash
# 1. Navega al directorio del proyecto
cd /ruta/a/consulta_medica-devops

# 2. Haz los scripts ejecutables
chmod +x deploy-all.sh cleanup.sh verify-deployment.sh

# 3. Ejecuta el despliegue completo
./deploy-all.sh
```

El script hará automáticamente:

- ✓ Verificar requisitos
- ✓ Crear namespace
- ✓ Desplegar infraestructura con Terraform
- ✓ Desplegar Docker Registry
- ✓ Desplegar Jenkins
- ✓ Desplegar aplicación y MySQL
- ✓ Desplegar Prometheus y Grafana

**Espera a que termine. Verás un mensaje de éxito.**

---

## 🚀 Paso 3: Verificación (5 minutos)

```bash
# Ejecutar verificación completa
./verify-deployment.sh
```

Deberías ver:

```
✓ Kubernetes está disponible
✓ Namespace consulta-medica existe
✓ Se encontraron X pods
✓ Se encontraron X servicios
✓ Jenkins está corriendo
✓ Docker Registry está corriendo
✓ Aplicación está corriendo
✓ MySQL está corriendo
✓ Prometheus está corriendo
✓ Grafana está corriendo
```

---

## 🚀 Paso 4: Acceder a los Servicios (5 minutos)

### Jenkins - CI/CD

```
URL: http://localhost:8080
Usuario: admin
Contraseña: admin
```

### Docker Registry - Registro Local

```
URL: http://localhost:5000
Comando: docker push localhost:5000/imagen:tag
```

### Aplicación - Consulta Médica

```
URL: http://localhost
```

### Prometheus - Monitoreo

```
URL: http://localhost:9090
```

### Grafana - Dashboards

```
URL: http://localhost:3000
Usuario: admin
Contraseña: admin
```

---

## 🚀 Paso 5: Configurar Jenkins (5 minutos)

### Crear el Job de Pipeline

1. Abre Jenkins: `http://localhost:8080`
2. Haz clic en **New Item**
3. Ingresa el nombre: `consulta-medica-pipeline`
4. Selecciona **Pipeline**
5. Haz clic en **OK**

### Configurar el Pipeline

1. En **Pipeline**, selecciona **Pipeline script from SCM**
2. En **SCM**, selecciona **Git**
3. En **Repository URL**, ingresa:
   ```
   file:///ruta/a/consulta_medica-devops
   ```
   O si tienes un servidor Git local:
   ```
   http://localhost:3000/usuario/consulta_medica-devops.git
   ```
4. En **Script Path**, asegúrate de que sea: `Jenkinsfile`
5. Haz clic en **Save**

### Ejecutar el Pipeline

1. En la página del job, haz clic en **Build Now**
2. Observa el progreso en **Build History**
3. Haz clic en el build para ver los logs

El pipeline ejecutará:
- ✓ Checkout del código
- ✓ Build de la imagen Docker
- ✓ Tests
- ✓ Security Scan
- ✓ Push al registro local
- ✓ Deploy a Kubernetes
- ✓ Verificación

---

## ✅ ¡Listo!

Tu laboratorio DevOps está completamente funcional.

### Próximos Pasos

1. **Explorar Jenkins:**
   - Crea más jobs
   - Configura webhooks
   - Agrega más etapas al pipeline

2. **Monitorear con Grafana:**
   - Crea dashboards personalizados
   - Configura alertas
   - Integra con notificaciones

3. **Desarrollar la Aplicación:**
   - Modifica el código en `app/consulta_medica`
   - Ejecuta el pipeline para desplegar cambios
   - Verifica en `http://localhost`

4. **Aprender DevOps:**
   - Revisa los manifests de Kubernetes en `k8s/`
   - Estudia el Jenkinsfile
   - Experimenta con Terraform en `terraform/`

---

## 🆘 Problemas Comunes

### Docker Registry no está disponible

```bash
# Reiniciar Docker
# Docker Desktop > Restart

# O en Linux:
sudo systemctl restart docker
```

### Jenkins no responde

```bash
# Ver los logs
kubectl logs -n consulta-medica -l app=jenkins

# Reiniciar Jenkins
kubectl delete pod -n consulta-medica -l app=jenkins
```

### La aplicación no está disponible

```bash
# Ver los logs de la aplicación
kubectl logs -n consulta-medica -l app=consulta-medica

# Verificar que MySQL está corriendo
kubectl get pods -n consulta-medica -l app=mysql
```

Para más ayuda, ver **TROUBLESHOOTING.md**

---

## 📚 Documentación Completa

- **README.md** - Guía principal del proyecto
- **Arquitectura del Laboratorio DevOps.md** - Documentación técnica detallada
- **Guía de Inicio Rápido - Jenkins Local.md** - Guía específica de Jenkins
- **TROUBLESHOOTING.md** - Solución de problemas
- **MAINTENANCE.md** - Operación y mantenimiento
- **Cambios en Versión 2.0 - Migración a Jenkins Local.md** - Cambios en la versión 2.0

---

## 📞 Soporte

Si necesitas ayuda:

1. Consulta **TROUBLESHOOTING.md**
2. Revisa los logs con `kubectl logs`
3. Ejecuta `./verify-deployment.sh` para diagnosticar

---

**¡Felicidades! Tu laboratorio DevOps está listo para usar.** 🎉

Ahora puedes:
- Desarrollar y desplegar aplicaciones
- Aprender sobre CI/CD con Jenkins
- Experimentar con Kubernetes
- Monitorear con Prometheus y Grafana
- Automatizar todo con Terraform y Ansible

¡Diviértete aprendiendo DevOps! 🚀
