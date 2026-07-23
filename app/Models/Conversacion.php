<?php

declare(strict_types=1);

namespace App\Models;

final class Conversacion
{
    /**
     * Añade un mensaje escrito por el usuario.
     */
    public function anadirUsuario(string $mensaje): void
    {
        // Se implementará en la Fase B.
    }

    /**
     * Añade una respuesta generada por el asistente.
     */
    public function anadirAsistente(string $mensaje): void
    {
        // Se implementará en la Fase B.
    }

    /**
     * Devuelve el historial de la conversación.
     *
     * @return array<int, array{role: string, content: string}>
     */
    public function obtenerMensajes(): array
    {
        return [];
    }

    /**
     * Borra el historial de la conversación.
     */
    public function reiniciar(): void
    {
        // Se implementará en la Fase B.
    }
}