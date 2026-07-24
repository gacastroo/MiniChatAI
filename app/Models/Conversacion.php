<?php

declare(strict_types=1);

namespace App\Models;

final class Conversacion
{
    private const CLAVE_SESION = 'minichatgpt_historial';
    private const MAX_MENSAJES = 20;

    private const MENSAJE_SISTEMA =
        'Eres un asistente útil y cercano. ' .
        'Respondes siempre en español, de forma clara y breve. ' .
        'Si no sabes algo, lo dices en lugar de inventarlo.';

    /**
     * Añade un mensaje escrito por el usuario.
     */
    public function anadirUsuario(string $mensaje): void
    {
        $this->inicializar();

        $_SESSION[self::CLAVE_SESION][] = [
            'role' => 'user',
            'content' => $mensaje,
        ];

        $this->limitarHistorial();
    }

    /**
     * Añade una respuesta generada por el asistente.
     */
    public function anadirAsistente(string $mensaje): void
    {
        $this->inicializar();

        $_SESSION[self::CLAVE_SESION][] = [
            'role' => 'assistant',
            'content' => $mensaje,
        ];

        $this->limitarHistorial();
    }

    /**
     * Devuelve todos los mensajes de la conversación.
     *
     * @return array<int, array{role: string, content: string}>
     */
    public function obtenerMensajes(): array
    {
        $this->inicializar();

        return $_SESSION[self::CLAVE_SESION];
    }

    /**
     * Borra el historial y crea una conversación nueva.
     */
    public function reiniciar(): void
    {
        unset($_SESSION[self::CLAVE_SESION]);

        $this->inicializar();
    }

    /**
     * Crea el historial inicial si todavía no existe.
     */
    private function inicializar(): void
    {
        if (
            isset($_SESSION[self::CLAVE_SESION]) &&
            is_array($_SESSION[self::CLAVE_SESION])
        ) {
            return;
        }

        $_SESSION[self::CLAVE_SESION] = [
            [
                'role' => 'system',
                'content' => self::MENSAJE_SISTEMA,
            ],
        ];
    }

    /**
     * Conserva el mensaje system y los veinte mensajes más recientes.
     */
    private function limitarHistorial(): void
    {
        $historial = $_SESSION[self::CLAVE_SESION];

        if (count($historial) <= self::MAX_MENSAJES + 1) {
            return;
        }

        $mensajeSistema = $historial[0];

        $mensajesRecientes = array_slice(
            $historial,
            -self::MAX_MENSAJES
        );

        $_SESSION[self::CLAVE_SESION] = array_merge(
            [$mensajeSistema],
            $mensajesRecientes
        );
    }
}