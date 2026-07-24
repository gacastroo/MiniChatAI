<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| PEGA AQUÍ TU NUEVA CLAVE DE OPENROUTER
|--------------------------------------------------------------------------
| No reutilices la clave que publicaste anteriormente. Revócala y crea otra.
*/
define('OPENROUTER_API_KEY', 'PEGA_AQUI_TU_NUEVA_CLAVE');

// Endpoints de OpenRouter
define('OPENROUTER_CHAT_URL', 'https://openrouter.ai/api/v1/chat/completions');
define('OPENROUTER_MODELS_URL', 'https://openrouter.ai/api/v1/models');

// Datos opcionales de tu aplicación
define('OPENROUTER_APP_URL', 'http://127.0.0.1:8000');
define('OPENROUTER_APP_NOMBRE', 'Chat IA local');

// Prioridad: 'latency' = empieza antes; 'throughput' = genera más rápido
define('OPENROUTER_PRIORIDAD', 'latency');

// Número de modelos gratuitos que se enviarán como alternativas
define('OPENROUTER_MAX_MODELOS', 3);

// Caché para no consultar el catálogo en cada mensaje
define('OPENROUTER_CACHE_ARCHIVO', __DIR__ . '/cache_modelos_openrouter.json');
define('OPENROUTER_CACHE_SEGUNDOS', 3600);

// Ajustes del asistente
define('LLM_TEMPERATURA', 0.7);
define('LLM_MAX_HISTORIAL', 20);
define(
    'LLM_SYSTEM',
    'Eres un asistente útil y cercano. ' .
    'Respondes siempre en español, de forma clara y breve. ' .
    'Si no sabes algo, lo dices en lugar de inventarlo.'
);
