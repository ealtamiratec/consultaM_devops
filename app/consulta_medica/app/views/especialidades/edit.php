<!-- Formulario de Editar Especialidad -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title"><i class="fas fa-edit"></i> Editar Especialidad</h2>
        <a href="<?= base_url('especialidades') ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Volver</a>
    </div>
    <div class="card-body">
        <form action="<?= base_url('especialidades/editar/' . $especialidad['id']) ?>" method="POST" data-validate>
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="nombre" class="form-label required">Nombre</label>
                <input type="text" name="nombre" id="nombre" class="form-control" value="<?= e($especialidad['nombre']) ?>" required maxlength="100">
            </div>
            <div class="form-group">
                <label for="descripcion" class="form-label">Descripción</label>
                <textarea name="descripcion" id="descripcion" class="form-control" rows="3"><?= e($especialidad['descripcion'] ?? '') ?></textarea>
            </div>
            <div class="card-footer" style="margin: 1.5rem -1.5rem -1.5rem;">
                <div class="d-flex justify-content-between">
                    <a href="<?= base_url('especialidades') ?>" class="btn btn-secondary"><i class="fas fa-times"></i> Cancelar</a>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar Cambios</button>
                </div>
            </div>
        </form>
    </div>
</div>
