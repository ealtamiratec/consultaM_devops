<!-- Listado de Consultas -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fas fa-stethoscope"></i>
            Consultas Médicas
        </h2>
        <a href="<?= base_url('consultas/crear') ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nueva Consulta
        </a>
    </div>
    <div class="card-body">
        <!-- Filtros -->
        <form action="<?= base_url('consultas') ?>" method="GET" class="mb-4">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Desde</label>
                    <input type="date" name="fecha_desde" class="form-control" value="<?= e($filtros['fecha_desde'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Hasta</label>
                    <input type="date" name="fecha_hasta" class="form-control" value="<?= e($filtros['fecha_hasta'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-control">
                        <option value="">Todos</option>
                        <option value="Programada" <?= ($filtros['estado'] ?? '') === 'Programada' ? 'selected' : '' ?>>Programada</option>
                        <option value="En espera" <?= ($filtros['estado'] ?? '') === 'En espera' ? 'selected' : '' ?>>En espera</option>
                        <option value="En atención" <?= ($filtros['estado'] ?? '') === 'En atención' ? 'selected' : '' ?>>En atención</option>
                        <option value="Atendida" <?= ($filtros['estado'] ?? '') === 'Atendida' ? 'selected' : '' ?>>Atendida</option>
                        <option value="Cancelada" <?= ($filtros['estado'] ?? '') === 'Cancelada' ? 'selected' : '' ?>>Cancelada</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Búsqueda</label>
                    <input type="text" name="busqueda" class="form-control" placeholder="Paciente o N° consulta..." value="<?= e($filtros['busqueda'] ?? '') ?>">
                </div>
            </div>
            <div class="d-flex gap-2 mt-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Filtrar
                </button>
                <a href="<?= base_url('consultas') ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Limpiar
                </a>
            </div>
        </form>

        <?php if (empty($consultas['data'])): ?>
        <div class="text-center text-muted" style="padding: 2rem;">
            <i class="fas fa-clipboard-list" style="font-size: 3rem; margin-bottom: 1rem;"></i>
            <p>No se encontraron consultas</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>N° Consulta</th>
                        <th>Fecha/Hora</th>
                        <th>Paciente</th>
                        <th>Médico</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($consultas['data'] as $consulta): ?>
                    <tr>
                        <td><strong><?= e($consulta['numero_consulta']) ?></strong></td>
                        <td>
                            <?= format_date($consulta['fecha_consulta']) ?>
                            <br>
                            <small class="text-muted"><?= e(substr($consulta['hora_consulta'], 0, 5)) ?></small>
                        </td>
                        <td>
                            <div><?= e($consulta['paciente_nombres'] . ' ' . $consulta['paciente_apellido']) ?></div>
                            <small class="text-muted"><?= e($consulta['numero_historia']) ?></small>
                        </td>
                        <td>
                            <div>Dr(a). <?= e($consulta['medico_nombres'] . ' ' . $consulta['medico_apellido']) ?></div>
                            <small class="text-muted"><?= e($consulta['especialidad']) ?></small>
                        </td>
                        <td>
                            <span class="badge badge-secondary"><?= e($consulta['tipo_consulta']) ?></span>
                        </td>
                        <td>
                            <?php
                            $badgeClass = match($consulta['estado']) {
                                'Programada' => 'badge-info',
                                'En espera' => 'badge-warning',
                                'En atención' => 'badge-primary',
                                'Atendida' => 'badge-success',
                                'Cancelada' => 'badge-danger',
                                'No asistió' => 'badge-secondary',
                                default => 'badge-secondary'
                            };
                            ?>
                            <span class="badge <?= $badgeClass ?>"><?= e($consulta['estado']) ?></span>
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="<?= base_url('consultas/ver/' . $consulta['id']) ?>" class="btn btn-sm btn-outline-primary" title="Ver">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php if (!in_array($consulta['estado'], ['Atendida', 'Cancelada', 'No asistió'])): ?>
                                <a href="<?= base_url('consultas/editar/' . $consulta['id']) ?>" class="btn btn-sm btn-success" title="Atender">
                                    <i class="fas fa-stethoscope"></i>
                                </a>
                                <form action="<?= base_url('consultas/cancelar/' . $consulta['id']) ?>" method="POST" style="display: inline;">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-danger" title="Cancelar" data-confirm="¿Está seguro de cancelar esta consulta?">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        <?php if ($consultas['total_pages'] > 1): ?>
        <nav class="pagination">
            <?php if ($consultas['has_prev']): ?>
            <a href="<?= base_url('consultas?page=' . ($consultas['current_page'] - 1)) ?>">
                <i class="fas fa-chevron-left"></i>
            </a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $consultas['total_pages']; $i++): ?>
                <?php if ($i === $consultas['current_page']): ?>
                <span class="active"><span><?= $i ?></span></span>
                <?php else: ?>
                <a href="<?= base_url('consultas?page=' . $i) ?>"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($consultas['has_next']): ?>
            <a href="<?= base_url('consultas?page=' . ($consultas['current_page'] + 1)) ?>">
                <i class="fas fa-chevron-right"></i>
            </a>
            <?php endif; ?>
        </nav>
        <?php endif; ?>

        <div class="text-muted text-center mt-3">
            Mostrando <?= count($consultas['data']) ?> de <?= $consultas['total'] ?> consultas
        </div>
        <?php endif; ?>
    </div>
</div>
