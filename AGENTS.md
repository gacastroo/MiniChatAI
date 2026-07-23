# AGENTS.md — MiniChatGPT

## Contexto

Proyecto docente. El objetivo es que el codigo sea legible y explicable
por un alumno de segundo curso, no que sea ingenioso.

## Arquitectura

- PHP 8, POO y MVC. Sin frameworks. Sin Composer. Sin base de datos.
- Una clase por archivo. Nombre de archivo = nombre de clase.
- Namespaces App\Config, App\Controllers, App\Models, App\Services.
- Autocarga con una funcion spl_autoload_register propia.
- Solo la carpeta public/ se expone al servidor web.

## Reglas de codigo

- Codigo y comentarios en espanol. Nombres de clases y metodos en espanol.
- declare(strict_types=1) en todos los archivos PHP.
- Tipado explicito en parametros y valores de retorno.
- Los errores se propagan con excepciones, nunca con die() ni echo.
- Ninguna sentencia echo fuera de las vistas.
- Todo texto que venga del modelo se escapa antes de mostrarse.

## Prohibido

- Escribir la clave de API en cualquier archivo que no sea .env
- Enviar la clave al navegador o llamar a OpenRouter desde JavaScript.
- Anadir librerias, dependencias o herramientas de compilacion.
- Modificar PRD.md sin que yo lo pida.
- Crear archivos que no esten en la estructura definida en el PRD.

## Forma de trabajar

- Trabajamos por fases. No adelantes trabajo de fases posteriores.
- Antes de escribir codigo, resume en 3 lineas lo que vas a hacer.
- Al terminar, indica que archivos has tocado y como probar el resultado.
- Si una instruccion mia contradice el PRD, para y preguntame.
