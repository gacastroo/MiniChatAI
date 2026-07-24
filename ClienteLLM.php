<?php

declare(strict_types=1);

final class ClienteLLM
{
    public function __construct(
        private string $url,
        private string $apiKey,
        private float $temperatura,
        private string $prioridad,
        private string $appUrl,
        private string $appNombre
    ) {
    }

    /**
     * @param array<int, array{role:string, content:string}> $mensajes
     * @param string[] $modelos
     * @return array{texto:string, modelo:string}
     */
    public function preguntar(array $mensajes, array $modelos): array
    {
        // OpenRouter rechaza arrays con más de tres modelos.
        $modelos = array_slice(
            array_values(array_unique($modelos)),
            0,
            3
        );

        if ($modelos === []) {
            throw new InvalidArgumentException(
                'La lista de modelos está vacía.'
            );
        }

        $prioridad = $this->prioridad === 'throughput'
            ? 'throughput'
            : 'latency';

        $payload = [
            'models' => array_values($modelos),
            'messages' => $mensajes,
            'temperature' => $this->temperatura,
            'stream' => false,
            'provider' => [
                'sort' => [
                    'by' => $prioridad,
                    'partition' => 'none',
                ],
                'allow_fallbacks' => true,
            ],
        ];

        $respuesta = $this->peticionPost($payload);

        $texto = $respuesta['choices'][0]['message']['content'] ?? null;
        $modelo = $respuesta['model'] ?? 'desconocido';

        if (!is_string($texto) || trim($texto) === '') {
            throw new RuntimeException(
                'El modelo no devolvió una respuesta de texto.'
            );
        }

        return [
            'texto' => trim($texto),
            'modelo' => is_string($modelo) ? $modelo : 'desconocido',
        ];
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function peticionPost(array $payload): array
    {
        if (!function_exists('curl_init')) {
            throw new RuntimeException(
                'PHP no tiene habilitada la extensión cURL.'
            );
        }

        try {
            $json = json_encode(
                $payload,
                JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
            );
        } catch (JsonException $e) {
            throw new RuntimeException(
                'No se pudo construir la petición JSON.'
            );
        }

        $curl = curl_init($this->url);

        if ($curl === false) {
            throw new RuntimeException('No se pudo iniciar cURL.');
        }

        $cabeceras = [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json',
            'Accept: application/json',
        ];

        if ($this->appUrl !== '') {
            $cabeceras[] = 'HTTP-Referer: ' . $this->appUrl;
        }

        if ($this->appNombre !== '') {
            $cabeceras[] = 'X-OpenRouter-Title: ' . $this->appNombre;
        }

        curl_setopt_array($curl, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_TIMEOUT => 180,
            CURLOPT_HTTPHEADER => $cabeceras,
            CURLOPT_POSTFIELDS => $json,
        ]);

        $cuerpo = curl_exec($curl);
        $errorCurl = curl_error($curl);
        $codigoHttp = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        if ($cuerpo === false) {
            throw new RuntimeException(
                'No se pudo conectar con OpenRouter: ' . $errorCurl
            );
        }

        try {
            $datos = json_decode(
                $cuerpo,
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException $e) {
            throw new RuntimeException(
                'OpenRouter devolvió una respuesta JSON no válida.'
            );
        }

        if (!is_array($datos)) {
            throw new RuntimeException(
                'La respuesta de OpenRouter no es válida.'
            );
        }

        if ($codigoHttp < 200 || $codigoHttp >= 300) {
            $mensaje = $datos['error']['message']
                ?? $datos['message']
                ?? ('Error HTTP ' . $codigoHttp);

            throw new RuntimeException(
                'OpenRouter: ' . (string) $mensaje
            );
        }

        return $datos;
    }
}
