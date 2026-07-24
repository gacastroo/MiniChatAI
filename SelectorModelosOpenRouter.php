<?php

declare(strict_types=1);

final class SelectorModelosOpenRouter
{
    /**
     * Palabras que identifican modelos auxiliares que no deben usarse como chat.
     * Se revisan en el ID, nombre y descripción del modelo.
     *
     * @var string[]
     */
    private const TERMINOS_EXCLUIDOS = [
        'content-safety',
        'content safety',
        'safety model',
        'safety classifier',
        'guardrail',
        'guardrails',
        'moderation',
        'moderator',
        'classifier',
        'classification',
        'prompt-guard',
        'prompt guard',
        'policy model',
        'toxicity',
        'rerank',
        'reranker',
        'embedding',
        'embed model',
        '-embed-',
        '/embed-',
        'reward model',
        'scoring model',
        'judge model',
        'detector model',
        'ocr model',
        'transcription model',
        'speech-to-text',
        'text-to-speech',
    ];

    public function __construct(
        private string $url,
        private string $apiKey,
        private string $prioridad,
        private int $maxModelos,
        private string $cacheArchivo,
        private int $cacheSegundos
    ) {
        // OpenRouter admite como máximo tres modelos en el array "models".
        $this->maxModelos = max(1, min(3, $this->maxModelos));
    }

    /**
     * @return string[]
     */
    public function obtener(): array
    {
        $cache = $this->leerCache();

        if ($cache !== null) {
            return array_slice($cache, 0, $this->maxModelos);
        }

        $orden = $this->prioridad === 'throughput'
            ? 'throughput-high-to-low'
            : 'latency-low-to-high';

        // OpenRouter filtra primero los modelos de entrada/salida textual.
        // El filtrado local posterior elimina modelos auxiliares de seguridad,
        // embeddings, reranking y clasificación.
        $url = $this->url . '?' . http_build_query([
            'sort' => $orden,
            'input_modalities' => 'text',
            'output_modalities' => 'text',
            'supported_parameters' => 'temperature,max_tokens',
            'min_price' => 0,
            'max_price' => 0,
            'min_output_price' => 0,
            'max_output_price' => 0,
        ]);

        $respuesta = $this->peticionGet($url);

        if (!isset($respuesta['data']) || !is_array($respuesta['data'])) {
            throw new RuntimeException(
                'OpenRouter no devolvió un catálogo de modelos válido.'
            );
        }

        $modelos = [];

        foreach ($respuesta['data'] as $modelo) {
            if (!is_array($modelo) || !$this->esModeloConversacional($modelo)) {
                continue;
            }

            $id = (string) ($modelo['id'] ?? '');
            $modelos[] = $id;

            if (count($modelos) >= $this->maxModelos) {
                break;
            }
        }

        if ($modelos === []) {
            throw new RuntimeException(
                'No se encontraron modelos gratuitos conversacionales disponibles en OpenRouter.'
            );
        }

        $this->guardarCache($modelos);

        return $modelos;
    }

    /**
     * @param array<string, mixed> $modelo
     */
    private function esModeloConversacional(array $modelo): bool
    {
        $id = trim((string) ($modelo['id'] ?? ''));
        $nombre = trim((string) ($modelo['name'] ?? ''));
        $descripcion = trim((string) ($modelo['description'] ?? ''));
        $pricing = $modelo['pricing'] ?? null;
        $architecture = $modelo['architecture'] ?? null;
        $supported = $modelo['supported_parameters'] ?? null;

        if ($id === '' || $id === 'openrouter/free' || !is_array($pricing)) {
            return false;
        }

        // Comprobación local de gratuidad por seguridad.
        $precioEntrada = (float) ($pricing['prompt'] ?? -1);
        $precioSalida = (float) ($pricing['completion'] ?? -1);

        if ($precioEntrada !== 0.0 || $precioSalida !== 0.0) {
            return false;
        }

        if (!is_array($architecture)) {
            return false;
        }

        $entradas = $architecture['input_modalities'] ?? [];
        $salidas = $architecture['output_modalities'] ?? [];

        if (!is_array($entradas) || !in_array('text', $entradas, true)) {
            return false;
        }

        if (!is_array($salidas) || !in_array('text', $salidas, true)) {
            return false;
        }

        // Un modelo de chat normal debe aceptar parámetros básicos de generación.
        if (!is_array($supported)) {
            return false;
        }

        if (
            !in_array('temperature', $supported, true) ||
            !in_array('max_tokens', $supported, true)
        ) {
            return false;
        }

        $textoBusqueda = strtolower($id . ' ' . $nombre . ' ' . $descripcion);

        foreach (self::TERMINOS_EXCLUIDOS as $termino) {
            if (str_contains($textoBusqueda, $termino)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Comprueba IDs almacenados en una caché antigua.
     */
    private function esIdConversacional(string $id): bool
    {
        $id = strtolower(trim($id));

        if ($id === '' || $id === 'openrouter/free') {
            return false;
        }

        foreach (self::TERMINOS_EXCLUIDOS as $termino) {
            if (str_contains($id, $termino)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array<string, mixed>
     */
    private function peticionGet(string $url): array
    {
        if (!function_exists('curl_init')) {
            throw new RuntimeException(
                'PHP no tiene habilitada la extensión cURL.'
            );
        }

        $curl = curl_init($url);

        if ($curl === false) {
            throw new RuntimeException('No se pudo iniciar cURL.');
        }

        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->apiKey,
                'Accept: application/json',
            ],
        ]);

        $cuerpo = curl_exec($curl);
        $errorCurl = curl_error($curl);
        $codigoHttp = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        if ($cuerpo === false) {
            throw new RuntimeException(
                'No se pudo consultar OpenRouter: ' . $errorCurl
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
                'La respuesta del catálogo de OpenRouter no es válida.'
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

    /**
     * @return string[]|null
     */
    private function leerCache(): ?array
    {
        if (!is_file($this->cacheArchivo)) {
            return null;
        }

        $modificacion = filemtime($this->cacheArchivo);

        if (
            $modificacion === false ||
            (time() - $modificacion) > $this->cacheSegundos
        ) {
            return null;
        }

        $contenido = file_get_contents($this->cacheArchivo);

        if ($contenido === false || $contenido === '') {
            return null;
        }

        try {
            $datos = json_decode(
                $contenido,
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException $e) {
            return null;
        }

        if (!is_array($datos)) {
            return null;
        }

        $modelos = array_values(
            array_filter(
                $datos,
                fn ($modelo): bool =>
                    is_string($modelo) && $this->esIdConversacional($modelo)
            )
        );

        return $modelos !== [] ? $modelos : null;
    }

    /**
     * @param string[] $modelos
     */
    private function guardarCache(array $modelos): void
    {
        $json = json_encode(
            $modelos,
            JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
        );

        if ($json === false) {
            return;
        }

        @file_put_contents(
            $this->cacheArchivo,
            $json,
            LOCK_EX
        );
    }
}
