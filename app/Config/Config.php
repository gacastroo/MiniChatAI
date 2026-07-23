<?php

declare(strict_types=1);

namespace App\Config;

use RuntimeException;

final class Config
{
    /**
     * Variables cargadas desde el archivo .env.
     *
     * @var array<string, string>|null
     */
    private static ?array $variables = null;

    /**
     * Obtiene una variable de configuración.
     */
    public static function obtener(string $clave): string
    {
        self::cargar();

        $valor = self::$variables[$clave] ?? '';

        if ($valor === '') {
            throw new RuntimeException(
                sprintf(
                    'Falta la variable de configuración "%s" en el archivo .env.',
                    $clave
                )
            );
        }

        return $valor;
    }

    /**
     * Carga el archivo .env una sola vez.
     */
    private static function cargar(): void
    {
        if (self::$variables !== null) {
            return;
        }

        $rutaEnv = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . '.env';

        if (!is_file($rutaEnv)) {
            throw new RuntimeException(
                'No se encontró el archivo .env en la raíz del proyecto.'
            );
        }

        $lineas = file(
            $rutaEnv,
            FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES
        );

        if ($lineas === false) {
            throw new RuntimeException(
                'No se pudo leer el archivo .env.'
            );
        }

        self::$variables = [];

        foreach ($lineas as $linea) {
            $linea = trim($linea);

            if ($linea === '' || str_starts_with($linea, '#')) {
                continue;
            }

            if (!str_contains($linea, '=')) {
                continue;
            }

            [$clave, $valor] = explode('=', $linea, 2);

            $clave = trim($clave);
            $valor = trim($valor);

            if (
                strlen($valor) >= 2 &&
                (
                    ($valor[0] === '"' && $valor[strlen($valor) - 1] === '"') ||
                    ($valor[0] === "'" && $valor[strlen($valor) - 1] === "'")
                )
            ) {
                $valor = substr($valor, 1, -1);
            }

            if ($clave !== '') {
                self::$variables[$clave] = $valor;
            }
        }
    }
}