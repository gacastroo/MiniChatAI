<?php

declare(strict_types=1);

session_start();

/**
 * Autocarga las clases del namespace App desde la carpeta app/.
 */
spl_autoload_register(
    static function (string $clase): void {
        $prefijo = 'App\\';

        if (!str_starts_with($clase, $prefijo)) {
            return;
        }

        $nombreRelativo = substr($clase, strlen($prefijo));

        $ruta = dirname(__DIR__)
            . DIRECTORY_SEPARATOR
            . 'app'
            . DIRECTORY_SEPARATOR
            . str_replace('\\', DIRECTORY_SEPARATOR, $nombreRelativo)
            . '.php';

        if (is_file($ruta)) {
            require_once $ruta;
        }
    }
);

require dirname(__DIR__)
    . DIRECTORY_SEPARATOR
    . 'app'
    . DIRECTORY_SEPARATOR
    . 'Views'
    . DIRECTORY_SEPARATOR
    . 'chat.view.php';