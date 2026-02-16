# Guía de Inicio Rápido - Jenkins Local

Despliega el laboratorio DevOps con Jenkins en menos de 20 minutos.

## 1. Requisitos Previos

Verifica que tienes instalado:

```bash
# Docker Desktop con Kubernetes habilitado
docker version
kubectl version --client

# Terraform
terraform version

# Ansible
ansible --version
```

## 2. Configurar Docker para Registro Inseguro

**Importante:** Docker necesita estar configurado para usar el registro local sin HTTPS.

### En Docker Desktop (macOS/Windows):

1. Abre Docker Desktop
2. Ve a **Settings > Docker Engine**
3. Localiza el JSON de configuración
4. Agrega esta línea (si no existe):
   ```json
   "insecure-registries": ["localhost:5000"]
   ```
5. Haz clic en **Apply & Restart**

### En Linux:

Edita `/etc/docker/daemon.json`:

```json
{
  "insecure-registries": ["localhost:5000"]
}
```

Luego reinicia Docker:

```bash
sudo systemctl restart docker
```

## 3. Desplegar la Infraestructura Base

```bash
cd terraform

# Inicializar Terraform
terraform init

# Aplicar la configuración
terraform apply --auto-approve

# Obtener los outputs
terraform output
```

**Espera 2-3 minutos** a que todos los pods estén listos.

## 4. Desplegar Jenkins y Docker Registry

```bash
cd ../jenkins

# Hacer el script ejecutable
chmod +x deploy-jenkins.sh

# Ejecutar el despliegue
./deploy-jenkins.sh
```

Este script:
- Despliega Docker Registry en puerto 5000
- Despliega Jenkins en puerto 8080
- Configura permisos de RBAC
- Muestra información de acceso

## 5. Acceder a Jenkins

Abre tu navegador y ve a:

```
http://localhost:8080
```

**Credenciales:**
- Usuario: `admin`
- Contraseña: `admin`

## 6. Crear el Job de Pipeline en Jenkins

1. En la página principal de Jenkins, haz clic en **New Item**
2. Ingresa el nombre: `consulta-medica-pipeline`
3. Selecciona **Pipeline** como tipo de job
4. Haz clic en **OK**

### Configurar el Pipeline:

1. En la sección **Pipeline**, selecciona **Pipeline script from SCM**
2. En **SCM**, selecciona **Git**
3. En **Repository URL**, ingresa la ruta a tu repositorio local:
   ```
   file:///ruta/a/tu/consulta_medica-devops
   ```
   O si tienes un servidor Git local:
   ```
   http://localhost:3000/usuario/consulta_medica-devops.git
   ```
4. En **Script Path**, asegúrate de que sea: `Jenkinsfile`
5. Haz clic en **Save**

## 7. Ejecutar el Pipeline

1. En la página del job, haz clic en **Build Now**
2. Observa el progreso en **Build History**
3. Haz clic en el build para ver los logs en tiempo real

El pipeline ejecutará:
- ✓ Checkout del código
- ✓ Build de la imagen Docker
- ✓ Tests
- ✓ Security Scan
- ✓ Push al registro local
- ✓ Deploy a Kubernetes
- ✓ Verificación

## 8. Verificar el Despliegue

```bash
# Ver todos los pods
kubectl get pods -n consulta-medica

# Ver los servicios
kubectl get svc -n consulta-medica

# Ver los logs de la aplicación
kubectl logs -n consulta-medica -l app=consulta-medica
```

## 9. Acceder a los Servicios

| Servicio | URL | Credenciales |
| :--- | :--- | :--- |
| Aplicación | `http://localhost` | - |
| Prometheus | `http://localhost:9090` | - |
| Grafana | `http://localhost:3000` | admin/admin |
| Jenkins | `http://localhost:8080` | admin/admin |
| Docker Registry | `http://localhost:5000` | - |

## 10. Ejecutar Pruebas de Resiliencia

```bash
# Hacer el script ejecutable
chmod +x observability/resilience-test.sh

# Ejecutar las pruebas
./observability/resilience-test.sh
```

## Solución de Problemas

### Jenkins no está disponible

```bash
# Verificar que el pod está corriendo
kubectl get pods -n consulta-medica -l app=jenkins

# Ver los logs de Jenkins
kubectl logs -n consulta-medica -l app=jenkins
```

### Docker Registry no está disponible

```bash
# Verificar que el pod está corriendo
kubectl get pods -n consulta-medica -l app=docker-registry

# Probar la conexión
curl -s http://localhost:5000/v2/
```

### El build de Jenkins falla

1. Revisa los logs en la consola de Jenkins
2. Verifica que Docker está configurado correctamente
3. Asegúrate de que Kubernetes está disponible
4. Revisa los logs del pod de Jenkins

### La aplicación no está disponible

```bash
# Verificar que el pod está corriendo
kubectl get pods -n consulta-medica -l app=consulta-medica

# Ver los logs
kubectl logs -n consulta-medica -l app=consulta-medica

# Describir el pod para ver errores
kubectl describe pod -n consulta-medica <pod-name>
```

## Próximos Pasos

1. **Modificar el Jenkinsfile:** Personaliza el pipeline según tus necesidades
2. **Crear más jobs:** Agrega jobs adicionales para diferentes tareas
3. **Configurar webhooks:** Configura webhooks para disparar builds automáticamente
4. **Agregar más etapas:** Añade etapas de test, security, etc.
5. **Integrar con Git:** Usa un servidor Git local para automatizar los builds

## Recursos Adicionales

- [Documentación de Jenkins](https://www.jenkins.io/doc/)
- [Documentación de Kubernetes](https://kubernetes.io/docs/)
- [Documentación de Docker Registry](https://docs.docker.com/registry/)
- [Documentación de Terraform](https://www.terraform.io/docs)
