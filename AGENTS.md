# AGENTS.md — MiniChatGPT

## Contexto

Proyecto docente. El objetivo es que el código sea legible y explicable
por un alumno de segundo curso, no que sea ingenioso.

## Arquitectura

- PHP 8, POO y MVC.
- Sin frameworks.
- Sin Composer.
- Sin base de datos.
- Una clase por archivo.
- El nombre del archivo debe coincidir con el nombre de la clase.
- Namespaces:
  - App\Config
  - App\Controllers
  - App\Models
  - App\Services
- Autocarga mediante una función propia con spl_autoload_register.
- Solo la carpeta public/ puede exponerse al servidor web.

## Reglas de código

- Código y comentarios en español.
- Nombres de clases y métodos en español.
- Usar declare(strict_types=1) en todos los archivos PHP.
- Usar tipos explícitos en parámetros y valores de retorno.
- Los errores se propagan mediante excepciones.
- No utilizar die() para gestionar errores.
- Ninguna sentencia echo fuera de las vistas.
- Todo texto recibido del modelo debe escaparse antes de mostrarse.

## Prohibido

- Escribir la clave de API en cualquier archivo que no sea .env.
- Enviar la clave al navegador.
- Llamar a OpenRouter desde JavaScript.
- Añadir librerías o dependencias.
- Añadir herramientas de compilación.
- Modificar PRD.md sin autorización.
- Crear archivos que no estén en la estructura definida en PRD.md.

## Forma de trabajar

- El proyecto se construye por fases.
- No adelantar trabajo de fases posteriores.
- Antes de escribir código, resumir en tres líneas qué se va a hacer.
- Al terminar, indicar qué archivos se han modificado.
- Al terminar, explicar cómo probar el resultado.
- Si una instrucción contradice PRD.md, detenerse y solicitar revisión.
