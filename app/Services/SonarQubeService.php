<?php

declare(strict_types=1);

namespace App\Services;

/** Read-only SonarQube Quality Gate connector. Credentials and host come from server config. */
final class SonarQubeService
{
    public static function qualityGate(string $projectKey): array
    {
        $baseUrl = rtrim(trim((string)getenv('SONARQUBE_URL')), '/');
        $token = trim((string)getenv('SONARQUBE_TOKEN'));
        $projectKey = trim($projectKey);

        if ($baseUrl === '' || $token === '' || $projectKey === '') {
            return ['configured' => false, 'status' => 'not_run', 'source' => 'integration', 'notes' => 'Configura SONARQUBE_URL, SONARQUBE_TOKEN y project key para consultar SonarQube.'];
        }
        $parts = parse_url($baseUrl);
        if (!is_array($parts) || !in_array(strtolower((string)($parts['scheme'] ?? '')), ['http', 'https'], true) || empty($parts['host']) || isset($parts['user']) || isset($parts['pass'])) {
            return ['configured' => true, 'status' => 'error', 'source' => 'integration', 'notes' => 'SONARQUBE_URL debe ser una URL HTTP(S) válida sin credenciales embebidas.'];
        }
        if (!function_exists('curl_init')) {
            return ['configured' => true, 'status' => 'error', 'source' => 'integration', 'notes' => 'La extensión cURL de PHP no está habilitada.'];
        }

        $url = $baseUrl . '/api/qualitygates/project_status?' . http_build_query(['projectKey' => $projectKey]);
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $token, 'Accept: application/json'],
            CURLOPT_CONNECTTIMEOUT => 4,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);
        $body = curl_exec($curl);
        $httpStatus = (int)curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        $error = curl_error($curl);
        curl_close($curl);

        if ($body === false || $httpStatus < 200 || $httpStatus >= 300) {
            return [
                'configured' => true, 'status' => 'error', 'source' => 'integration',
                'notes' => $body === false ? 'No se pudo conectar a SonarQube: ' . $error : 'SonarQube respondió HTTP ' . $httpStatus . '.',
            ];
        }
        $payload = json_decode((string)$body, true);
        $qualityGate = $payload['projectStatus'] ?? null;
        if (!is_array($qualityGate) || !in_array(($qualityGate['status'] ?? ''), ['OK', 'ERROR', 'WARN', 'NONE'], true)) {
            return ['configured' => true, 'status' => 'error', 'source' => 'integration', 'notes' => 'La respuesta de SonarQube no contiene un estado de Quality Gate reconocido.'];
        }

        $gate = $qualityGate['status'];
        $status = $gate === 'OK' ? 'passed' : ($gate === 'ERROR' ? 'failed' : 'not_run');
        return [
            'configured' => true,
            'status' => $status,
            'source' => 'integration',
            'evidence_url' => $baseUrl . '/dashboard?id=' . rawurlencode($projectKey),
            'metrics' => ['quality_gate_status' => $gate, 'conditions' => $qualityGate['conditions'] ?? []],
            'notes' => 'Resultado consultado en SonarQube. El Quality Gate refleja las condiciones configuradas para el análisis del proyecto.',
        ];
    }
}
