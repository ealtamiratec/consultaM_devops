# Guía de Inicio Rápido - Laboratorio DevOps

Esta guía te permitirá desplegar el laboratorio DevOps en menos de 30 minutos.

## 1. Requisitos Previos

Verifica que tienes instalado:

```bash
# Verificar Docker Desktop con Kubernetes habilitado
docker version
kubectl version --client

# Verificar Terraform
terraform version

# Verificar Ansible
ansible --version
```

## 2. Clonar el Repositorio

```bash
git clone https://github.com/tu-usuario/consulta_medica-devops.git
cd consulta_medica-devops
```

## 3. Configurar Kubernetes Localmente

### Opción A: Usar Terraform (Recomendado)

```bash
cd terraform

# Inicializar Terraform
terraform init

# Validar la configuración
terraform validate

# Aplicar la configuración
terraform apply

# Obtener los outputs
terraform output
```

### Opción B: Usar Ansible

```bash
# Instalar la colección de Kubernetes para Ansible
ansible-galaxy collection install community.kubernetes

# Ejecutar el playbook
cd ansible
ansible-playbook playbook.yml
```

### Opción C: Aplicar Manifests Manualmente

```bash
# Crear namespace
kubectl create namespace consulta-medica

# Aplicar manifests de MySQL
kubectl apply -f k8s/mysql-deployment.yaml -n consulta-medica
kubectl apply -f k8s/mysql-service.yaml -n consulta-medica

# Esperar a que MySQL esté listo
kubectl wait --for=condition=ready pod -l app=mysql -n consulta-medica --timeout=300s

# Aplicar manifests de la aplicación
kubectl apply -f k8s/app-deployment.yaml -n consulta-medica
kubectl apply -f k8s/app-service.yaml -n consulta-medica

# Aplicar manifests de observabilidad
kubectl apply -f k8s/prometheus-deployment.yaml -n consulta-medica
kubectl apply -f k8s/grafana-deployment.yaml -n consulta-medica
```

## 4. Verificar el Despliegue

```bash
# Ver todos los recursos
kubectl get all -n consulta-medica

# Ver los pods
kubectl get pods -n consulta-medica

# Ver los servicios
kubectl get svc -n consulta-medica

# Ver los logs de la aplicación
kubectl logs -n consulta-medica -l app=consulta-medica
```

## 5. Acceder a los Servicios

### Opción A: Usando Port-Forward

```bash
# Aplicación
kubectl port-forward -n consulta-medica svc/consulta-medica 8080:80 &

# Prometheus
kubectl port-forward -n consulta-medica svc/prometheus 9090:9090 &

# Grafana
kubectl port-forward -n consulta-medica svc/grafana 3000:3000 &
```

Luego accede a:
- Aplicación: `http://localhost:8080`
- Prometheus: `http://localhost:9090`
- Grafana: `http://localhost:3000` (usuario: `admin`, contraseña: `admin`)

### Opción B: Usando LoadBalancer (Docker Desktop)

En Docker Desktop, los servicios LoadBalancer se exponen en `localhost`:

- Aplicación: `http://localhost`
- Prometheus: `http://localhost:9090`
- Grafana: `http://localhost:3000`

## 6. Ejecutar Pruebas de Resiliencia

```bash
# Hacer el script ejecutable
chmod +x observability/resilience-test.sh

# Ejecutar las pruebas
./observability/resilience-test.sh
```

## 7. Configurar CI/CD con GitHub Actions

1. Crea un fork del repositorio en GitHub.
2. Ve a `Settings > Secrets and variables > Actions`.
3. Agrega los siguientes secretos:
   - `DOCKER_USERNAME`: Tu usuario de Docker Hub
   - `DOCKER_PASSWORD`: Tu token de Docker Hub
   - `KUBE_CONFIG`: Tu archivo kubeconfig codificado en Base64

4. Haz un `push` a la rama `main` para activar el pipeline.

## 8. Monitorear el Pipeline

En GitHub, ve a `Actions` para ver el progreso del pipeline CI/CD.

## Solución de Problemas

### Los pods no se inician

```bash
# Ver eventos del namespace
kubectl get events -n consulta-medica --sort-by='.lastTimestamp'

# Ver logs detallados de un pod
kubectl logs -n consulta-medica <pod-name> -p  # -p para logs anteriores
```

### No puedo conectarme a la aplicación

```bash
# Verificar que el servicio está expuesto
kubectl get svc -n consulta-medica

# Verificar que el pod está corriendo
kubectl get pods -n consulta-medica

# Describir el pod para ver errores
kubectl describe pod -n consulta-medica <pod-name>
```

### MySQL no se conecta

```bash
# Verificar que MySQL está corriendo
kubectl get pods -n consulta-medica -l app=mysql

# Verificar los logs de MySQL
kubectl logs -n consulta-medica -l app=mysql

# Conectarse a MySQL desde otro pod
kubectl exec -it -n consulta-medica <app-pod> -- mysql -h mysql -u consulta_app -p
```

## Próximos Pasos

1. **Personalizar la Aplicación:** Modifica el código en `app/` según tus necesidades.
2. **Crear Dashboards en Grafana:** Accede a Grafana y crea dashboards personalizados.
3. **Escalar la Aplicación:** Ajusta el número de replicas en `k8s/app-deployment.yaml`.
4. **Implementar CI/CD:** Configura GitHub Actions para automatizar el despliegue.

## Recursos Adicionales

- [Documentación de Kubernetes](https://kubernetes.io/docs/)
- [Documentación de Terraform](https://www.terraform.io/docs)
- [Documentación de Ansible](https://docs.ansible.com/)
- [Documentación de Prometheus](https://prometheus.io/docs/)
- [Documentación de Grafana](https://grafana.com/docs/)
