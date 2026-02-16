<?php
/**
 * Clase Validator - Validación de datos
 * Sistema de Atención Médica - Consulta Externa
 */

namespace Core;

class Validator
{
    private array $errors = [];
    private array $data = [];

    /**
     * Constructor
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Validar datos según reglas
     */
    public function validate(array $rules): bool
    {
        $this->errors = [];

        foreach ($rules as $field => $ruleSet) {
            $rulesArray = is_string($ruleSet) ? explode('|', $ruleSet) : $ruleSet;
            $value = $this->data[$field] ?? null;

            foreach ($rulesArray as $rule) {
                $this->applyRule($field, $value, $rule);
            }
        }

        return empty($this->errors);
    }

    /**
     * Aplicar regla de validación
     */
    private function applyRule(string $field, $value, string $rule): void
    {
        $params = [];
        if (strpos($rule, ':') !== false) {
            [$rule, $paramString] = explode(':', $rule, 2);
            $params = explode(',', $paramString);
        }

        $method = 'validate' . ucfirst($rule);
        if (method_exists($this, $method)) {
            $this->$method($field, $value, $params);
        }
    }

    /**
     * Campo requerido
     */
    private function validateRequired(string $field, $value, array $params): void
    {
        if ($value === null || $value === '' || (is_array($value) && empty($value))) {
            $this->addError($field, "El campo {$field} es obligatorio");
        }
    }

    /**
     * Email válido
     */
    private function validateEmail(string $field, $value, array $params): void
    {
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, "El campo {$field} debe ser un email válido");
        }
    }

    /**
     * Longitud mínima
     */
    private function validateMin(string $field, $value, array $params): void
    {
        $min = (int) ($params[0] ?? 0);
        if (!empty($value) && strlen($value) < $min) {
            $this->addError($field, "El campo {$field} debe tener al menos {$min} caracteres");
        }
    }

    /**
     * Longitud máxima
     */
    private function validateMax(string $field, $value, array $params): void
    {
        $max = (int) ($params[0] ?? 255);
        if (!empty($value) && strlen($value) > $max) {
            $this->addError($field, "El campo {$field} no debe exceder {$max} caracteres");
        }
    }

    /**
     * Solo números
     */
    private function validateNumeric(string $field, $value, array $params): void
    {
        if (!empty($value) && !is_numeric($value)) {
            $this->addError($field, "El campo {$field} debe ser numérico");
        }
    }

    /**
     * Solo enteros
     */
    private function validateInteger(string $field, $value, array $params): void
    {
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_INT)) {
            $this->addError($field, "El campo {$field} debe ser un número entero");
        }
    }

    /**
     * Fecha válida
     */
    private function validateDate(string $field, $value, array $params): void
    {
        $format = $params[0] ?? 'Y-m-d';
        if (!empty($value)) {
            $date = \DateTime::createFromFormat($format, $value);
            if (!$date || $date->format($format) !== $value) {
                $this->addError($field, "El campo {$field} debe ser una fecha válida");
            }
        }
    }

    /**
     * Hora válida
     */
    private function validateTime(string $field, $value, array $params): void
    {
        if (!empty($value) && !preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/', $value)) {
            $this->addError($field, "El campo {$field} debe ser una hora válida");
        }
    }

    /**
     * Valor en lista de opciones
     */
    private function validateIn(string $field, $value, array $params): void
    {
        if (!empty($value) && !in_array($value, $params)) {
            $options = implode(', ', $params);
            $this->addError($field, "El campo {$field} debe ser uno de: {$options}");
        }
    }

    /**
     * Expresión regular
     */
    private function validateRegex(string $field, $value, array $params): void
    {
        $pattern = $params[0] ?? '';
        if (!empty($value) && !preg_match($pattern, $value)) {
            $this->addError($field, "El campo {$field} tiene un formato inválido");
        }
    }

    /**
     * Solo letras
     */
    private function validateAlpha(string $field, $value, array $params): void
    {
        if (!empty($value) && !preg_match('/^[\pL\s]+$/u', $value)) {
            $this->addError($field, "El campo {$field} solo debe contener letras");
        }
    }

    /**
     * Letras y números
     */
    private function validateAlphanumeric(string $field, $value, array $params): void
    {
        if (!empty($value) && !preg_match('/^[\pL\pN\s]+$/u', $value)) {
            $this->addError($field, "El campo {$field} solo debe contener letras y números");
        }
    }

    /**
     * Confirmación de campo
     */
    private function validateConfirmed(string $field, $value, array $params): void
    {
        $confirmField = $field . '_confirmation';
        $confirmValue = $this->data[$confirmField] ?? null;
        
        if ($value !== $confirmValue) {
            $this->addError($field, "La confirmación de {$field} no coincide");
        }
    }

    /**
     * Contraseña segura
     */
    private function validatePassword(string $field, $value, array $params): void
    {
        if (!empty($value)) {
            if (strlen($value) < 8) {
                $this->addError($field, "La contraseña debe tener al menos 8 caracteres");
            }
            if (!preg_match('/[A-Z]/', $value)) {
                $this->addError($field, "La contraseña debe contener al menos una mayúscula");
            }
            if (!preg_match('/[a-z]/', $value)) {
                $this->addError($field, "La contraseña debe contener al menos una minúscula");
            }
            if (!preg_match('/[0-9]/', $value)) {
                $this->addError($field, "La contraseña debe contener al menos un número");
            }
        }
    }

    /**
     * Documento de identidad
     */
    private function validateDocumento(string $field, $value, array $params): void
    {
        if (!empty($value) && !preg_match('/^[A-Z0-9]{6,20}$/i', $value)) {
            $this->addError($field, "El campo {$field} debe ser un documento válido");
        }
    }

    /**
     * Teléfono
     */
    private function validatePhone(string $field, $value, array $params): void
    {
        if (!empty($value) && !preg_match('/^[0-9+\-\s()]{7,20}$/', $value)) {
            $this->addError($field, "El campo {$field} debe ser un teléfono válido");
        }
    }

    /**
     * Agregar error
     */
    public function addError(string $field, string $message): void
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }

    /**
     * Obtener todos los errores
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Obtener errores de un campo
     */
    public function getFieldErrors(string $field): array
    {
        return $this->errors[$field] ?? [];
    }

    /**
     * Verificar si hay errores
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Obtener primer error de cada campo
     */
    public function getFirstErrors(): array
    {
        $firstErrors = [];
        foreach ($this->errors as $field => $errors) {
            $firstErrors[$field] = $errors[0] ?? '';
        }
        return $firstErrors;
    }
}
