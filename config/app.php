<?php

declare(strict_types=1);

/**
 * Devioz OS — Configuración General de la Aplicación
 */

return [
    'name' => getenv('APP_NAME') ?: 'Devioz 24',
    'env' => getenv('APP_ENV') ?: 'local',
    'debug' => filter_var(getenv('APP_DEBUG') ?: 'false', FILTER_VALIDATE_BOOL),
    'url' => getenv('APP_URL') ?: 'http://localhost/Devioz-Portafolio-de-Proyectos/public',
    'timezone' => getenv('APP_TIMEZONE') ?: 'America/Lima',
    
    // Configuración de subidas (Uploads)
    'upload' => [
        'max_size' => (int)(getenv('UPLOAD_MAX_SIZE') ?: 524288000), // 500 MB en bytes
        'allowed_types' => explode(',', getenv('UPLOAD_ALLOWED_TYPES') ?: 'pdf,zip,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,webp,gif'),
        'path' => dirname(__DIR__) . '/public/uploads',
        'private_path' => getenv('UPLOAD_PRIVATE_PATH') ?: dirname(__DIR__) . '/storage/private/uploads',
    ],

    // Configuración de Inteligencia Artificial para LABVA (Groq + Gemini + Local Fallback)
    'ai' => [
        'groq_api_key' => getenv('GROQ_API_KEY') ?: '',
        'groq_model' => getenv('GROQ_MODEL') ?: 'llama-3.3-70b-versatile',
        'gemini_api_key' => getenv('GEMINI_API_KEY') ?: '',
        'gemini_model' => getenv('GEMINI_MODEL') ?: 'gemini-1.5-flash',
    ],
];
