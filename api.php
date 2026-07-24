<?php

declare(strict_types=1);

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/SelectorModelosOpenRouter.php';
require_once __DIR__ . '/ClienteLLM.php';

function responderError(string $mensaje, int $codigo = 500): void
{
    http_response_code($codigo);

    echo json_encode(
        ['error' => $mensaje],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

try {
    $contenidoEntrada = file_get_contents('php://input');

    $entrada = json_decode(
        $contenidoEntrada ?: '{}',
        true,
        512,
        JSON_THROW_ON_ERROR
    );
} catch (JsonException $e) {
    responderError(
        'La petición contiene JSON no válido.',
        400
    );
}

if (!is_array($entrada)) {
    responderError('La petición no es válida.', 400);
}

if (($entrada['accion'] ?? '') === 'reiniciar') {
    unset($_SESSION['historial']);

    echo json_encode(
        ['ok' => true],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

/*
|--------------------------------------------------------------------------
| AQUÍ NO SE PEGA LA CLAVE
|--------------------------------------------------------------------------
| La clave se pega únicamente en config.php.
*/
if (
    OPENROUTER_API_KEY === '' ||
    str_contains(OPENROUTER_API_KEY, 'PEGA_AQUI') ||
    !str_starts_with(OPENROUTER_API_KEY, 'sk-or-')
) {
    responderError(
        'Falta la clave de OpenRouter. Pégala en config.php.',
        500
    );
}

$mensaje = trim((string) ($entrada['mensaje'] ?? ''));

if ($mensaje === '') {
    responderError('El mensaje está vacío.', 400);
}

if (!isset($_SESSION['historial']) || !is_array($_SESSION['historial'])) {
    $_SESSION['historial'] = [
        [
            'role' => 'system',
            'content' => LLM_SYSTEM,
        ],
    ];
}

$_SESSION['historial'][] = [
    'role' => 'user',
    'content' => $mensaje,
];

if (count($_SESSION['historial']) > LLM_MAX_HISTORIAL) {
    $sistema = $_SESSION['historial'][0];

    $recientes = array_slice(
        $_SESSION['historial'],
        -(LLM_MAX_HISTORIAL - 1)
    );

    $_SESSION['historial'] = array_merge(
        [$sistema],
        $recientes
    );
}

try {
    $modelos = $_SESSION['cache_modelos'] ?? null;

    if (!is_array($modelos) || $modelos === []) {
        $selector = new SelectorModelosOpenRouter(
            OPENROUTER_MODELS_URL,
            OPENROUTER_API_KEY,
            OPENROUTER_PRIORIDAD,
            OPENROUTER_MAX_MODELOS,
            OPENROUTER_CACHE_ARCHIVO,
            OPENROUTER_CACHE_SEGUNDOS
        );

        $modelos = $selector->obtener();
        $_SESSION['cache_modelos'] = $modelos;
    }

    // Liberar el bloqueo de sesión ANTES de la llamada HTTP larga
    session_write_close();

    $cliente = new ClienteLLM(
        OPENROUTER_CHAT_URL,
        OPENROUTER_API_KEY,
        LLM_TEMPERATURA,
        OPENROUTER_PRIORIDAD,
        OPENROUTER_APP_URL,
        OPENROUTER_APP_NOMBRE
    );

    $resultado = $cliente->preguntar(
        $_SESSION['historial'],
        $modelos
    );

    // Re-adquirir sesión para guardar la respuesta
    session_start();
    $_SESSION['historial'][] = [
        'role' => 'assistant',
        'content' => $resultado['texto'],
    ];
    session_write_close();
} catch (Throwable $e) {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    array_pop($_SESSION['historial']);
    session_write_close();

    responderError($e->getMessage(), 500);
}

echo json_encode(
    [
        'respuesta' => $resultado['texto'],
        'modelo' => $resultado['modelo'],
        'prioridad' => OPENROUTER_PRIORIDAD,
    ],
    JSON_UNESCAPED_UNICODE
);
