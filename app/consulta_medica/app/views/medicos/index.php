<!-- Listado de Médicos -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fas fa-user-md"></i>
            Listado de Médicos
        </h2>
        <a href="<?= base_url('medicos/crear') ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Médico
        </a>
    </div>
    <div class="card-body">
        <!-- Búsqueda -->
        <form action="<?= base_url('medicos') ?>" method="GET" class="mb-4">
            <div class="d-flex gap-2">
                <input type="text" name="busqueda" class="form-control" 
                       placeholder="Buscar por nombre, código o colegiatura..."
                       value="<?= e($busqueda) ?>" style="max-width: 400px;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Buscar
                </button>
                <?php if ($busqueda): ?>
                <a href="<?= base_url('medicos') ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Limpiar
                </a>
                <?php endif; ?>
            </div>
        </form>

        <?php if (empty($medicos['data'])): ?>
        <div class="text-center text-muted" style="padding: 2rem;">
            <i class="fas fa-user-md" style="font-size: 3rem; margin-bottom: 1rem;"></i>
            <p>No se encontraron médicos</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Médico</th>
                        <th>Colegiatura</th>
                        <th>Especialidad</th>
                        <th>Teléfono</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($medicos['data'] as $medico): ?>
                    <tr>
                        <td><strong><?= e($medico['codigo_medico']) ?></strong></td>
                        <td>
                            Dr(a). <?= e($medico['nombres'] . ' ' . $medico['apellido_paterno'] . ' ' . ($medico['apellido_materno'] ?? '')) ?>
                        </td>
                        <td><?= e($medico['numero_colegiatura']) ?></td>
                        <td><span class="badge badge-primary"><?= e($medico['especialidad_nombre'] ?? '-') ?></span></td>
                        <td><?= e($medico['telefono'] ?? '-') ?></td>
                        <td>
                            <div class="btn-group">
                                <a href="<?= base_url('medicos/ver/' . $medico['id']) ?>" class="btn btn-sm btn-outline-primary" title="Ver">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="<?= base_url('medicos/editar/' . $medico['id']) ?>" class="btn btn-sm btn-outline-primary" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($medicos['total_pages'] > 1): ?>
        <nav class="pagination">
            <?php if ($medicos['has_prev']): ?>
            <a href="<?= base_url('medicos?page=' . ($medicos['current_page'] - 1)) ?>"><i class="fas fa-chevron-left"></i></a>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $medicos['total_pages']; $i++): ?>
                <?php if ($i === $medicos['current_page']): ?>
                <span class="active"><span><?= $i ?></span></span>
                <?php else: ?>
                <a href="<?= base_url('medicos?page=' . $i) ?>"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            <?php if ($medicos['has_next']): ?>
            <a href="<?= base_url('medicos?page=' . ($medicos['current_page'] + 1)) ?>"><i class="fas fa-chevron-right"></i></a>
            <?php endif; ?>
        </nav>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
