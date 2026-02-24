# Evidencia PVC - 20260223T160005Z

## PVCs
NAME                  STATUS   VOLUME                                     CAPACITY   ACCESS MODES   STORAGECLASS   VOLUMEATTRIBUTESCLASS   AGE     VOLUMEMODE
docker-registry-pvc   Bound    pvc-a5845259-1b05-4c93-ad58-120fc5b5b754   20Gi       RWO            hostpath       <unset>                 6d17h   Filesystem
jenkins-pvc           Bound    pvc-9353e60f-da50-4c07-a298-08fad87fb344   20Gi       RWO            hostpath       <unset>                 6d17h   Filesystem
mysql-pvc             Bound    pvc-7ba18f0b-4226-4f14-87a6-dfabab116029   10Gi       RWO            hostpath       <unset>                 6d17h   Filesystem

## PVs relacionados
NAME                                       CAPACITY   ACCESS MODES   RECLAIM POLICY   STATUS   CLAIM                                 STORAGECLASS   VOLUMEATTRIBUTESCLASS   REASON   AGE
pvc-7ba18f0b-4226-4f14-87a6-dfabab116029   10Gi       RWO            Delete           Bound    consulta-medica/mysql-pvc             hostpath       <unset>                          6d17h
pvc-9353e60f-da50-4c07-a298-08fad87fb344   20Gi       RWO            Delete           Bound    consulta-medica/jenkins-pvc           hostpath       <unset>                          6d17h
pvc-a5845259-1b05-4c93-ad58-120fc5b5b754   20Gi       RWO            Delete           Bound    consulta-medica/docker-registry-pvc   hostpath       <unset>                          6d17h

## Detalle PVC mysql-pvc
Name:          mysql-pvc
Namespace:     consulta-medica
StorageClass:  hostpath
Status:        Bound
Volume:        pvc-7ba18f0b-4226-4f14-87a6-dfabab116029
Labels:        <none>
Annotations:   pv.kubernetes.io/bind-completed: yes
               pv.kubernetes.io/bound-by-controller: yes
               volume.beta.kubernetes.io/storage-provisioner: docker.io/hostpath
               volume.kubernetes.io/storage-provisioner: docker.io/hostpath
Finalizers:    [kubernetes.io/pvc-protection]
Capacity:      10Gi
Access Modes:  RWO
VolumeMode:    Filesystem
Used By:       mysql-7cbddc954f-xp4fb
Events:        <none>

## Detalle PVC jenkins-pvc
Name:          jenkins-pvc
Namespace:     consulta-medica
StorageClass:  hostpath
Status:        Bound
Volume:        pvc-9353e60f-da50-4c07-a298-08fad87fb344
Labels:        <none>
Annotations:   pv.kubernetes.io/bind-completed: yes
               pv.kubernetes.io/bound-by-controller: yes
               volume.beta.kubernetes.io/storage-provisioner: docker.io/hostpath
               volume.kubernetes.io/storage-provisioner: docker.io/hostpath
Finalizers:    [kubernetes.io/pvc-protection]
Capacity:      20Gi
Access Modes:  RWO
VolumeMode:    Filesystem
Used By:       jenkins-5bdcb97494-mv4tt
Events:        <none>

## Detalle PVC docker-registry-pvc
Name:          docker-registry-pvc
Namespace:     consulta-medica
StorageClass:  hostpath
Status:        Bound
Volume:        pvc-a5845259-1b05-4c93-ad58-120fc5b5b754
Labels:        <none>
Annotations:   pv.kubernetes.io/bind-completed: yes
               pv.kubernetes.io/bound-by-controller: yes
               volume.beta.kubernetes.io/storage-provisioner: docker.io/hostpath
               volume.kubernetes.io/storage-provisioner: docker.io/hostpath
Finalizers:    [kubernetes.io/pvc-protection]
Capacity:      20Gi
Access Modes:  RWO
VolumeMode:    Filesystem
Used By:       docker-registry-769b45886b-vvmdk
Events:        <none>
