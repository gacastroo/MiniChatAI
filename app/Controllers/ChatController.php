<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Conversacion;
use App\Services\ClienteLLM;

final class ChatController
{
    public function __construct(
        private Conversacion $conversacion,
        private ClienteLLM $clienteLLM
    ) {
    }

    /**
     * Procesará las peticiones enviadas desde el chat.
     *
     * @param array<string, mixed> $entrada
     * @return array<string, mixed>
     */
    public function procesar(array $entrada): array
    {
        // Se implementará en la Fase B.
        return [
            'ok' => false,
            'error' => 'El chat todavía no está disponible.',
        ];
    }
}