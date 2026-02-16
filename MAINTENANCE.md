# Guía de Mantenimiento y Operación

Esta guía proporciona procedimientos para mantener y operar el laboratorio DevOps en producción.

## 📋 Tabla de Contenidos

1. [Operaciones Diarias](#operaciones-diarias)
2. [Monitoreo](#monitoreo)
3. [Backup y Recuperación](#backup-y-recuperación)
4. [Actualizaciones](#actualizaciones)
5. [Escalado](#escalado)
6. [Seguridad](#seguridad)
7. [Optimización](#optimización)

---

## Operaciones Diarias

### Verificar el Estado del Sistema

```bash
# Verificar que todos los pods están corriendo
kubectl get pods -n consulta-medica

# Verificar el uso de recursos
kubectl top nodes
kubectl top pods -n consulta-medica

# Verificar los eventos recientes
kubectl get events -n consulta-medica --sort-by='.lastTimestamp' | tail -20
```

### Revisar los Logs

```bash
# Logs de Jenkins
kubectl logs -n consulta-medica -l app=jenkins -f

# Logs de la aplicación
kubectl logs -n consulta-medica -l app=consulta-medica -f

# Logs de MySQL
kubectl logs -n consulta-medica -l app=mysql -f

# Logs de Prometheus
kubectl logs -n consulta-medica -l app=prometheus -f

# Logs de Grafana
kubectl logs -n consulta-medica -l app=grafana -f
```

### Ejecutar el Script de Verificación

```bash
# Ejecutar verificación completa
./verify-deployment.sh

# Esto verificará:
# - Kubernetes disponible
# - Namespace existe
# - Todos los pods corriendo
# - Todos los servicios accesibles
# - Volúmenes persistentes
```

---

## Monitoreo

### Acceder a Prometheus

```
http://localhost:9090
```

**Métricas importantes a monitorear:**

- `container_cpu_usage_seconds_total` - Uso de CPU
- `container_memory_usage_bytes` - Uso de memoria
- `container_network_receive_bytes_total` - Tráfico de red recibido
- `container_network_transmit_bytes_total` - Tráfico de red transmitido
- `kube_pod_status_phase` - Estado de los pods

### Acceder a Grafana

```
http://localhost:3000
Usuario: admin
Contraseña: admin
```

**Dashboards recomendados:**

1. **Kubernetes Cluster Monitoring** - Estado general del clúster
2. **Pod Resource Usage** - Uso de recursos por pod
3. **Container Network** - Tráfico de red
4. **MySQL Performance** - Métricas de MySQL

### Crear Alertas

En Prometheus, puedes crear alertas para:

```promql
# CPU alta
rate(container_cpu_usage_seconds_total[5m]) > 0.8

# Memoria alta
container_memory_usage_bytes / 1024 / 1024 > 800

# Pod reiniciándose
increase(kube_pod_container_status_restarts_total[1h]) > 0

# Servicio no disponible
up{job="kubernetes-pods"} == 0
```

---

## Backup y Recuperación

### Backup de Jenkins

```bash
# Backup de la configuración de Jenkins
kubectl exec -n consulta-medica -it <jenkins-pod> -- \
  tar czf /tmp/jenkins-backup.tar.gz /var/jenkins_home

# Copiar el backup a la máquina local
kubectl cp -n consulta-medica <jenkins-pod>:/tmp/jenkins-backup.tar.gz \
  ./jenkins-backup.tar.gz
```

### Backup de Docker Registry

```bash
# Listar todas las imágenes
curl -s http://localhost:5000/v2/_catalog | jq '.repositories'

# Hacer backup de las imágenes
docker pull localhost:5000/<imagen>:tag
docker save localhost:5000/<imagen>:tag > <imagen>.tar
```

### Backup de MySQL

```bash
# Hacer dump de la base de datos
kubectl exec -n consulta-medica -it <mysql-pod> -- \
  mysqldump -u consulta_user -p consulta_pass consulta_medica > backup.sql

# Copiar el backup
kubectl cp -n consulta-medica <mysql-pod>:/tmp/backup.sql ./backup.sql
```

### Backup Automático

Crea un CronJob para backups automáticos:

```yaml
apiVersion: batch/v1
kind: CronJob
metadata:
  name: backup-jenkins
  namespace: consulta-medica
spec:
  schedule: "0 2 * * *"  # 2 AM diariamente
  jobTemplate:
    spec:
      template:
        spec:
          serviceAccountName: jenkins
          containers:
          - name: backup
            image: busybox
            command:
            - /bin/sh
            - -c
            - tar czf /backup/jenkins-$(date +%Y%m%d).tar.gz /var/jenkins_home
            volumeMounts:
            - name: jenkins-home
              mountPath: /var/jenkins_home
            - name: backup-storage
              mountPath: /backup
          volumes:
          - name: jenkins-home
            persistentVolumeClaim:
              claimName: jenkins-pvc
          - name: backup-storage
            hostPath:
              path: /backups
          restartPolicy: OnFailure
```

### Recuperación de Backup

```bash
# Restaurar Jenkins
kubectl exec -n consulta-medica -it <jenkins-pod> -- \
  tar xzf /tmp/jenkins-backup.tar.gz -C /

# Restaurar MySQL
kubectl exec -n consulta-medica -it <mysql-pod> -- \
  mysql -u consulta_user -p consulta_pass consulta_medica < backup.sql

# Restaurar imágenes Docker
docker load < <imagen>.tar
docker tag <imagen>:tag localhost:5000/<imagen>:tag
docker push localhost:5000/<imagen>:tag
```

---

## Actualizaciones

### Actualizar la Aplicación

```bash
# 1. Construir nueva imagen
docker build -t localhost:5000/consulta-medica:v2.0 .

# 2. Hacer push al registry
docker push localhost:5000/consulta-medica:v2.0

# 3. Actualizar el deployment
kubectl set image deployment/consulta-medica \
  consulta-medica=localhost:5000/consulta-medica:v2.0 \
  -n consulta-medica

# 4. Monitorear el rollout
kubectl rollout status deployment/consulta-medica -n consulta-medica
```

### Rollback a Versión Anterior

```bash
# Ver el historial de rollouts
kubectl rollout history deployment/consulta-medica -n consulta-medica

# Rollback a la revisión anterior
kubectl rollout undo deployment/consulta-medica -n consulta-medica

# Rollback a una revisión específica
kubectl rollout undo deployment/consulta-medica \
  --to-revision=2 -n consulta-medica
```

### Actualizar Jenkins

```bash
# 1. Hacer backup
kubectl exec -n consulta-medica -it <jenkins-pod> -- \
  tar czf /tmp/jenkins-backup.tar.gz /var/jenkins_home

# 2. Actualizar la imagen en el deployment
kubectl set image deployment/jenkins \
  jenkins=jenkins/jenkins:lts-jdk11 \
  -n consulta-medica

# 3. Monitorear el rollout
kubectl rollout status deployment/jenkins -n consulta-medica
```

---

## Escalado

### Escalar Horizontalmente

```bash
# Aumentar el número de réplicas de la aplicación
kubectl scale deployment/consulta-medica --replicas=5 -n consulta-medica

# Verificar el escalado
kubectl get pods -n consulta-medica -l app=consulta-medica
```

### Escalar Verticalmente

```bash
# Editar el deployment para cambiar recursos
kubectl edit deployment/consulta-medica -n consulta-medica

# Cambiar:
# resources:
#   requests:
#     cpu: 500m
#     memory: 512Mi
#   limits:
#     cpu: 1000m
#     memory: 1Gi

# Aplicar los cambios
kubectl rollout restart deployment/consulta-medica -n consulta-medica
```

### Auto-scaling

Crear un HorizontalPodAutoscaler:

```yaml
apiVersion: autoscaling/v2
kind: HorizontalPodAutoscaler
metadata:
  name: consulta-medica-hpa
  namespace: consulta-medica
spec:
  scaleTargetRef:
    apiVersion: apps/v1
    kind: Deployment
    name: consulta-medica
  minReplicas: 2
  maxReplicas: 10
  metrics:
  - type: Resource
    resource:
      name: cpu
      target:
        type: Utilization
        averageUtilization: 70
  - type: Resource
    resource:
      name: memory
      target:
        type: Utilization
        averageUtilization: 80
```

---

## Seguridad

### Cambiar Contraseñas

```bash
# Cambiar contraseña de Jenkins
# Jenkins > Manage Jenkins > Manage Users > admin > Configure

# Cambiar contraseña de Grafana
# Grafana > Configuration > Change Password

# Cambiar contraseña de MySQL
kubectl exec -n consulta-medica -it <mysql-pod> -- \
  mysql -u root -p -e "ALTER USER 'consulta_user'@'%' IDENTIFIED BY 'nueva_contraseña';"
```

### Actualizar Secretos

```bash
# Crear un nuevo secret
kubectl create secret generic mysql-credentials \
  --from-literal=password=nueva_contraseña \
  -n consulta-medica --dry-run=client -o yaml | kubectl apply -f -

# Actualizar los deployments para usar el nuevo secret
kubectl set env deployment/consulta-medica \
  MYSQL_PASSWORD=$(kubectl get secret mysql-credentials -n consulta-medica \
  -o jsonpath='{.data.password}' | base64 -d) \
  -n consulta-medica
```

### Habilitar RBAC

```bash
# Crear un ServiceAccount con permisos limitados
kubectl create serviceaccount jenkins-ci -n consulta-medica

# Crear un Role con permisos específicos
kubectl create role jenkins-role \
  --verb=get,list,watch,create,update,patch,delete \
  --resource=pods,deployments,services \
  -n consulta-medica

# Vincular el Role al ServiceAccount
kubectl create rolebinding jenkins-rolebinding \
  --clusterrole=jenkins-role \
  --serviceaccount=consulta-medica:jenkins-ci \
  -n consulta-medica
```

---

## Optimización

### Optimizar el Uso de Recursos

```bash
# Identificar pods que usan más recursos
kubectl top pods -n consulta-medica --sort-by=memory

# Reducir los límites de recursos si es posible
kubectl set resources deployment/consulta-medica \
  --limits=cpu=500m,memory=512Mi \
  --requests=cpu=250m,memory=256Mi \
  -n consulta-medica
```

### Limpiar Recursos No Utilizados

```bash
# Eliminar pods completados
kubectl delete pod -n consulta-medica --field-selector status.phase=Succeeded

# Eliminar pods fallidos
kubectl delete pod -n consulta-medica --field-selector status.phase=Failed

# Limpiar imágenes Docker no utilizadas
docker image prune -a

# Limpiar volúmenes no utilizados
docker volume prune
```

### Optimizar Almacenamiento

```bash
# Ver el uso de almacenamiento
kubectl exec -n consulta-medica -it <pod> -- df -h

# Limpiar logs antiguos
kubectl exec -n consulta-medica -it <pod> -- \
  find /var/log -type f -mtime +30 -delete

# Comprimir logs
kubectl exec -n consulta-medica -it <pod> -- \
  find /var/log -type f -name "*.log" -exec gzip {} \;
```

---

## Checklist de Mantenimiento

### Diario
- [ ] Verificar que todos los pods están corriendo
- [ ] Revisar los logs de errores
- [ ] Verificar el uso de recursos
- [ ] Revisar los eventos del namespace

### Semanal
- [ ] Ejecutar el script de verificación
- [ ] Revisar los dashboards de Grafana
- [ ] Hacer backup de Jenkins
- [ ] Hacer backup de MySQL

### Mensual
- [ ] Revisar y actualizar las políticas de seguridad
- [ ] Limpiar recursos no utilizados
- [ ] Actualizar las imágenes base
- [ ] Revisar los logs de auditoría

### Trimestral
- [ ] Actualizar Kubernetes
- [ ] Actualizar Jenkins
- [ ] Actualizar las dependencias
- [ ] Revisar la capacidad del almacenamiento

---

## Contacto y Soporte

Para reportar problemas o solicitar soporte:

1. Recopila información del sistema (ver TROUBLESHOOTING.md)
2. Incluye los logs relevantes
3. Describe los pasos para reproducir el problema
4. Proporciona capturas de pantalla si es aplicable

---

**Última actualización:** Febrero 2026  
**Versión:** 2.0  
**Mantenedor:** DevOps Team
