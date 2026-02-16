<!-- Detalle de Paciente -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="<?= base_url('pacientes') ?>" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
    <div class="btn-group">
        <a href="<?= base_url('pacientes/editar/' . $paciente['id']) ?>" class="btn btn-primary">
            <i class="fas fa-edit"></i> Editar
        </a>
        <a href="<?= base_url('consultas/crear?paciente_id=' . $paciente['id']) ?>" class="btn btn-success">
            <i class="fas fa-calendar-plus"></i> Nueva Consulta
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fas fa-user"></i>
            <?= e($paciente['nombres'] . ' ' . $paciente['apellido_paterno'] . ' ' . ($paciente['apellido_materno'] ?? '')) ?>
        </h2>
        <span class="badge badge-primary" style="font-size: 1rem;">
            <?= e($paciente['numero_historia']) ?>
        </span>
    </div>
    <div class="card-body">
        <div class="detail-grid">
            <div class="detail-item">
                <div class="detail-label">Documento</div>
                <div class="detail-value"><?= e($paciente['tipo_documento'] . ': ' . $paciente['numero_documento']) ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Fecha de Nacimiento</div>
                <div class="detail-value"><?= format_date($paciente['fecha_nacimiento']) ?> (<?= $edad ?> años)</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Sexo</div>
                <div class="detail-value"><?= $paciente['sexo'] === 'M' ? 'Masculino' : ($paciente['sexo'] === 'F' ? 'Femenino' : 'Otro') ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Estado Civil</div>
                <div class="detail-value"><?= e($paciente['estado_civil'] ?? '-') ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Teléfono</div>
                <div class="detail-value"><?= e($paciente['telefono'] ?? '-') ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Email</div>
                <div class="detail-value"><?= e($paciente['email'] ?? '-') ?></div>
            </div>
            <div class="detail-item" style="grid-column: span 2;">
                <div class="detail-label">Dirección</div>
                <div class="detail-value"><?= e($paciente['direccion'] ?? '-') ?></div>
            </div>
        </div>

        <h3 class="mt-4 mb-3" style="font-size: 1rem; color: var(--primary-color); border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
            <i class="fas fa-heartbeat"></i> Información Médica
        </h3>

        <div class="detail-grid">
            <div class="detail-item">
                <div class="detail-label">Grupo Sanguíneo</div>
                <div class="detail-value"><?= e($paciente['grupo_sanguineo'] ?? '-') ?></div>
            </div>
            <div class="detail-item" style="grid-column: span 2;">
                <div class="detail-label">Alergias</div>
                <div class="detail-value"><?= nl2br(e($paciente['alergias'] ?? 'No registradas')) ?></div>
            </div>
            <div class="detail-item" style="grid-column: span 3;">
                <div class="detail-label">Antecedentes Médicos</div>
                <div class="detail-value"><?= nl2br(e($paciente['antecedentes'] ?? 'No registrados')) ?></div>
            </div>
        </div>

        <h3 class="mt-4 mb-3" style="font-size: 1rem; color: var(--primary-color); border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
            <i class="fas fa-phone-alt"></i> Contacto de Emergencia
        </h3>

        <div class="detail-grid">
            <div class="detail-item">
                <div class="detail-label">Nombre</div>
                <div class="detail-value"><?= e($paciente['contacto_emergencia'] ?? '-') ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Teléfono</div>
                <div class="detail-value"><?= e($paciente['telefono_emergencia'] ?? '-') ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Historial de Consultas -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fas fa-history"></i>
            Historial de Consultas
        </h2>
    </div>
    <div class="card-body">
        <?php if (empty($historial)): ?>
        <div class="text-center text-muted" style="padding: 2rem;">
            <i class="fas fa-clipboard-list" style="font-size: 3rem; margin-bottom: 1rem;"></i>
            <p>El paciente no tiene consultas registradas</p>
            <a href="<?= base_url('consultas/crear?paciente_id=' . $paciente['id']) ?>" class="btn btn-primary mt-3">
                <i class="fas fa-plus"></i> Registrar Primera Consulta
            </a>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Médico</th>
                        <th>Especialidad</th>
                        <th>Motivo</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($historial as $consulta): ?>
                    <tr>
                        <td>
                            <strong><?= format_date($consulta['fecha_consulta']) ?></strong>
                            <br>
                            <small class="text-muted"><?= e(substr($consulta['hora_consulta'], 0, 5)) ?></small>
                        </td>
                        <td>Dr(a). <?= e($consulta['medico_nombres'] . ' ' . $consulta['medico_apellido']) ?></td>
                        <td><?= e($consulta['especialidad']) ?></td>
                        <td style="max-width: 200px;">
                            <?= e(substr($consulta['motivo_consulta'], 0, 50)) ?><?= strlen($consulta['motivo_consulta']) > 50 ? '...' : '' ?>
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
                            <a href="<?= base_url('consultas/ver/' . $consulta['id']) ?>" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i>
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
