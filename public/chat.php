<?php

declare(strict_types=1);

use App\Config\Config;
use App\Controllers\ChatController;
use App\Models\Conversacion;
use App\Services\ClienteLLM;

session_start();

header('Content-Type: application/json; charset=utf-8');

/**
 * Autocarga las clases del namespace App desde la carpeta app/.
 */
spl_autoload_register(
    static function (string $clase): void {
        $prefijo = 'App\\';

        if (!str_starts_with($clase, $prefijo)) {
            return;
        }

        $nombreRelativo = substr(
            $clase,
            strlen($prefijo)
        );

        $ruta = dirname(__DIR__)
            . DIRECTORY_SEPARATOR
            . 'app'
            . DIRECTORY_SEPARATOR
            . str_replace(
                '\\',
                DIRECTORY_SEPARATOR,
                $nombreRelativo
            )
            . '.php';

        if (is_file($ruta)) {
            require_once $ruta;
        }
    }
);

/**
 * Devuelve una respuesta JSON y termina la ejecuciÃƒÂ³n.
 *
 * @param array<string, mixed> $cuerpo
 */
function responderJson(
    int $codigoHttp,
    array $cuerpo
): never {
    http_response_code($codigoHttp);

    try {
        $json = json_encode(
            $cuerpo,
            JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
        );
    } catch (JsonException $e) {
        http_response_code(500);

        $json = json_encode(
            [
                'ok' => false,
                'error' => 'No se pudo generar la respuesta JSON.',
            ],
            JSON_UNESCAPED_UNICODE
        );
    }

    print $json;
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderJson(
        405,
        [
            'ok' => false,
            'error' => 'Solo se permiten peticiones POST.',
        ]
    );
}

$contenido = file_get_contents('php://input');

if ($contenido === false) {
    responderJson(
        400,
        [
            'ok' => false,
            'error' => 'No se pudo leer la peticiÃƒÂ³n.',
        ]
    );
}



try {
    $entrada = json_decode(
        $contenido !== '' ? $contenido : '{}',
        true,
        512,
        JSON_THROW_ON_ERROR
    );
} catch (JsonException $e) {
    responderJson(
        400,
        [
            'ok' => false,
            'error' => 'La peticiÃƒÂ³n contiene JSON no vÃƒÂ¡lido.',
        ]
    );
}

if (!is_array($entrada)) {
    responderJson(
        400,
        [
            'ok' => false,
            'error' => 'La peticiÃƒÂ³n debe contener un objeto JSON.',
        ]
    );
}

try {
    $apiKey = Config::obtener(
        'OPENROUTER_API_KEY'
    );

    $modelo = Config::obtener(
        'OPENROUTER_MODEL'
    );

    $conversacion = new Conversacion();

    $clienteLLM = new ClienteLLM(
        $apiKey,
        $modelo
    );

    $controlador = new ChatController(
        $conversacion,
        $clienteLLM
    );

    $resultado = $controlador->procesar(
        $entrada
    );

    responderJson(
        $resultado['codigo'],
        $resultado['cuerpo']
    );
} catch (RuntimeException $e) {
    responderJson(
        500,
        [
            'ok' => false,
            'error' => $e->getMessage(),
        ]
    );
} catch (Throwable $e) {
    responderJson(
        500,
        [
            'ok' => false,
            'error' => 'Se produjo un error interno inesperado.',
        ]
    );
}