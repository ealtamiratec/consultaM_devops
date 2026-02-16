# Guía de Troubleshooting - Laboratorio DevOps

Esta guía te ayudará a resolver problemas comunes durante el despliegue y operación del laboratorio DevOps.

## 📋 Tabla de Contenidos

1. [Problemas de Kubernetes](#problemas-de-kubernetes)
2. [Problemas de Jenkins](#problemas-de-jenkins)
3. [Problemas de Docker Registry](#problemas-de-docker-registry)
4. [Problemas de la Aplicación](#problemas-de-la-aplicación)
5. [Problemas de Almacenamiento](#problemas-de-almacenamiento)
6. [Problemas de Conectividad](#problemas-de-conectividad)
7. [Problemas de Recursos](#problemas-de-recursos)

---

## Problemas de Kubernetes

### Kubernetes no está disponible

**Síntoma:** `kubectl cluster-info` falla

**Soluciones:**

```bash
# Verificar que Docker Desktop está corriendo
docker ps

# Verificar el contexto de kubectl
kubectl config current-context

# Cambiar al contexto de Docker Desktop
kubectl config use-context docker-desktop

# Reiniciar Kubernetes en Docker Desktop
# Abre Docker Desktop > Preferences > Kubernetes > Reset Kubernetes Cluster
```

### Namespace no existe

**Síntoma:** `error: namespaces "consulta-medica" not found`

**Soluciones:**

```bash
# Crear el namespace
kubectl create namespace consulta-medica

# Verificar que se creó
kubectl get namespace consulta-medica
```

### Pods no se están creando

**Síntoma:** `kubectl get pods` muestra lista vacía

**Soluciones:**

```bash
# Verificar eventos del namespace
kubectl get events -n consulta-medica

# Describir el namespace
kubectl describe namespace consulta-medica

# Verificar que hay suficientes recursos
kubectl top nodes

# Ver logs de los pods que fallan
kubectl logs -n consulta-medica <pod-name>
```

---

## Problemas de Jenkins

### Jenkins no está accesible

**Síntoma:** `http://localhost:8080` no responde

**Soluciones:**

```bash
# Verificar que el pod está corriendo
kubectl get pods -n consulta-medica -l app=jenkins

# Ver los logs de Jenkins
kubectl logs -n consulta-medica -l app=jenkins

# Describir el pod
kubectl describe pod -n consulta-medica -l app=jenkins

# Verificar el servicio
kubectl get svc -n consulta-medica -l app=jenkins

# Hacer port-forward manual
kubectl port-forward -n consulta-medica svc/jenkins 8080:8080
```

### Jenkins está corriendo pero lento

**Síntoma:** Jenkins tarda mucho en responder

**Soluciones:**

```bash
# Verificar recursos disponibles
kubectl top pod -n consulta-medica -l app=jenkins

# Aumentar la memoria de Jenkins
# Editar: k8s/jenkins-deployment.yaml
# Cambiar JAVA_OPTS y limits de memoria

# Reiniciar Jenkins
kubectl rollout restart deployment/jenkins -n consulta-medica
```

### No puedo acceder a Jenkins con credenciales

**Síntoma:** Usuario/contraseña incorrecto

**Soluciones:**

```bash
# Las credenciales por defecto son: admin/admin
# Si no funcionan, reiniciar Jenkins

kubectl delete pod -n consulta-medica -l app=jenkins

# Esperar a que se cree un nuevo pod
kubectl get pods -n consulta-medica -l app=jenkins -w
```

### El pipeline de Jenkins falla

**Síntoma:** Build falla con errores

**Soluciones:**

```bash
# Ver los logs del build en Jenkins
# Jenkins > Job > Build > Console Output

# Verificar que kubectl está disponible en Jenkins
kubectl exec -it -n consulta-medica <jenkins-pod> -- kubectl version

# Verificar que Docker está disponible
kubectl exec -it -n consulta-medica <jenkins-pod> -- docker version

# Ver los logs del pod de Jenkins
kubectl logs -n consulta-medica -l app=jenkins -f
```

---

## Problemas de Docker Registry

### Docker Registry no está accesible

**Síntoma:** `curl http://localhost:5000/v2/` falla

**Soluciones:**

```bash
# Verificar que el pod está corriendo
kubectl get pods -n consulta-medica -l app=docker-registry

# Ver los logs del registry
kubectl logs -n consulta-medica -l app=docker-registry

# Verificar el servicio
kubectl get svc -n consulta-medica -l app=docker-registry

# Hacer port-forward manual
kubectl port-forward -n consulta-medica svc/docker-registry 5000:5000
```

### No puedo hacer push a Docker Registry

**Síntoma:** `docker push localhost:5000/imagen:tag` falla

**Soluciones:**

```bash
# Verificar que Docker está configurado para registros inseguros
# Docker Desktop > Settings > Docker Engine
# Debe contener: "insecure-registries": ["localhost:5000"]

# Reiniciar Docker Desktop

# Probar la conexión al registry
curl -v http://localhost:5000/v2/

# Ver los logs del registry
kubectl logs -n consulta-medica -l app=docker-registry
```

### Imágenes no se están almacenando

**Síntoma:** Las imágenes desaparecen después de reiniciar

**Soluciones:**

```bash
# Verificar que el PVC está montado
kubectl get pvc -n consulta-medica -l app=docker-registry

# Verificar el estado del PVC
kubectl describe pvc -n consulta-medica docker-registry-pvc

# Listar imágenes en el registry
curl http://localhost:5000/v2/_catalog
```

---

## Problemas de la Aplicación

### La aplicación no está respondiendo

**Síntoma:** `http://localhost` no responde

**Soluciones:**

```bash
# Verificar que el pod está corriendo
kubectl get pods -n consulta-medica -l app=consulta-medica

# Ver los logs de la aplicación
kubectl logs -n consulta-medica -l app=consulta-medica

# Describir el pod
kubectl describe pod -n consulta-medica -l app=consulta-medica

# Hacer port-forward manual
kubectl port-forward -n consulta-medica svc/consulta-medica 8888:80
# Luego acceder a http://localhost:8888
```

### La aplicación no puede conectar a MySQL

**Síntoma:** Error de conexión a base de datos

**Soluciones:**

```bash
# Verificar que MySQL está corriendo
kubectl get pods -n consulta-medica -l app=mysql

# Ver los logs de MySQL
kubectl logs -n consulta-medica -l app=mysql

# Verificar la conectividad entre pods
kubectl exec -it -n consulta-medica <app-pod> -- \
  mysql -h mysql.consulta-medica.svc.cluster.local \
  -u consulta_user -p consulta_pass \
  -e "SELECT 1"

# Verificar las variables de entorno en la aplicación
kubectl exec -it -n consulta-medica <app-pod> -- env | grep MYSQL
```

### Errores de permisos en la aplicación

**Síntoma:** Errores de permiso denegado

**Soluciones:**

```bash
# Verificar permisos en el contenedor
kubectl exec -it -n consulta-medica <app-pod> -- ls -la /var/www/html

# Verificar el usuario del contenedor
kubectl exec -it -n consulta-medica <app-pod> -- whoami

# Ver los logs de Apache
kubectl exec -it -n consulta-medica <app-pod> -- tail -f /var/log/apache2/error.log
```

---

## Problemas de Almacenamiento

### PVC no se vincula

**Síntoma:** PVC está en estado "Pending"

**Soluciones:**

```bash
# Verificar el estado del PVC
kubectl describe pvc -n consulta-medica <pvc-name>

# Ver los eventos
kubectl get events -n consulta-medica --sort-by='.lastTimestamp'

# Verificar que hay espacio disponible
df -h

# Crear manualmente un PV si es necesario
kubectl get pv
```

### Espacio en disco insuficiente

**Síntoma:** Pods se quedan en estado "Pending"

**Soluciones:**

```bash
# Verificar el espacio disponible
df -h

# Limpiar imágenes Docker no utilizadas
docker image prune -a

# Limpiar volúmenes no utilizados
docker volume prune

# Aumentar el tamaño del disco de Docker Desktop
# Docker Desktop > Preferences > Resources > Disk Image Size
```

---

## Problemas de Conectividad

### No puedo acceder a los servicios desde fuera del clúster

**Síntoma:** Los servicios no son accesibles desde localhost

**Soluciones:**

```bash
# Verificar que el servicio es de tipo LoadBalancer
kubectl get svc -n consulta-medica

# Verificar el puerto del servicio
kubectl get svc -n consulta-medica <service-name> -o yaml | grep -A 5 "ports:"

# Hacer port-forward manual
kubectl port-forward -n consulta-medica svc/<service-name> <puerto-local>:<puerto-servicio>

# Verificar que el puerto no está siendo utilizado
lsof -i :<puerto>
```

### Los pods no pueden comunicarse entre sí

**Síntoma:** Errores de conexión entre servicios

**Soluciones:**

```bash
# Verificar DNS
kubectl exec -it -n consulta-medica <pod> -- nslookup <service-name>

# Verificar conectividad de red
kubectl exec -it -n consulta-medica <pod> -- ping <otro-pod-ip>

# Verificar las políticas de red
kubectl get networkpolicies -n consulta-medica

# Ver los logs de red
kubectl logs -n consulta-medica <pod> --previous
```

---

## Problemas de Recursos

### Los pods se están reiniciando constantemente

**Síntoma:** CrashLoopBackOff

**Soluciones:**

```bash
# Ver los logs del pod
kubectl logs -n consulta-medica <pod-name> --previous

# Describir el pod para ver los eventos
kubectl describe pod -n consulta-medica <pod-name>

# Aumentar los límites de recursos
# Editar el deployment y aumentar memory/cpu limits

# Verificar que hay suficientes recursos disponibles
kubectl top nodes
kubectl top pods -n consulta-medica
```

### Falta de memoria

**Síntoma:** OOMKilled

**Soluciones:**

```bash
# Verificar el uso de memoria
kubectl top pods -n consulta-medica

# Aumentar el límite de memoria
# Editar: k8s/<deployment>.yaml
# Cambiar: resources.limits.memory

# Reiniciar el deployment
kubectl rollout restart deployment/<deployment-name> -n consulta-medica
```

### Falta de CPU

**Síntoma:** Los pods están lentos o no responden

**Soluciones:**

```bash
# Verificar el uso de CPU
kubectl top pods -n consulta-medica

# Aumentar el límite de CPU
# Editar: k8s/<deployment>.yaml
# Cambiar: resources.limits.cpu

# Reducir el número de réplicas si es necesario
kubectl scale deployment/<deployment-name> --replicas=1 -n consulta-medica
```

---

## Comandos Útiles de Debugging

```bash
# Ver todos los recursos en el namespace
kubectl get all -n consulta-medica

# Ver los eventos del namespace
kubectl get events -n consulta-medica --sort-by='.lastTimestamp'

# Ver los logs de un pod
kubectl logs -n consulta-medica <pod-name>

# Ver los logs en tiempo real
kubectl logs -n consulta-medica <pod-name> -f

# Ejecutar comandos en un pod
kubectl exec -it -n consulta-medica <pod-name> -- /bin/bash

# Describir un recurso
kubectl describe <tipo> -n consulta-medica <nombre>

# Ver los recursos disponibles
kubectl top nodes
kubectl top pods -n consulta-medica

# Hacer port-forward
kubectl port-forward -n consulta-medica <pod-name> <puerto-local>:<puerto-pod>

# Copiar archivos desde un pod
kubectl cp -n consulta-medica <pod-name>:<ruta-remota> <ruta-local>

# Copiar archivos a un pod
kubectl cp -n consulta-medica <ruta-local> <pod-name>:<ruta-remota>
```

---

## Cuándo Contactar Soporte

Si después de seguir estas soluciones el problema persiste, recopila la siguiente información:

```bash
# Información del sistema
kubectl version
docker version
terraform version

# Información del cluster
kubectl cluster-info dump

# Logs de todos los pods
kubectl logs -n consulta-medica --all-containers=true

# Descripción de todos los recursos
kubectl describe all -n consulta-medica

# Eventos del namespace
kubectl get events -n consulta-medica
```

Guarda esta información en un archivo para facilitar el debugging.

---

## Recursos Adicionales

- [Documentación de Kubernetes](https://kubernetes.io/docs/)
- [Documentación de Jenkins](https://www.jenkins.io/doc/)
- [Documentación de Docker](https://docs.docker.com/)
- [Troubleshooting de Kubernetes](https://kubernetes.io/docs/tasks/debug-application-cluster/)
