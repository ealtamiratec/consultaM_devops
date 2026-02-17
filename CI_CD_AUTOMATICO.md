# CI/CD Automático (GitHub -> Jenkins -> Kubernetes)

Este proyecto queda preparado para que Jenkins detecte cambios en `main` y despliegue automáticamente.

## Flujo configurado

1. Haces `git push origin main`
2. GitHub recibe el cambio
3. Jenkins detecta el nuevo commit (polling SCM cada ~2 minutos)
4. Jenkins ejecuta `Jenkinsfile`
5. Jenkins construye la imagen Docker
6. Jenkins actualiza el deployment en Kubernetes
7. La app queda actualizada

## Requisito clave

El repositorio local debe tener remoto `origin` apuntando a GitHub.

```bash
git remote -v
```

Si no existe, créalo:

```bash
git remote add origin https://github.com/USUARIO/REPO.git
```

## Setup automático de Jenkins

Ejecuta:

```bash
cd /Applications/MAMP/htdocs/consultaM_devops
bash jenkins/setup-jenkins.sh
```

El script:
- Detecta `origin`
- Crea/actualiza el job `consulta-medica-pipeline` como Pipeline SCM
- Configura rama `main`
- Activa trigger automático `pollSCM('H/2 * * * *')`
- Dispara un build inicial de validación

## Opcional: detección inmediata por Webhook

Con Jenkins en `localhost`, GitHub no puede llamar el webhook directamente desde Internet.

Si quieres disparo inmediato (sin esperar polling):
1. Expón Jenkins públicamente (por ejemplo con túnel HTTPS).
2. En GitHub -> Settings -> Webhooks agrega:
   - Payload URL: `https://TU_URL_PUBLICA/github-webhook/`
   - Content type: `application/json`
   - Events: `Just the push event`

Si no usas webhook, el polling ya cubre el flujo automático solicitado.
