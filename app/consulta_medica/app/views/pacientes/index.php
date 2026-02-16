<!-- Listado de Pacientes -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fas fa-users"></i>
            Listado de Pacientes
        </h2>
        <a href="<?= base_url('pacientes/crear') ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Paciente
        </a>
    </div>
    <div class="card-body">
        <!-- Búsqueda -->
        <form action="<?= base_url('pacientes') ?>" method="GET" class="mb-4">
            <div class="d-flex gap-2">
                <input 
                    type="text" 
                    name="busqueda" 
                    class="form-control" 
                    placeholder="Buscar por nombre, documento o historia clínica..."
                    value="<?= e($busqueda) ?>"
                    style="max-width: 400px;"
                >
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Buscar
                </button>
                <?php if ($busqueda): ?>
                <a href="<?= base_url('pacientes') ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Limpiar
                </a>
                <?php endif; ?>
            </div>
        </form>

        <?php if (empty($pacientes['data'])): ?>
        <div class="text-center text-muted" style="padding: 2rem;">
            <i class="fas fa-users-slash" style="font-size: 3rem; margin-bottom: 1rem;"></i>
            <p>No se encontraron pacientes</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Historia</th>
                        <th>Paciente</th>
                        <th>Documento</th>
                        <th>Fecha Nac.</th>
                        <th>Teléfono</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pacientes['data'] as $paciente): ?>
                    <tr>
                        <td>
                            <strong><?= e($paciente['numero_historia']) ?></strong>
                        </td>
                        <td>
                            <div><?= e($paciente['nombres'] . ' ' . $paciente['apellido_paterno'] . ' ' . ($paciente['apellido_materno'] ?? '')) ?></div>
                            <small class="text-muted">
                                <?= $paciente['sexo'] === 'M' ? 'Masculino' : ($paciente['sexo'] === 'F' ? 'Femenino' : 'Otro') ?>
                            </small>
                        </td>
                        <td><?= e($paciente['tipo_documento'] . ': ' . $paciente['numero_documento']) ?></td>
                        <td><?= format_date($paciente['fecha_nacimiento']) ?></td>
                        <td><?= e($paciente['telefono'] ?? '-') ?></td>
                        <td>
                            <div class="btn-group">
                                <a href="<?= base_url('pacientes/ver/' . $paciente['id']) ?>" class="btn btn-sm btn-outline-primary" title="Ver">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="<?= base_url('pacientes/editar/' . $paciente['id']) ?>" class="btn btn-sm btn-outline-primary" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="<?= base_url('consultas/crear?paciente_id=' . $paciente['id']) ?>" class="btn btn-sm btn-success" title="Nueva Consulta">
                                    <i class="fas fa-calendar-plus"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        <?php if ($pacientes['total_pages'] > 1): ?>
        <nav class="pagination">
            <?php if ($pacientes['has_prev']): ?>
            <a href="<?= base_url('pacientes?page=' . ($pacientes['current_page'] - 1) . ($busqueda ? '&busqueda=' . urlencode($busqueda) : '')) ?>">
                <i class="fas fa-chevron-left"></i>
            </a>
            <?php else: ?>
            <span class="disabled"><i class="fas fa-chevron-left"></i></span>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $pacientes['total_pages']; $i++): ?>
                <?php if ($i === $pacientes['current_page']): ?>
                <span class="active"><span><?= $i ?></span></span>
                <?php else: ?>
                <a href="<?= base_url('pacientes?page=' . $i . ($busqueda ? '&busqueda=' . urlencode($busqueda) : '')) ?>"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($pacientes['has_next']): ?>
            <a href="<?= base_url('pacientes?page=' . ($pacientes['current_page'] + 1) . ($busqueda ? '&busqueda=' . urlencode($busqueda) : '')) ?>">
                <i class="fas fa-chevron-right"></i>
            </a>
            <?php else: ?>
            <span class="disabled"><i class="fas fa-chevron-right"></i></span>
            <?php endif; ?>
        </nav>
        <?php endif; ?>

        <div class="text-muted text-center mt-3">
            Mostrando <?= count($pacientes['data']) ?> de <?= $pacientes['total'] ?> pacientes
        </div>
        <?php endif; ?>
    </div>
</div>
