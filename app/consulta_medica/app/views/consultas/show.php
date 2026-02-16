<!-- Detalle de Consulta -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="<?= base_url('consultas') ?>" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
    <div class="btn-group">
        <?php if (!in_array($consulta['estado'], ['Atendida', 'Cancelada', 'No asistió'])): ?>
        <a href="<?= base_url('consultas/editar/' . $consulta['id']) ?>" class="btn btn-success">
            <i class="fas fa-stethoscope"></i> Atender Consulta
        </a>
        <form action="<?= base_url('consultas/atender/' . $consulta['id']) ?>" method="POST" style="display: inline;">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-primary" data-confirm="¿Marcar esta consulta como atendida?">
                <i class="fas fa-check"></i> Marcar Atendida
            </button>
        </form>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fas fa-file-medical"></i>
            Consulta <?= e($consulta['numero_consulta']) ?>
        </h2>
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
        <span class="badge <?= $badgeClass ?>" style="font-size: 1rem;"><?= e($consulta['estado']) ?></span>
    </div>
    <div class="card-body">
        <div class="detail-grid">
            <div class="detail-item">
                <div class="detail-label">Fecha de Consulta</div>
                <div class="detail-value"><?= format_date($consulta['fecha_consulta']) ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Hora</div>
                <div class="detail-value"><?= e(substr($consulta['hora_consulta'], 0, 5)) ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Tipo de Consulta</div>
                <div class="detail-value"><?= e($consulta['tipo_consulta']) ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Registrado por</div>
                <div class="detail-value"><?= e($consulta['registrado_por']) ?></div>
            </div>
        </div>

        <h3 class="mt-4 mb-3" style="font-size: 1rem; color: var(--primary-color); border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
            <i class="fas fa-user"></i> Datos del Paciente
        </h3>

        <div class="detail-grid">
            <div class="detail-item">
                <div class="detail-label">Historia Clínica</div>
                <div class="detail-value">
                    <a href="<?= base_url('pacientes/ver/' . $consulta['paciente_id']) ?>">
                        <?= e($consulta['numero_historia']) ?>
                    </a>
                </div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Paciente</div>
                <div class="detail-value">
                    <?= e($consulta['paciente_nombres'] . ' ' . $consulta['paciente_apellido_paterno'] . ' ' . ($consulta['paciente_apellido_materno'] ?? '')) ?>
                </div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Edad</div>
                <div class="detail-value"><?= $edad ?> años</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Sexo</div>
                <div class="detail-value"><?= $consulta['paciente_sexo'] === 'M' ? 'Masculino' : ($consulta['paciente_sexo'] === 'F' ? 'Femenino' : 'Otro') ?></div>
            </div>
        </div>

        <h3 class="mt-4 mb-3" style="font-size: 1rem; color: var(--primary-color); border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
            <i class="fas fa-user-md"></i> Médico Tratante
        </h3>

        <div class="detail-grid">
            <div class="detail-item">
                <div class="detail-label">Médico</div>
                <div class="detail-value">
                    Dr(a). <?= e($consulta['medico_nombres'] . ' ' . $consulta['medico_apellido_paterno'] . ' ' . ($consulta['medico_apellido_materno'] ?? '')) ?>
                </div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Código</div>
                <div class="detail-value"><?= e($consulta['codigo_medico']) ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Especialidad</div>
                <div class="detail-value"><?= e($consulta['especialidad_nombre']) ?></div>
            </div>
        </div>

        <h3 class="mt-4 mb-3" style="font-size: 1rem; color: var(--primary-color); border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
            <i class="fas fa-notes-medical"></i> Información de la Consulta
        </h3>

        <div class="detail-item mb-3" style="background: var(--light-color); padding: 1rem; border-radius: var(--radius);">
            <div class="detail-label">Motivo de Consulta</div>
            <div class="detail-value"><?= nl2br(e($consulta['motivo_consulta'])) ?></div>
        </div>

        <?php if ($consulta['sintomas']): ?>
        <div class="detail-item mb-3" style="background: var(--light-color); padding: 1rem; border-radius: var(--radius);">
            <div class="detail-label">Síntomas</div>
            <div class="detail-value"><?= nl2br(e($consulta['sintomas'])) ?></div>
        </div>
        <?php endif; ?>

        <?php if ($signosVitales): ?>
        <h3 class="mt-4 mb-3" style="font-size: 1rem; color: var(--primary-color); border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
            <i class="fas fa-heartbeat"></i> Signos Vitales
        </h3>

        <div class="detail-grid">
            <?php if ($signosVitales['presion_sistolica'] && $signosVitales['presion_diastolica']): ?>
            <div class="detail-item">
                <div class="detail-label">Presión Arterial</div>
                <div class="detail-value"><?= e($signosVitales['presion_sistolica']) ?>/<?= e($signosVitales['presion_diastolica']) ?> mmHg</div>
            </div>
            <?php endif; ?>
            <?php if ($signosVitales['frecuencia_cardiaca']): ?>
            <div class="detail-item">
                <div class="detail-label">Frecuencia Cardíaca</div>
                <div class="detail-value"><?= e($signosVitales['frecuencia_cardiaca']) ?> lpm</div>
            </div>
            <?php endif; ?>
            <?php if ($signosVitales['frecuencia_respiratoria']): ?>
            <div class="detail-item">
                <div class="detail-label">Frecuencia Respiratoria</div>
                <div class="detail-value"><?= e($signosVitales['frecuencia_respiratoria']) ?> rpm</div>
            </div>
            <?php endif; ?>
            <?php if ($signosVitales['temperatura']): ?>
            <div class="detail-item">
                <div class="detail-label">Temperatura</div>
                <div class="detail-value"><?= e($signosVitales['temperatura']) ?> °C</div>
            </div>
            <?php endif; ?>
            <?php if ($signosVitales['peso']): ?>
            <div class="detail-item">
                <div class="detail-label">Peso</div>
                <div class="detail-value"><?= e($signosVitales['peso']) ?> kg</div>
            </div>
            <?php endif; ?>
            <?php if ($signosVitales['talla']): ?>
            <div class="detail-item">
                <div class="detail-label">Talla</div>
                <div class="detail-value"><?= e($signosVitales['talla']) ?> cm</div>
            </div>
            <?php endif; ?>
            <?php if ($signosVitales['imc']): ?>
            <div class="detail-item">
                <div class="detail-label">IMC</div>
                <div class="detail-value"><?= e($signosVitales['imc']) ?></div>
            </div>
            <?php endif; ?>
            <?php if ($signosVitales['saturacion_oxigeno']): ?>
            <div class="detail-item">
                <div class="detail-label">Saturación O2</div>
                <div class="detail-value"><?= e($signosVitales['saturacion_oxigeno']) ?>%</div>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if ($consulta['examen_fisico']): ?>
        <h3 class="mt-4 mb-3" style="font-size: 1rem; color: var(--primary-color); border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
            <i class="fas fa-stethoscope"></i> Examen Físico
        </h3>
        <div class="detail-item" style="background: var(--light-color); padding: 1rem; border-radius: var(--radius);">
            <div class="detail-value"><?= nl2br(e($consulta['examen_fisico'])) ?></div>
        </div>
        <?php endif; ?>

        <?php if ($consulta['diagnostico']): ?>
        <h3 class="mt-4 mb-3" style="font-size: 1rem; color: var(--success-color); border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
            <i class="fas fa-diagnoses"></i> Diagnóstico
        </h3>
        <div class="detail-item" style="background: rgba(16, 185, 129, 0.1); padding: 1rem; border-radius: var(--radius);">
            <div class="detail-value"><?= nl2br(e($consulta['diagnostico'])) ?></div>
        </div>
        <?php endif; ?>

        <?php if ($consulta['tratamiento']): ?>
        <h3 class="mt-4 mb-3" style="font-size: 1rem; color: var(--primary-color); border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
            <i class="fas fa-prescription"></i> Tratamiento
        </h3>
        <div class="detail-item" style="background: var(--light-color); padding: 1rem; border-radius: var(--radius);">
            <div class="detail-value"><?= nl2br(e($consulta['tratamiento'])) ?></div>
        </div>
        <?php endif; ?>

        <?php if ($consulta['observaciones']): ?>
        <h3 class="mt-4 mb-3" style="font-size: 1rem; color: var(--secondary-color); border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
            <i class="fas fa-comment-medical"></i> Observaciones
        </h3>
        <div class="detail-item" style="background: var(--light-color); padding: 1rem; border-radius: var(--radius);">
            <div class="detail-value"><?= nl2br(e($consulta['observaciones'])) ?></div>
        </div>
        <?php endif; ?>

        <?php if ($consulta['proxima_cita']): ?>
        <div class="alert alert-info mt-4">
            <i class="fas fa-calendar-alt"></i>
            <strong>Próxima Cita:</strong> <?= format_date($consulta['proxima_cita']) ?>
        </div>
        <?php endif; ?>
    </div>
</div>
