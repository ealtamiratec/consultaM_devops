<!-- Formulario de Nueva Consulta -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fas fa-calendar-plus"></i>
            Programar Nueva Consulta
        </h2>
        <a href="<?= base_url('consultas') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
    <div class="card-body">
        <form action="<?= base_url('consultas/crear') ?>" method="POST" data-validate>
            <?= csrf_field() ?>

            <h3 class="mb-3" style="font-size: 1rem; color: var(--primary-color); border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
                <i class="fas fa-user"></i> Datos del Paciente
            </h3>

            <?php if ($paciente): ?>
            <!-- Paciente preseleccionado -->
            <input type="hidden" name="paciente_id" value="<?= e($paciente['id']) ?>">
            <div class="detail-grid mb-4">
                <div class="detail-item">
                    <div class="detail-label">Historia Clínica</div>
                    <div class="detail-value"><?= e($paciente['numero_historia']) ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Paciente</div>
                    <div class="detail-value"><?= e($paciente['nombres'] . ' ' . $paciente['apellido_paterno'] . ' ' . ($paciente['apellido_materno'] ?? '')) ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Documento</div>
                    <div class="detail-value"><?= e($paciente['tipo_documento'] . ': ' . $paciente['numero_documento']) ?></div>
                </div>
            </div>
            <a href="<?= base_url('consultas/crear') ?>" class="btn btn-sm btn-outline-primary mb-4">
                <i class="fas fa-exchange-alt"></i> Cambiar Paciente
            </a>
            <?php else: ?>
            <!-- Búsqueda de paciente -->
            <div class="form-group">
                <label for="paciente_busqueda" class="form-label required">Buscar Paciente</label>
                <div class="autocomplete-container">
                    <input type="text" id="paciente_busqueda" 
                           class="form-control" 
                           placeholder="Escriba nombre, documento o historia clínica..."
                           data-autocomplete="api/pacientes/buscar"
                           autocomplete="off">
                    <input type="hidden" name="paciente_id" id="paciente_id" value="<?= e($old['paciente_id'] ?? '') ?>" required>
                    <div class="autocomplete-results"></div>
                </div>
                <div class="form-text">Ingrese al menos 2 caracteres para buscar</div>
                <?php if (isset($errors['paciente_id'])): ?>
                <div class="invalid-feedback" style="display: block;"><?= e($errors['paciente_id']) ?></div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <h3 class="mb-3 mt-4" style="font-size: 1rem; color: var(--primary-color); border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
                <i class="fas fa-user-md"></i> Médico y Especialidad
            </h3>

            <div class="form-row">
                <div class="form-group">
                    <label for="especialidad_id" class="form-label required">Especialidad</label>
                    <select name="especialidad_id" id="especialidad_id" class="form-control" required>
                        <option value="">Seleccione especialidad...</option>
                        <?php foreach ($especialidades as $esp): ?>
                        <option value="<?= e($esp['id']) ?>" <?= ($old['especialidad_id'] ?? '') == $esp['id'] ? 'selected' : '' ?>>
                            <?= e($esp['nombre']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="medico_id" class="form-label required">Médico</label>
                    <select name="medico_id" id="medico_id" class="form-control <?= isset($errors['medico_id']) ? 'is-invalid' : '' ?>" required>
                        <option value="">Seleccione médico...</option>
                        <?php foreach ($medicos as $med): ?>
                        <option value="<?= e($med['id']) ?>" data-especialidad="<?= e($med['especialidad_id']) ?>" <?= ($old['medico_id'] ?? '') == $med['id'] ? 'selected' : '' ?>>
                            Dr(a). <?= e($med['nombres'] . ' ' . $med['apellido_paterno']) ?> - <?= e($med['especialidad_nombre']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['medico_id'])): ?>
                    <div class="invalid-feedback"><?= e($errors['medico_id']) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <h3 class="mb-3 mt-4" style="font-size: 1rem; color: var(--primary-color); border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
                <i class="fas fa-calendar"></i> Fecha y Hora
            </h3>

            <div class="form-row">
                <div class="form-group">
                    <label for="fecha_consulta" class="form-label required">Fecha de Consulta</label>
                    <input type="date" name="fecha_consulta" id="fecha_consulta" 
                           class="form-control <?= isset($errors['fecha_consulta']) ? 'is-invalid' : '' ?>"
                           value="<?= e($old['fecha_consulta'] ?? date('Y-m-d')) ?>" 
                           min="<?= date('Y-m-d') ?>" required>
                    <?php if (isset($errors['fecha_consulta'])): ?>
                    <div class="invalid-feedback"><?= e($errors['fecha_consulta']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="hora_consulta" class="form-label required">Hora de Consulta</label>
                    <input type="time" name="hora_consulta" id="hora_consulta" 
                           class="form-control <?= isset($errors['hora_consulta']) ? 'is-invalid' : '' ?>"
                           value="<?= e($old['hora_consulta'] ?? '08:00') ?>" required>
                    <?php if (isset($errors['hora_consulta'])): ?>
                    <div class="invalid-feedback"><?= e($errors['hora_consulta']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="tipo_consulta" class="form-label required">Tipo de Consulta</label>
                    <select name="tipo_consulta" id="tipo_consulta" class="form-control" required>
                        <option value="Primera vez" <?= ($old['tipo_consulta'] ?? '') === 'Primera vez' ? 'selected' : '' ?>>Primera vez</option>
                        <option value="Control" <?= ($old['tipo_consulta'] ?? '') === 'Control' ? 'selected' : '' ?>>Control</option>
                        <option value="Emergencia" <?= ($old['tipo_consulta'] ?? '') === 'Emergencia' ? 'selected' : '' ?>>Emergencia</option>
                        <option value="Referencia" <?= ($old['tipo_consulta'] ?? '') === 'Referencia' ? 'selected' : '' ?>>Referencia</option>
                    </select>
                </div>
            </div>

            <h3 class="mb-3 mt-4" style="font-size: 1rem; color: var(--primary-color); border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
                <i class="fas fa-notes-medical"></i> Motivo de Consulta
            </h3>

            <div class="form-group">
                <label for="motivo_consulta" class="form-label required">Motivo de la Consulta</label>
                <textarea name="motivo_consulta" id="motivo_consulta" 
                          class="form-control <?= isset($errors['motivo_consulta']) ? 'is-invalid' : '' ?>" 
                          rows="4" required minlength="10" maxlength="500"
                          placeholder="Describa el motivo principal de la consulta..."><?= e($old['motivo_consulta'] ?? '') ?></textarea>
                <?php if (isset($errors['motivo_consulta'])): ?>
                <div class="invalid-feedback"><?= e($errors['motivo_consulta']) ?></div>
                <?php endif; ?>
                <div class="form-text">Mínimo 10 caracteres</div>
            </div>

            <div class="card-footer" style="margin: 1.5rem -1.5rem -1.5rem;">
                <div class="d-flex justify-content-between">
                    <a href="<?= base_url('consultas') ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Programar Consulta
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
