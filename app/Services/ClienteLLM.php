<?php

declare(strict_types=1);

namespace App\Services;

final class ClienteLLM
{
    /**
     * Clave privada de OpenRouter.
     */
    private string $apiKey;

    /**
     * Identificador del modelo de OpenRouter.
     */
    private string $modelo;

    /**
     * Inicializa el cliente de OpenRouter.
     */
    public function __construct(
        string $apiKey,
        string $modelo
    ) {
        $this->apiKey = $apiKey;
        $this->modelo = $modelo;
    }

    /**
     * Envía el historial al modelo y devuelve su respuesta.
     *
     * @param array<int, array{role: string, content: string}> $mensajes
     */
    public function enviar(array $mensajes): string
    {
        // La conexión mediante cURL se implementará en la Fase B.
        return '';
    }
}