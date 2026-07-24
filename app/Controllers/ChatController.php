<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Conversacion;
use App\Services\ClienteLLM;
use RuntimeException;
use Throwable;

final class ChatController
{
    public function __construct(
        private Conversacion $conversacion,
        private ClienteLLM $clienteLLM
    ) {
    }

    /**
     * Procesa una petición del chat.
     *
     * @param array<string, mixed> $entrada
     * @return array{
     *     codigo: int,
     *     cuerpo: array<string, mixed>
     * }
     */
    public function procesar(array $entrada): array
    {
        if (($entrada['accion'] ?? '') === 'reiniciar') {
            $this->conversacion->reiniciar();

            return $this->respuesta(
                200,
                ['ok' => true]
            );
        }

        $mensaje = trim(
            (string) ($entrada['mensaje'] ?? '')
        );

        if ($mensaje === '') {
            return $this->respuesta(
                400,
                [
                    'ok' => false,
                    'error' => 'El mensaje no puede estar vacío.',
                ]
            );
        }

        if ($this->longitud($mensaje) > 2000) {
            return $this->respuesta(
                400,
                [
                    'ok' => false,
                    'error' =>
                        'El mensaje no puede superar los 2000 caracteres.',
                ]
            );
        }

        try {
            $this->conversacion->anadirUsuario($mensaje);

            $respuestaModelo = $this->clienteLLM->enviar(
                $this->conversacion->obtenerMensajes()
            );

            $this->conversacion->anadirAsistente(
                $respuestaModelo
            );

            return $this->respuesta(
                200,
                [
                    'ok' => true,
                    'respuesta' => $respuestaModelo,
                ]
            );
        } catch (RuntimeException $e) {
            return $this->respuesta(
                $this->normalizarCodigoHttp($e->getCode()),
                [
                    'ok' => false,
                    'error' => $e->getMessage(),
                ]
            );
        } catch (Throwable $e) {
            return $this->respuesta(
                500,
                [
                    'ok' => false,
                    'error' =>
                        'Se produjo un error interno inesperado.',
                ]
            );
        }
    }

    /**
     * Calcula la longitud del mensaje.
     */
    private function longitud(string $texto): int
    {
        if (function_exists('mb_strlen')) {
            return mb_strlen($texto, 'UTF-8');
        }

        return strlen($texto);
    }

    /**
     * Limita los códigos permitidos en la respuesta HTTP.
     */
    private function normalizarCodigoHttp(int $codigo): int
    {
        $codigosPermitidos = [
            400,
            401,
            429,
            500,
            502,
            504,
        ];

        if (in_array($codigo, $codigosPermitidos, true)) {
            return $codigo;
        }

        return 502;
    }

    /**
     * Construye la respuesta interna para public/chat.php.
     *
     * @param array<string, mixed> $cuerpo
     * @return array{
     *     codigo: int,
     *     cuerpo: array<string, mixed>
     * }
     */
    private function respuesta(
        int $codigo,
        array $cuerpo
    ): array {
        return [
            'codigo' => $codigo,
            'cuerpo' => $cuerpo,
        ];
    }
}