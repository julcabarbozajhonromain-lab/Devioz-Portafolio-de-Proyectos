<?php

declare(strict_types=1);

namespace App\Services;

use InvalidArgumentException;

/** Rules and reproducible scoring for internal quality reviews. */
final class QualityAuditService
{
    public const STANDARD_VERSION = 'ISO/IEC 25010:2023';
    public const MINIMUM_SCORE = 80.0;

    public static function isoCriteria(): array
    {
        return [
            'functional_suitability' => ['Adecuación funcional', 'Los casos de aceptación acordados pasan y cubren las funciones del alcance.'],
            'performance_efficiency' => ['Eficiencia de desempeño', 'La medición de carga y latencia se compara con objetivos acordados.'],
            'compatibility' => ['Compatibilidad', 'Las integraciones y entornos soportados pasan sus pruebas de interoperabilidad.'],
            'interaction_capability' => ['Capacidad de interacción', 'Las tareas, accesibilidad y prevención de errores se verifican con evidencia.'],
            'reliability' => ['Fiabilidad', 'Recuperación, disponibilidad y manejo de fallos se verifican para el contexto del sistema.'],
            'security' => ['Seguridad', 'Los controles de seguridad se prueban y los hallazgos tienen severidad y tratamiento.'],
            'maintainability' => ['Mantenibilidad', 'El análisis, modularidad, cambios y pruebas del código son verificables.'],
            'flexibility' => ['Flexibilidad', 'La adaptación, escalabilidad e instalación se comprueban en los entornos objetivo.'],
            'safety' => ['Seguridad operacional', 'Los riesgos de daño, restricciones y respuestas seguras se revisan si aplican.'],
        ];
    }

    public static function owaspCriteria(): array
    {
        return [
            'A01' => 'Control de acceso roto',
            'A02' => 'Configuración de seguridad incorrecta',
            'A03' => 'Fallos en la cadena de suministro de software',
            'A04' => 'Fallos criptográficos',
            'A05' => 'Inyección',
            'A06' => 'Diseño inseguro',
            'A07' => 'Fallos de autenticación',
            'A08' => 'Fallos de integridad de software o datos',
            'A09' => 'Fallos de registro y alertas de seguridad',
            'A10' => 'Manejo incorrecto de condiciones excepcionales',
        ];
    }

    /**
     * Build an auditable assessment snapshot. The score is only available when
     * all nine dimensions have a rating and written evidence.
     */
    public static function assess(array $input): array
    {
        $iso = [];
        $ratingTotal = 0;
        $ratingCount = 0;
        $complete = true;

        foreach (self::isoCriteria() as $key => [$label, $prompt]) {
            $rawRating = $input['iso'][$key]['rating'] ?? '';
            $evidence = trim((string)($input['iso'][$key]['evidence'] ?? ''));
            $rating = filter_var($rawRating, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 4]]);
            if ($rating === false || $evidence === '') {
                $complete = false;
            } else {
                $ratingTotal += $rating;
                $ratingCount++;
            }
            $iso[$key] = [
                'label' => $label,
                'prompt' => $prompt,
                'rating' => $rating === false ? null : $rating,
                'evidence' => $evidence,
            ];
        }

        $owasp = [];
        foreach (self::owaspCriteria() as $key => $label) {
            $result = (string)($input['owasp'][$key]['result'] ?? 'not_assessed');
            $evidence = trim((string)($input['owasp'][$key]['evidence'] ?? ''));
            if (!in_array($result, ['no_finding', 'finding', 'not_applicable'], true) || $evidence === '') {
                $complete = false;
            }
            $owasp[$key] = ['label' => $label, 'result' => $result, 'evidence' => $evidence];
        }

        $tools = [];
        foreach (['automated_tests' => 'Pruebas automatizadas', 'static_analysis' => 'Análisis estático', 'security_scan' => 'Análisis de seguridad'] as $key => $label) {
            $status = (string)($input['tools'][$key]['status'] ?? 'not_run');
            $evidence = trim((string)($input['tools'][$key]['evidence'] ?? ''));
            if (!in_array($status, ['passed', 'failed', 'not_run', 'error'], true)) {
                throw new InvalidArgumentException('Estado de herramienta no válido.');
            }
            $tools[$key] = [
                'label' => $label,
                'status' => $status,
                'evidence' => $evidence,
                'source' => ($input['tools'][$key]['source'] ?? 'manual') === 'integration' ? 'integration' : 'manual',
                'evidence_url' => trim((string)($input['tools'][$key]['evidence_url'] ?? '')),
                'metrics' => $input['tools'][$key]['metrics'] ?? null,
            ];
            if ($status !== 'passed' || $evidence === '') {
                $complete = false;
            }
        }

        $findings = [];
        foreach (($input['findings'] ?? []) as $finding) {
            $title = trim((string)($finding['title'] ?? ''));
            $description = trim((string)($finding['description'] ?? ''));
            if ($title === '' && $description === '') {
                continue;
            }
            $severity = (string)($finding['severity'] ?? 'medium');
            if (!in_array($severity, ['critical', 'high', 'medium', 'low', 'info'], true) || $title === '' || $description === '') {
                throw new InvalidArgumentException('Cada hallazgo requiere título, descripción y severidad válida.');
            }
            $findings[] = [
                'title' => $title,
                'description' => $description,
                'severity' => $severity,
                'status' => 'open',
            ];
        }

        $score = $ratingCount === count(self::isoCriteria()) ? round(($ratingTotal / ($ratingCount * 4)) * 100, 2) : null;
        $hasBlocker = count(array_filter($findings, static fn(array $finding): bool => in_array($finding['severity'], ['critical', 'high'], true))) > 0;
        $anyFailedTool = count(array_filter($tools, static fn(array $tool): bool => $tool['status'] === 'failed')) > 0;
        $allToolsPassed = count(array_filter($tools, static fn(array $tool): bool => $tool['status'] === 'passed' && $tool['evidence'] !== '')) === count($tools);
        $anyOwaspFinding = count(array_filter($owasp, static fn(array $item): bool => $item['result'] === 'finding')) > 0;
        $allOwaspReviewed = count(array_filter($owasp, static fn(array $item): bool => in_array($item['result'], ['no_finding', 'finding', 'not_applicable'], true) && $item['evidence'] !== '')) === count($owasp);

        if ($hasBlocker) {
            $gateStatus = 'blocked';
            $auditStatus = 'blocked';
        } elseif ($anyFailedTool || ($score !== null && $score < self::MINIMUM_SCORE) || $anyOwaspFinding) {
            $gateStatus = 'failed';
            $auditStatus = 'needs_changes';
        } elseif ($complete && $allToolsPassed && $allOwaspReviewed && $score !== null && $score >= self::MINIMUM_SCORE) {
            $gateStatus = 'passed';
            $auditStatus = 'passed';
        } else {
            $gateStatus = 'pending';
            $auditStatus = 'in_review';
        }

        return [
            'criteria' => ['iso_iec_25010' => self::STANDARD_VERSION, 'iso' => $iso, 'owasp' => ['version' => 'OWASP Top 10:2025', 'items' => $owasp], 'tools' => $tools],
            'findings' => $findings,
            'score' => $score,
            'gate_status' => $gateStatus,
            'status' => $auditStatus,
            'complete' => $complete,
        ];
    }
}
