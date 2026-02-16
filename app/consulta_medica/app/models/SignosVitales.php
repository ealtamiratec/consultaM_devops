<?php
/**
 * Modelo SignosVitales
 * Sistema de Atención Médica - Consulta Externa
 */

namespace App\Models;

use Core\Model;

class SignosVitales extends Model
{
    protected string $table = 'signos_vitales';
    
    protected array $fillable = [
        'consulta_id',
        'presion_sistolica',
        'presion_diastolica',
        'frecuencia_cardiaca',
        'frecuencia_respiratoria',
        'temperatura',
        'peso',
        'talla',
        'imc',
        'saturacion_oxigeno'
    ];

    /**
     * Obtener signos vitales de una consulta
     */
    public function getByConsulta(int $consultaId): ?array
    {
        return $this->findBy('consulta_id', $consultaId);
    }

    /**
     * Calcular IMC
     */
    public function calcularIMC(float $peso, float $talla): float
    {
        if ($talla <= 0) {
            return 0;
        }
        // Talla en metros
        $tallaMetros = $talla / 100;
        return round($peso / ($tallaMetros * $tallaMetros), 2);
    }

    /**
     * Guardar signos vitales con cálculo de IMC
     */
    public function guardarSignos(array $data): int
    {
        // Calcular IMC si hay peso y talla
        if (!empty($data['peso']) && !empty($data['talla'])) {
            $data['imc'] = $this->calcularIMC((float) $data['peso'], (float) $data['talla']);
        }

        return $this->create($data);
    }

    /**
     * Actualizar signos vitales
     */
    public function actualizarSignos(int $consultaId, array $data): bool
    {
        // Calcular IMC si hay peso y talla
        if (!empty($data['peso']) && !empty($data['talla'])) {
            $data['imc'] = $this->calcularIMC((float) $data['peso'], (float) $data['talla']);
        }

        $existing = $this->getByConsulta($consultaId);
        
        if ($existing) {
            return $this->update($existing['id'], $data);
        } else {
            $data['consulta_id'] = $consultaId;
            return $this->create($data) > 0;
        }
    }

    /**
     * Interpretar presión arterial
     */
    public function interpretarPresion(int $sistolica, int $diastolica): string
    {
        if ($sistolica < 90 || $diastolica < 60) {
            return 'Hipotensión';
        } elseif ($sistolica < 120 && $diastolica < 80) {
            return 'Normal';
        } elseif ($sistolica < 130 && $diastolica < 80) {
            return 'Elevada';
        } elseif ($sistolica < 140 || $diastolica < 90) {
            return 'Hipertensión Grado 1';
        } elseif ($sistolica < 180 || $diastolica < 120) {
            return 'Hipertensión Grado 2';
        } else {
            return 'Crisis Hipertensiva';
        }
    }

    /**
     * Interpretar IMC
     */
    public function interpretarIMC(float $imc): string
    {
        if ($imc < 18.5) {
            return 'Bajo peso';
        } elseif ($imc < 25) {
            return 'Normal';
        } elseif ($imc < 30) {
            return 'Sobrepeso';
        } elseif ($imc < 35) {
            return 'Obesidad Grado I';
        } elseif ($imc < 40) {
            return 'Obesidad Grado II';
        } else {
            return 'Obesidad Grado III';
        }
    }
}
