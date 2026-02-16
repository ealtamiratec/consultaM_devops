<!-- Dashboard Principal -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon primary">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-info">
            <h3><?= number_format($stats['total_pacientes']) ?></h3>
            <p>Pacientes Registrados</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon success">
            <i class="fas fa-user-md"></i>
        </div>
        <div class="stat-info">
            <h3><?= number_format($stats['total_medicos']) ?></h3>
            <p>Médicos Activos</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon warning">
            <i class="fas fa-calendar-check"></i>
        </div>
        <div class="stat-info">
            <h3><?= number_format($stats['consultas_hoy']) ?></h3>
            <p>Consultas Hoy</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon info">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-info">
            <h3><?= number_format($stats['consultas_pendientes']) ?></h3>
            <p>Consultas Pendientes</p>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fas fa-calendar-day"></i>
            Consultas de Hoy - <?= date('d/m/Y') ?>
        </h2>
        <a href="<?= base_url('consultas/crear') ?>" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Nueva Consulta
        </a>
    </div>
    <div class="card-body">
        <?php if (empty($consultasHoy)): ?>
        <div class="text-center text-muted" style="padding: 2rem;">
            <i class="fas fa-calendar-times" style="font-size: 3rem; margin-bottom: 1rem;"></i>
            <p>No hay consultas programadas para hoy</p>
            <a href="<?= base_url('consultas/crear') ?>" class="btn btn-primary mt-3">
                <i class="fas fa-plus"></i> Programar Consulta
            </a>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Hora</th>
                        <th>Paciente</th>
                        <th>Médico</th>
                        <th>Especialidad</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($consultasHoy as $consulta): ?>
                    <tr>
                        <td>
                            <strong><?= e(substr($consulta['hora_consulta'], 0, 5)) ?></strong>
                        </td>
                        <td>
                            <div><?= e($consulta['paciente_nombres'] . ' ' . $consulta['paciente_apellido']) ?></div>
                            <small class="text-muted"><?= e($consulta['numero_historia']) ?></small>
                        </td>
                        <td>
                            Dr(a). <?= e($consulta['medico_nombres'] . ' ' . $consulta['medico_apellido']) ?>
                        </td>
                        <td><?= e($consulta['especialidad']) ?></td>
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
                                <?php if ($consulta['estado'] !== 'Atendida' && $consulta['estado'] !== 'Cancelada'): ?>
                                <a href="<?= base_url('consultas/editar/' . $consulta['id']) ?>" class="btn btn-sm btn-success" title="Atender">
                                    <i class="fas fa-stethoscope"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fas fa-chart-bar"></i>
            Resumen del Mes
        </h2>
    </div>
    <div class="card-body">
        <div class="detail-grid">
            <div class="detail-item">
                <div class="detail-label">Total Consultas del Mes</div>
                <div class="detail-value" style="font-size: 1.5rem; color: var(--primary-color);">
                    <?= number_format($estadisticasConsultas['mes']) ?>
                </div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Consultas Atendidas Hoy</div>
                <div class="detail-value" style="font-size: 1.5rem; color: var(--success-color);">
                    <?= number_format($estadisticasConsultas['atendidas_hoy']) ?>
                </div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Total Histórico</div>
                <div class="detail-value" style="font-size: 1.5rem; color: var(--info-color);">
                    <?= number_format($estadisticasConsultas['total']) ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Accesos Rápidos -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fas fa-bolt"></i>
            Accesos Rápidos
        </h2>
    </div>
    <div class="card-body">
        <div class="d-flex gap-3" style="flex-wrap: wrap;">
            <a href="<?= base_url('pacientes/crear') ?>" class="btn btn-outline-primary">
                <i class="fas fa-user-plus"></i> Nuevo Paciente
            </a>
            <a href="<?= base_url('consultas/crear') ?>" class="btn btn-outline-primary">
                <i class="fas fa-calendar-plus"></i> Nueva Consulta
            </a>
            <a href="<?= base_url('medicos/crear') ?>" class="btn btn-outline-primary">
                <i class="fas fa-user-md"></i> Nuevo Médico
            </a>
            <a href="<?= base_url('pacientes') ?>" class="btn btn-outline-primary">
                <i class="fas fa-search"></i> Buscar Paciente
            </a>
        </div>
    </div>
</div>
