<!-- Formulario de Editar Paciente -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fas fa-user-edit"></i>
            Editar Paciente: <?= e($paciente['numero_historia']) ?>
        </h2>
        <a href="<?= base_url('pacientes/ver/' . $paciente['id']) ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
    <div class="card-body">
        <form action="<?= base_url('pacientes/editar/' . $paciente['id']) ?>" method="POST" data-validate>
            <?= csrf_field() ?>

            <h3 class="mb-3" style="font-size: 1rem; color: var(--primary-color); border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
                <i class="fas fa-id-card"></i> Datos de Identificación
            </h3>

            <div class="form-row">
                <div class="form-group">
                    <label for="numero_historia" class="form-label">Número de Historia</label>
                    <input type="text" class="form-control" value="<?= e($paciente['numero_historia']) ?>" disabled>
                </div>

                <div class="form-group">
                    <label for="tipo_documento" class="form-label required">Tipo de Documento</label>
                    <select name="tipo_documento" id="tipo_documento" class="form-control <?= isset($errors['tipo_documento']) ? 'is-invalid' : '' ?>" required>
                        <option value="DNI" <?= $paciente['tipo_documento'] === 'DNI' ? 'selected' : '' ?>>DNI</option>
                        <option value="CE" <?= $paciente['tipo_documento'] === 'CE' ? 'selected' : '' ?>>Carné de Extranjería</option>
                        <option value="Pasaporte" <?= $paciente['tipo_documento'] === 'Pasaporte' ? 'selected' : '' ?>>Pasaporte</option>
                        <option value="Otro" <?= $paciente['tipo_documento'] === 'Otro' ? 'selected' : '' ?>>Otro</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="numero_documento" class="form-label required">Número de Documento</label>
                    <input type="text" name="numero_documento" id="numero_documento" 
                           class="form-control <?= isset($errors['numero_documento']) ? 'is-invalid' : '' ?>"
                           value="<?= e($paciente['numero_documento']) ?>" required maxlength="20">
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
                           value="<?= e($paciente['nombres']) ?>" required maxlength="100">
                </div>

                <div class="form-group">
                    <label for="apellido_paterno" class="form-label required">Apellido Paterno</label>
                    <input type="text" name="apellido_paterno" id="apellido_paterno" 
                           class="form-control <?= isset($errors['apellido_paterno']) ? 'is-invalid' : '' ?>"
                           value="<?= e($paciente['apellido_paterno']) ?>" required maxlength="50">
                </div>

                <div class="form-group">
                    <label for="apellido_materno" class="form-label">Apellido Materno</label>
                    <input type="text" name="apellido_materno" id="apellido_materno" 
                           class="form-control"
                           value="<?= e($paciente['apellido_materno'] ?? '') ?>" maxlength="50">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="fecha_nacimiento" class="form-label required">Fecha de Nacimiento</label>
                    <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" 
                           class="form-control <?= isset($errors['fecha_nacimiento']) ? 'is-invalid' : '' ?>"
                           value="<?= e($paciente['fecha_nacimiento']) ?>" required max="<?= date('Y-m-d') ?>">
                </div>

                <div class="form-group">
                    <label for="sexo" class="form-label required">Sexo</label>
                    <select name="sexo" id="sexo" class="form-control" required>
                        <option value="M" <?= $paciente['sexo'] === 'M' ? 'selected' : '' ?>>Masculino</option>
                        <option value="F" <?= $paciente['sexo'] === 'F' ? 'selected' : '' ?>>Femenino</option>
                        <option value="Otro" <?= $paciente['sexo'] === 'Otro' ? 'selected' : '' ?>>Otro</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="estado_civil" class="form-label">Estado Civil</label>
                    <select name="estado_civil" id="estado_civil" class="form-control">
                        <option value="">Seleccione...</option>
                        <option value="Soltero" <?= ($paciente['estado_civil'] ?? '') === 'Soltero' ? 'selected' : '' ?>>Soltero(a)</option>
                        <option value="Casado" <?= ($paciente['estado_civil'] ?? '') === 'Casado' ? 'selected' : '' ?>>Casado(a)</option>
                        <option value="Divorciado" <?= ($paciente['estado_civil'] ?? '') === 'Divorciado' ? 'selected' : '' ?>>Divorciado(a)</option>
                        <option value="Viudo" <?= ($paciente['estado_civil'] ?? '') === 'Viudo' ? 'selected' : '' ?>>Viudo(a)</option>
                        <option value="Conviviente" <?= ($paciente['estado_civil'] ?? '') === 'Conviviente' ? 'selected' : '' ?>>Conviviente</option>
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
                           value="<?= e($paciente['direccion'] ?? '') ?>" maxlength="255">
                </div>

                <div class="form-group">
                    <label for="telefono" class="form-label">Teléfono</label>
                    <input type="tel" name="telefono" id="telefono" class="form-control"
                           value="<?= e($paciente['telefono'] ?? '') ?>" maxlength="20">
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" id="email" class="form-control"
                           value="<?= e($paciente['email'] ?? '') ?>" maxlength="100">
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
                        <?php foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $gs): ?>
                        <option value="<?= $gs ?>" <?= ($paciente['grupo_sanguineo'] ?? '') === $gs ? 'selected' : '' ?>><?= $gs ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="alergias" class="form-label">Alergias</label>
                <textarea name="alergias" id="alergias" class="form-control" rows="2"><?= e($paciente['alergias'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label for="antecedentes" class="form-label">Antecedentes Médicos</label>
                <textarea name="antecedentes" id="antecedentes" class="form-control" rows="3"><?= e($paciente['antecedentes'] ?? '') ?></textarea>
            </div>

            <h3 class="mb-3 mt-4" style="font-size: 1rem; color: var(--primary-color); border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
                <i class="fas fa-phone-alt"></i> Contacto de Emergencia
            </h3>

            <div class="form-row">
                <div class="form-group">
                    <label for="contacto_emergencia" class="form-label">Nombre del Contacto</label>
                    <input type="text" name="contacto_emergencia" id="contacto_emergencia" class="form-control"
                           value="<?= e($paciente['contacto_emergencia'] ?? '') ?>" maxlength="100">
                </div>

                <div class="form-group">
                    <label for="telefono_emergencia" class="form-label">Teléfono de Emergencia</label>
                    <input type="tel" name="telefono_emergencia" id="telefono_emergencia" class="form-control"
                           value="<?= e($paciente['telefono_emergencia'] ?? '') ?>" maxlength="20">
                </div>
            </div>

            <div class="card-footer" style="margin: 1.5rem -1.5rem -1.5rem;">
                <div class="d-flex justify-content-between">
                    <a href="<?= base_url('pacientes/ver/' . $paciente['id']) ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
