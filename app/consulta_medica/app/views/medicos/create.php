<!-- Formulario de Nuevo Médico -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title"><i class="fas fa-user-md"></i> Registrar Nuevo Médico</h2>
        <a href="<?= base_url('medicos') ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Volver</a>
    </div>
    <div class="card-body">
        <form action="<?= base_url('medicos/crear') ?>" method="POST" data-validate>
            <?= csrf_field() ?>

            <div class="form-row">
                <div class="form-group">
                    <label for="numero_colegiatura" class="form-label required">Número de Colegiatura</label>
                    <input type="text" name="numero_colegiatura" id="numero_colegiatura" class="form-control"
                           value="<?= e($old['numero_colegiatura'] ?? '') ?>" required maxlength="20" placeholder="CMP-XXXXX">
                </div>
                <div class="form-group">
                    <label for="especialidad_id" class="form-label required">Especialidad</label>
                    <select name="especialidad_id" id="especialidad_id" class="form-control" required>
                        <option value="">Seleccione...</option>
                        <?php foreach ($especialidades as $esp): ?>
                        <option value="<?= e($esp['id']) ?>" <?= ($old['especialidad_id'] ?? '') == $esp['id'] ? 'selected' : '' ?>><?= e($esp['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="nombres" class="form-label required">Nombres</label>
                    <input type="text" name="nombres" id="nombres" class="form-control"
                           value="<?= e($old['nombres'] ?? '') ?>" required maxlength="100">
                </div>
                <div class="form-group">
                    <label for="apellido_paterno" class="form-label required">Apellido Paterno</label>
                    <input type="text" name="apellido_paterno" id="apellido_paterno" class="form-control"
                           value="<?= e($old['apellido_paterno'] ?? '') ?>" required maxlength="50">
                </div>
                <div class="form-group">
                    <label for="apellido_materno" class="form-label">Apellido Materno</label>
                    <input type="text" name="apellido_materno" id="apellido_materno" class="form-control"
                           value="<?= e($old['apellido_materno'] ?? '') ?>" maxlength="50">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="telefono" class="form-label">Teléfono</label>
                    <input type="tel" name="telefono" id="telefono" class="form-control"
                           value="<?= e($old['telefono'] ?? '') ?>" maxlength="20">
                </div>
                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" id="email" class="form-control"
                           value="<?= e($old['email'] ?? '') ?>" maxlength="100">
                </div>
                <div class="form-group">
                    <label for="horario_atencion" class="form-label">Horario de Atención</label>
                    <input type="text" name="horario_atencion" id="horario_atencion" class="form-control"
                           value="<?= e($old['horario_atencion'] ?? '') ?>" placeholder="Ej: Lun-Vie 8:00-17:00">
                </div>
            </div>

            <div class="card-footer" style="margin: 1.5rem -1.5rem -1.5rem;">
                <div class="d-flex justify-content-between">
                    <a href="<?= base_url('medicos') ?>" class="btn btn-secondary"><i class="fas fa-times"></i> Cancelar</a>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar Médico</button>
                </div>
            </div>
        </form>
    </div>
</div>
