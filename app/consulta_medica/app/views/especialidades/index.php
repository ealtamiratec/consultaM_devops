<!-- Listado de Especialidades -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title"><i class="fas fa-tags"></i> Especialidades Médicas</h2>
        <a href="<?= base_url('especialidades/crear') ?>" class="btn btn-primary"><i class="fas fa-plus"></i> Nueva Especialidad</a>
    </div>
    <div class="card-body">
        <?php if (empty($especialidades)): ?>
        <div class="text-center text-muted" style="padding: 2rem;">
            <i class="fas fa-tags" style="font-size: 3rem; margin-bottom: 1rem;"></i>
            <p>No hay especialidades registradas</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Médicos</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($especialidades as $esp): ?>
                    <tr>
                        <td><strong><?= e($esp['nombre']) ?></strong></td>
                        <td><?= e($esp['descripcion'] ?? '-') ?></td>
                        <td><span class="badge badge-info"><?= $esp['total_medicos'] ?> médicos</span></td>
                        <td>
                            <a href="<?= base_url('especialidades/editar/' . $esp['id']) ?>" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-edit"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>
