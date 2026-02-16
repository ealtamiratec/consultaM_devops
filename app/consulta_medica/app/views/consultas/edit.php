<!-- Formulario de Atención de Consulta -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fas fa-stethoscope"></i>
            Atender Consulta: <?= e($consulta['numero_consulta']) ?>
        </h2>
        <a href="<?= base_url('consultas/ver/' . $consulta['id']) ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
    <div class="card-body">
        <!-- Información del Paciente -->
        <div class="alert alert-info">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <strong><i class="fas fa-user"></i> Paciente:</strong> 
                    <?= e($consulta['paciente_nombres'] . ' ' . $consulta['paciente_apellido_paterno']) ?>
                    <span class="badge badge-primary"><?= e($consulta['numero_historia']) ?></span>
                    <br>
                    <small>Edad: <?= $edad ?> años | Sexo: <?= $consulta['paciente_sexo'] === 'M' ? 'Masculino' : 'Femenino' ?></small>
                </div>
                <div>
                    <strong>Fecha:</strong> <?= format_date($consulta['fecha_consulta']) ?> <?= e(substr($consulta['hora_consulta'], 0, 5)) ?>
                </div>
            </div>
        </div>

        <div class="detail-item mb-4" style="background: var(--light-color); padding: 1rem; border-radius: var(--radius);">
            <div class="detail-label">Motivo de Consulta</div>
            <div class="detail-value"><?= nl2br(e($consulta['motivo_consulta'])) ?></div>
        </div>

        <form action="<?= base_url('consultas/editar/' . $consulta['id']) ?>" method="POST" data-validate>
            <?= csrf_field() ?>

            <h3 class="mb-3" style="font-size: 1rem; color: var(--primary-color); border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
                <i class="fas fa-heartbeat"></i> Signos Vitales
            </h3>

            <div class="form-row">
                <div class="form-group">
                    <label for="presion_sistolica" class="form-label">Presión Sistólica (mmHg)</label>
                    <input type="number" name="presion_sistolica" id="presion_sistolica" class="form-control"
                           value="<?= e($signosVitales['presion_sistolica'] ?? '') ?>" min="60" max="250">
                </div>
                <div class="form-group">
                    <label for="presion_diastolica" class="form-label">Presión Diastólica (mmHg)</label>
                    <input type="number" name="presion_diastolica" id="presion_diastolica" class="form-control"
                           value="<?= e($signosVitales['presion_diastolica'] ?? '') ?>" min="40" max="150">
                </div>
                <div class="form-group">
                    <label for="frecuencia_cardiaca" class="form-label">Frec. Cardíaca (lpm)</label>
                    <input type="number" name="frecuencia_cardiaca" id="frecuencia_cardiaca" class="form-control"
                           value="<?= e($signosVitales['frecuencia_cardiaca'] ?? '') ?>" min="30" max="200">
                </div>
                <div class="form-group">
                    <label for="frecuencia_respiratoria" class="form-label">Frec. Respiratoria (rpm)</label>
                    <input type="number" name="frecuencia_respiratoria" id="frecuencia_respiratoria" class="form-control"
                           value="<?= e($signosVitales['frecuencia_respiratoria'] ?? '') ?>" min="8" max="40">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="temperatura" class="form-label">Temperatura (°C)</label>
                    <input type="number" name="temperatura" id="temperatura" class="form-control"
                           value="<?= e($signosVitales['temperatura'] ?? '') ?>" min="34" max="42" step="0.1">
                </div>
                <div class="form-group">
                    <label for="peso" class="form-label">Peso (kg)</label>
                    <input type="number" name="peso" id="peso" class="form-control"
                           value="<?= e($signosVitales['peso'] ?? '') ?>" min="1" max="300" step="0.1">
                </div>
                <div class="form-group">
                    <label for="talla" class="form-label">Talla (cm)</label>
                    <input type="number" name="talla" id="talla" class="form-control"
                           value="<?= e($signosVitales['talla'] ?? '') ?>" min="30" max="250">
                </div>
                <div class="form-group">
                    <label for="imc" class="form-label">IMC (calculado)</label>
                    <input type="text" name="imc" id="imc" class="form-control" 
                           value="<?= e($signosVitales['imc'] ?? '') ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="saturacion_oxigeno" class="form-label">Saturación O2 (%)</label>
                    <input type="number" name="saturacion_oxigeno" id="saturacion_oxigeno" class="form-control"
                           value="<?= e($signosVitales['saturacion_oxigeno'] ?? '') ?>" min="70" max="100">
                </div>
            </div>

            <h3 class="mb-3 mt-4" style="font-size: 1rem; color: var(--primary-color); border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
                <i class="fas fa-notes-medical"></i> Evaluación Médica
            </h3>

            <div class="form-group">
                <label for="sintomas" class="form-label">Síntomas</label>
                <textarea name="sintomas" id="sintomas" class="form-control" rows="3"
                          placeholder="Describa los síntomas referidos por el paciente..."><?= e($consulta['sintomas'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label for="examen_fisico" class="form-label">Examen Físico</label>
                <textarea name="examen_fisico" id="examen_fisico" class="form-control" rows="4"
                          placeholder="Hallazgos del examen físico..."><?= e($consulta['examen_fisico'] ?? '') ?></textarea>
            </div>

            <h3 class="mb-3 mt-4" style="font-size: 1rem; color: var(--success-color); border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
                <i class="fas fa-diagnoses"></i> Diagnóstico y Tratamiento
            </h3>

            <div class="form-group">
                <label for="diagnostico" class="form-label">Diagnóstico</label>
                <textarea name="diagnostico" id="diagnostico" class="form-control" rows="3"
                          placeholder="Diagnóstico principal y secundarios..."><?= e($consulta['diagnostico'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label for="tratamiento" class="form-label">Tratamiento / Prescripción</label>
                <textarea name="tratamiento" id="tratamiento" class="form-control" rows="4"
                          placeholder="Medicamentos, dosis, indicaciones..."><?= e($consulta['tratamiento'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label for="observaciones" class="form-label">Observaciones</label>
                <textarea name="observaciones" id="observaciones" class="form-control" rows="2"
                          placeholder="Observaciones adicionales..."><?= e($consulta['observaciones'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label for="proxima_cita" class="form-label">Próxima Cita</label>
                <input type="date" name="proxima_cita" id="proxima_cita" class="form-control"
                       value="<?= e($consulta['proxima_cita'] ?? '') ?>" min="<?= date('Y-m-d') ?>" style="max-width: 200px;">
            </div>

            <div class="card-footer" style="margin: 1.5rem -1.5rem -1.5rem;">
                <div class="d-flex justify-content-between">
                    <a href="<?= base_url('consultas/ver/' . $consulta['id']) ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <div class="btn-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar
                        </button>
                        <button type="submit" name="finalizar" value="1" class="btn btn-success" formaction="<?= base_url('consultas/editar/' . $consulta['id']) ?>">
                            <i class="fas fa-check"></i> Guardar y Finalizar
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
