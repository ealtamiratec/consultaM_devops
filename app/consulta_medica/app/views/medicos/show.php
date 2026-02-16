<!-- Detalle de Médico -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <a href="<?= base_url('medicos') ?>" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Volver</a>
    <a href="<?= base_url('medicos/editar/' . $medico['id']) ?>" class="btn btn-primary"><i class="fas fa-edit"></i> Editar</a>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title"><i class="fas fa-user-md"></i> Dr(a). <?= e($medico['nombres'] . ' ' . $medico['apellido_paterno']) ?></h2>
        <span class="badge badge-primary" style="font-size: 1rem;"><?= e($medico['codigo_medico']) ?></span>
    </div>
    <div class="card-body">
        <div class="detail-grid">
            <div class="detail-item">
                <div class="detail-label">Colegiatura</div>
                <div class="detail-value"><?= e($medico['numero_colegiatura']) ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Especialidad</div>
                <div class="detail-value"><?= e($medico['especialidad_nombre']) ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Teléfono</div>
                <div class="detail-value"><?= e($medico['telefono'] ?? '-') ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Email</div>
                <div class="detail-value"><?= e($medico['email'] ?? '-') ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Horario de Atención</div>
                <div class="detail-value"><?= e($medico['horario_atencion'] ?? '-') ?></div>
            </div>
        </div>

        <h3 class="mt-4 mb-3" style="font-size: 1rem; color: var(--primary-color);">Estadísticas</h3>
        <div class="detail-grid">
            <div class="detail-item">
                <div class="detail-label">Total Consultas</div>
                <div class="detail-value" style="font-size: 1.5rem;"><?= $totalConsultas ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Consultas Atendidas</div>
                <div class="detail-value" style="font-size: 1.5rem; color: var(--success-color);"><?= $consultasAtendidas ?></div>
            </div>
        </div>
    </div>
</div>
