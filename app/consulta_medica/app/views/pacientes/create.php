<!-- Formulario de Nuevo Paciente -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fas fa-user-plus"></i>
            Registrar Nuevo Paciente
        </h2>
        <a href="<?= base_url('pacientes') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
    <div class="card-body">
        <form action="<?= base_url('pacientes/crear') ?>" method="POST" data-validate>
            <?= csrf_field() ?>

            <h3 class="mb-3" style="font-size: 1rem; color: var(--primary-color); border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
                <i class="fas fa-id-card"></i> Datos de Identificación
            </h3>

            <div class="form-row">
                <div class="form-group">
                    <label for="tipo_documento" class="form-label required">Tipo de Documento</label>
                    <select name="tipo_documento" id="tipo_documento" class="form-control <?= isset($errors['tipo_documento']) ? 'is-invalid' : '' ?>" required>
                        <option value="">Seleccione...</option>
                        <option value="DNI" <?= ($old['tipo_documento'] ?? '') === 'DNI' ? 'selected' : '' ?>>DNI</option>
                        <option value="CE" <?= ($old['tipo_documento'] ?? '') === 'CE' ? 'selected' : '' ?>>Carné de Extranjería</option>
                        <option value="Pasaporte" <?= ($old['tipo_documento'] ?? '') === 'Pasaporte' ? 'selected' : '' ?>>Pasaporte</option>
                        <option value="Otro" <?= ($old['tipo_documento'] ?? '') === 'Otro' ? 'selected' : '' ?>>Otro</option>
                    </select>
                    <?php if (isset($errors['tipo_documento'])): ?>
                    <div class="invalid-feedback"><?= e($errors['tipo_documento']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="numero_documento" class="form-label required">Número de Documento</label>
                    <input type="text" name="numero_documento" id="numero_documento" 
                           class="form-control <?= isset($errors['numero_documento']) ? 'is-invalid' : '' ?>"
                           value="<?= e($old['numero_documento'] ?? '') ?>" required maxlength="20">
                    <?php if (isset($errors['numero_documento'])): ?>
                    <div class="invalid-feedback"><?= e($errors['numero_documento']) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <h3 class="mb-3 mt-4" style="font-size: 1rem; color: var(--primary-color); border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
                <i class="fas fa-user"></i> Datos Personales
            </h3>

            <div class="form-row">
                <div class="form-group">
                    <label for="nombres" class="form-label required">Nombres</label>
                    <input type="text" name="nombres" id="nombres" 
                           class="form-control <?= isset($errors['nombres']) ? 'is-invalid' : '' ?>"
                           value="<?= e($old['nombres'] ?? '') ?>" required maxlength="100">
                    <?php if (isset($errors['nombres'])): ?>
                    <div class="invalid-feedback"><?= e($errors['nombres']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="apellido_paterno" class="form-label required">Apellido Paterno</label>
                    <input type="text" name="apellido_paterno" id="apellido_paterno" 
                           class="form-control <?= isset($errors['apellido_paterno']) ? 'is-invalid' : '' ?>"
                           value="<?= e($old['apellido_paterno'] ?? '') ?>" required maxlength="50">
                    <?php if (isset($errors['apellido_paterno'])): ?>
                    <div class="invalid-feedback"><?= e($errors['apellido_paterno']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="apellido_materno" class="form-label">Apellido Materno</label>
                    <input type="text" name="apellido_materno" id="apellido_materno" 
                           class="form-control"
                           value="<?= e($old['apellido_materno'] ?? '') ?>" maxlength="50">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="fecha_nacimiento" class="form-label required">Fecha de Nacimiento</label>
                    <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" 
                           class="form-control <?= isset($errors['fecha_nacimiento']) ? 'is-invalid' : '' ?>"
                           value="<?= e($old['fecha_nacimiento'] ?? '') ?>" required max="<?= date('Y-m-d') ?>">
                    <?php if (isset($errors['fecha_nacimiento'])): ?>
                    <div class="invalid-feedback"><?= e($errors['fecha_nacimiento']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="sexo" class="form-label required">Sexo</label>
                    <select name="sexo" id="sexo" class="form-control <?= isset($errors['sexo']) ? 'is-invalid' : '' ?>" required>
                        <option value="">Seleccione...</option>
                        <option value="M" <?= ($old['sexo'] ?? '') === 'M' ? 'selected' : '' ?>>Masculino</option>
                        <option value="F" <?= ($old['sexo'] ?? '') === 'F' ? 'selected' : '' ?>>Femenino</option>
                        <option value="Otro" <?= ($old['sexo'] ?? '') === 'Otro' ? 'selected' : '' ?>>Otro</option>
                    </select>
                    <?php if (isset($errors['sexo'])): ?>
                    <div class="invalid-feedback"><?= e($errors['sexo']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="estado_civil" class="form-label">Estado Civil</label>
                    <select name="estado_civil" id="estado_civil" class="form-control">
                        <option value="">Seleccione...</option>
                        <option value="Soltero" <?= ($old['estado_civil'] ?? '') === 'Soltero' ? 'selected' : '' ?>>Soltero(a)</option>
                        <option value="Casado" <?= ($old['estado_civil'] ?? '') === 'Casado' ? 'selected' : '' ?>>Casado(a)</option>
                        <option value="Divorciado" <?= ($old['estado_civil'] ?? '') === 'Divorciado' ? 'selected' : '' ?>>Divorciado(a)</option>
                        <option value="Viudo" <?= ($old['estado_civil'] ?? '') === 'Viudo' ? 'selected' : '' ?>>Viudo(a)</option>
                        <option value="Conviviente" <?= ($old['estado_civil'] ?? '') === 'Conviviente' ? 'selected' : '' ?>>Conviviente</option>
                    </select>
                </div>
            </div>

            <h3 class="mb-3 mt-4" style="font-size: 1rem; color: var(--primary-color); border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
                <i class="fas fa-phone"></i> Datos de Contacto
            </h3>

            <div class="form-row">
                <div class="form-group">
                    <label for="direccion" class="form-label">Dirección</label>
                    <input type="text" name="direccion" id="direccion" class="form-control"
                           value="<?= e($old['direccion'] ?? '') ?>" maxlength="255">
                </div>

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
            </div>

            <h3 class="mb-3 mt-4" style="font-size: 1rem; color: var(--primary-color); border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
                <i class="fas fa-heartbeat"></i> Información Médica
            </h3>

            <div class="form-row">
                <div class="form-group">
                    <label for="grupo_sanguineo" class="form-label">Grupo Sanguíneo</label>
                    <select name="grupo_sanguineo" id="grupo_sanguineo" class="form-control">
                        <option value="">Seleccione...</option>
                        <option value="A+" <?= ($old['grupo_sanguineo'] ?? '') === 'A+' ? 'selected' : '' ?>>A+</option>
                        <option value="A-" <?= ($old['grupo_sanguineo'] ?? '') === 'A-' ? 'selected' : '' ?>>A-</option>
                        <option value="B+" <?= ($old['grupo_sanguineo'] ?? '') === 'B+' ? 'selected' : '' ?>>B+</option>
                        <option value="B-" <?= ($old['grupo_sanguineo'] ?? '') === 'B-' ? 'selected' : '' ?>>B-</option>
                        <option value="AB+" <?= ($old['grupo_sanguineo'] ?? '') === 'AB+' ? 'selected' : '' ?>>AB+</option>
                        <option value="AB-" <?= ($old['grupo_sanguineo'] ?? '') === 'AB-' ? 'selected' : '' ?>>AB-</option>
                        <option value="O+" <?= ($old['grupo_sanguineo'] ?? '') === 'O+' ? 'selected' : '' ?>>O+</option>
                        <option value="O-" <?= ($old['grupo_sanguineo'] ?? '') === 'O-' ? 'selected' : '' ?>>O-</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="alergias" class="form-label">Alergias</label>
                <textarea name="alergias" id="alergias" class="form-control" rows="2"
                          placeholder="Describa las alergias conocidas del paciente..."><?= e($old['alergias'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label for="antecedentes" class="form-label">Antecedentes Médicos</label>
                <textarea name="antecedentes" id="antecedentes" class="form-control" rows="3"
                          placeholder="Describa los antecedentes médicos relevantes..."><?= e($old['antecedentes'] ?? '') ?></textarea>
            </div>

            <h3 class="mb-3 mt-4" style="font-size: 1rem; color: var(--primary-color); border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
                <i class="fas fa-phone-alt"></i> Contacto de Emergencia
            </h3>

            <div class="form-row">
                <div class="form-group">
                    <label for="contacto_emergencia" class="form-label">Nombre del Contacto</label>
                    <input type="text" name="contacto_emergencia" id="contacto_emergencia" class="form-control"
                           value="<?= e($old['contacto_emergencia'] ?? '') ?>" maxlength="100">
                </div>

                <div class="form-group">
                    <label for="telefono_emergencia" class="form-label">Teléfono de Emergencia</label>
                    <input type="tel" name="telefono_emergencia" id="telefono_emergencia" class="form-control"
                           value="<?= e($old['telefono_emergencia'] ?? '') ?>" maxlength="20">
                </div>
            </div>

            <div class="card-footer" style="margin: 1.5rem -1.5rem -1.5rem; border-radius: 0 0 var(--radius) var(--radius);">
                <div class="d-flex justify-content-between">
                    <a href="<?= base_url('pacientes') ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Paciente
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
