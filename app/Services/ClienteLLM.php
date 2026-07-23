<?php

declare(strict_types=1);

namespace App\Services;

use JsonException;
use RuntimeException;

final class ClienteLLM
{
    public function __construct(
        private string $apiKey,
        private string $modelo,
        private string $url
    ) {
    }

    /**
     * Envía el historial a OpenRouter y devuelve el texto generado.
     *
     * @param array<int, array{role: string, content: string}> $mensajes
     */
    public function enviar(array $mensajes): string
    {
        if (!function_exists('curl_init')) {
            throw new RuntimeException(
                'PHP no tiene habilitada la extensión cURL.',
                500
            );
        }

        $cuerpo = $this->crearCuerpoJson($mensajes);

        $curl = curl_init($this->url);

        if ($curl === false) {
            throw new RuntimeException(
                'No se pudo iniciar la conexión con OpenRouter.',
                500
            );
        }

        curl_setopt_array(
            $curl,
            [
                CURLOPT_POST => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $this->apiKey,
                    'Content-Type: application/json',
                    'Accept: application/json',
                ],
                CURLOPT_POSTFIELDS => $cuerpo,
            ]
        );

        $respuesta = curl_exec($curl);

        if ($respuesta === false) {
            $numeroError = curl_errno($curl);
            $mensajeError = curl_error($curl);

            curl_close($curl);

            if ($numeroError === CURLE_OPERATION_TIMEDOUT) {
                throw new RuntimeException(
                    'OpenRouter ha tardado demasiado en responder.',
                    504
                );
            }

            throw new RuntimeException(
                'No se pudo conectar con OpenRouter: ' . $mensajeError,
                502
            );
        }

        $codigoHttp = (int) curl_getinfo(
            $curl,
            CURLINFO_HTTP_CODE
        );

        curl_close($curl);

        $this->comprobarCodigoHttp(
            $codigoHttp,
            $respuesta
        );

        return $this->extraerTexto($respuesta);
    }

    /**
     * @param array<int, array{role: string, content: string}> $mensajes
     */
    private function crearCuerpoJson(array $mensajes): string
    {
        try {
            return json_encode(
                [
                    'model' => $this->modelo,
                    'messages' => $mensajes,
                    'temperature' => 0.7,
                ],
                JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
            );
        } catch (JsonException $e) {
            throw new RuntimeException(
                'No se pudo preparar la petición para OpenRouter.',
                500
            );
        }
    }

    private function comprobarCodigoHttp(
        int $codigoHttp,
        string $respuesta
    ): void {
        if ($codigoHttp >= 200 && $codigoHttp < 300) {
            return;
        }

        $mensajeProveedor = $this->extraerMensajeError(
            $respuesta
        );

        if ($codigoHttp === 401) {
            throw new RuntimeException(
                'La clave de OpenRouter no es válida.',
                401
            );
        }

        if ($codigoHttp === 429) {
            throw new RuntimeException(
                'Se ha alcanzado el límite de peticiones. ' .
                'Espera unos segundos antes de intentarlo otra vez.',
                429
            );
        }

        throw new RuntimeException(
            'OpenRouter devolvió un error'
            . ($mensajeProveedor !== ''
                ? ': ' . $mensajeProveedor
                : ' HTTP ' . $codigoHttp),
            $codigoHttp
        );
    }

    private function extraerMensajeError(
        string $respuesta
    ): string {
        try {
            $datos = json_decode(
                $respuesta,
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException $e) {
            return '';
        }

        if (!is_array($datos)) {
            return '';
        }

        $mensaje = $datos['error']['message']
            ?? $datos['message']
            ?? '';

        return is_string($mensaje) ? $mensaje : '';
    }

    private function extraerTexto(
        string $respuesta
    ): string {
        try {
            $datos = json_decode(
                $respuesta,
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException $e) {
            throw new RuntimeException(
                'OpenRouter devolvió JSON no válido.',
                502
            );
        }

        $texto = $datos['choices'][0]['message']['content']
            ?? null;

        if (!is_string($texto) || trim($texto) === '') {
            throw new RuntimeException(
                'OpenRouter devolvió una respuesta vacía o malformada.',
                502
            );
        }

        return trim($texto);
    }
}