<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Devioz 24 — Validador Declarativo de Formularios y Peticiones
 */
final class Validator
{
    private array $data = [];
    private array $rules = [];
    private array $errors = [];

    public function __construct(array $data, array $rules)
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->execute();
    }

    public static function validate(array $data, array $rules): self
    {
        return new self($data, $rules);
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function passes(): bool
    {
        return empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(?string $field = null): ?string
    {
        if ($field !== null) {
            return $this->errors[$field][0] ?? null;
        }

        foreach ($this->errors as $fieldErrors) {
            if (!empty($fieldErrors)) {
                return $fieldErrors[0];
            }
        }

        return null;
    }

    /**
     * Guarda los datos previos y errores en la sesión para persistencia en vista.
     */
    public function flashErrorsAndInput(): void
    {
        foreach ($this->data as $key => $value) {
            if (!is_array($value) && !str_contains($key, 'password')) {
                Session::flash('old_' . $key, (string)$value);
            }
        }
        foreach ($this->errors as $field => $messages) {
            foreach ($messages as $msg) {
                Session::flash('error', $msg);
            }
        }
    }

    private function execute(): void
    {
        foreach ($this->rules as $field => $ruleSet) {
            $rulesList = is_string($ruleSet) ? explode('|', $ruleSet) : $ruleSet;
            $value = $this->data[$field] ?? null;

            foreach ($rulesList as $ruleItem) {
                $params = [];
                if (str_contains($ruleItem, ':')) {
                    [$ruleName, $paramStr] = explode(':', $ruleItem, 2);
                    $params = explode(',', $paramStr);
                } else {
                    $ruleName = $ruleItem;
                }

                $this->applyRule($field, $value, $ruleName, $params);
            }
        }
    }

    private function applyRule(string $field, mixed $value, string $rule, array $params): void
    {
        $label = ucfirst(str_replace('_', ' ', $field));

        switch ($rule) {
            case 'required':
                if ($value === null || $value === '' || (is_array($value) && empty($value))) {
                    $this->addError($field, "El campo {$label} es obligatorio.");
                }
                break;

            case 'email':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, "El campo {$label} debe ser un correo electrónico válido.");
                }
                break;

            case 'min':
                $min = (int)($params[0] ?? 0);
                if (is_string($value) && mb_strlen($value) < $min) {
                    $this->addError($field, "El campo {$label} debe tener al menos {$min} caracteres.");
                } elseif (is_numeric($value) && $value < $min) {
                    $this->addError($field, "El valor de {$label} debe ser mínimo {$min}.");
                } elseif (is_array($value) && count($value) < $min) {
                    $this->addError($field, "Debe seleccionar al menos {$min} elementos en {$label}.");
                }
                break;

            case 'max':
                $max = (int)($params[0] ?? 255);
                if (is_string($value) && mb_strlen($value) > $max) {
                    $this->addError($field, "El campo {$label} no puede superar los {$max} caracteres.");
                } elseif (is_numeric($value) && $value > $max) {
                    $this->addError($field, "El valor de {$label} no puede ser mayor a {$max}.");
                } elseif (is_array($value) && count($value) > $max) {
                    $this->addError($field, "No puede exceder {$max} elementos en {$label}.");
                }
                break;

            case 'in':
                if (!empty($value) && !in_array((string)$value, $params, true)) {
                    $this->addError($field, "La opción seleccionada en {$label} no es válida.");
                }
                break;

            case 'url':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_URL)) {
                    $this->addError($field, "El campo {$label} debe ser una dirección URL válida.");
                }
                break;

            case 'numeric':
                if (!empty($value) && !is_numeric($value)) {
                    $this->addError($field, "El campo {$label} debe ser un valor numérico.");
                }
                break;

            case 'date':
                if (!empty($value)) {
                    $d = \DateTime::createFromFormat('Y-m-d', (string)$value);
                    if (!$d || $d->format('Y-m-d') !== $value) {
                        $this->addError($field, "El campo {$label} debe ser una fecha válida (YYYY-MM-DD).");
                    }
                }
                break;

            case 'unique':
                // Formato: unique:table,column,exceptId,idColumn
                if (!empty($value)) {
                    $table = $params[0] ?? '';
                    $column = $params[1] ?? $field;
                    $exceptId = $params[2] ?? null;
                    $idCol = $params[3] ?? 'id';

                    $db = Database::getInstance();
                    $sql = "SELECT COUNT(*) as count FROM `{$table}` WHERE `{$column}` = :val";
                    $binds = ['val' => $value];

                    if ($exceptId !== null && $exceptId !== '') {
                        $sql .= " AND `{$idCol}` != :except_id";
                        $binds['except_id'] = $exceptId;
                    }

                    $row = $db->fetch($sql, $binds);
                    if ($row && (int)$row['count'] > 0) {
                        $this->addError($field, "El valor ingresado en {$label} ya se encuentra registrado.");
                    }
                }
                break;
        }
    }

    private function addError(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }
}
