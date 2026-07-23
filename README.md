# MiniChatGPT

MiniChatGPT es una aplicación web de chat conversacional construida con PHP 8, programación orientada a objetos y patrón MVC.

La aplicación se conecta a un modelo de lenguaje mediante la API de OpenRouter. El historial de la conversación se almacena en la sesión de PHP y la interfaz se comunica con el servidor mediante JavaScript y `fetch`.

## Tecnologías utilizadas

- PHP 8.

- Programación orientada a objetos.

- Patrón MVC.

- Sesiones de PHP.

- cURL.

- HTML5.

- Tailwind CSS mediante CDN.

- JavaScript nativo.

- API de OpenRouter.

- Git.

No se utilizan frameworks, Composer, npm ni base de datos.

## Requisitos

Para ejecutar el proyecto se necesita:

- PHP 8.0 o superior.

- Extensión cURL habilitada en PHP.

- Navegador web moderno.

- Conexión a Internet.

- Cuenta de OpenRouter.

- Clave de API válida de OpenRouter.

- Modelo gratuito disponible en OpenRouter.

Para comprobar la versión de PHP:

```powershell

php --version

```

Para comprobar que cURL está habilitado:

```powershell

php -m | Select-String curl

```

Debe aparecer:

```text

curl

```

## Instalación

### 1. Descargar o clonar el proyecto

Sitúate en la carpeta del proyecto:

```powershell

cd C:\\Users\\manana\\Desktop\\minichatgpt

```

### 2. Crear el archivo `.env`

Copia `.env.example`:

```powershell

Copy-Item .env.example .env

```

Abre el archivo:

```powershell

notepad .env

```

Configura las variables:

```env

OPENROUTER\_API\_KEY=sk-or-TU\_CLAVE

OPENROUTER\_MODEL=openrouter/free

```

La clave real debe almacenarse exclusivamente en `.env`.

El archivo `.env` está incluido en `.gitignore` y no debe subirse al repositorio.

### 3. Iniciar el servidor

Desde la raíz del proyecto:

```powershell

php -S 127.0.0.1:8001 -t public

```

La opción:

```text

\-t public

```

hace que solamente la carpeta `public/` quede expuesta al navegador.

### 4. Abrir la aplicación

Abre esta dirección:

```text

http://127.0.0.1:8001

```

Si el navegador conserva una versión antigua del JavaScript, realiza una recarga completa:

```text

Ctrl + F5

```

## Uso

1\. Escribe un mensaje en el campo de texto.

2\. Pulsa el botón \*\*Enviar\*\* o la tecla `Enter`.

3\. Usa `Mayús + Enter` para insertar un salto de línea.

4\. Espera la respuesta del modelo.

5\. Pulsa \*\*Nueva conversación\*\* para borrar el historial de la sesión.

Durante la petición:

- El mensaje del usuario aparece inmediatamente.

- Se muestra el estado `Escribiendo…`.

- El campo y los botones quedan temporalmente deshabilitados.

- El campo recupera el foco cuando termina la petición.

## Cambiar de modelo

El modelo se configura en `.env`:

```env

OPENROUTER\_MODEL=openrouter/free

```

Para utilizar otro modelo, modifica únicamente el valor de `OPENROUTER\_MODEL`.

Ejemplo:

```env

OPENROUTER\_MODEL=identificador-del-modelo:free

```

El identificador debe coincidir exactamente con uno de los modelos disponibles en OpenRouter.

Después de modificar `.env`, recarga la aplicación.

## Estructura

```text

minichatgpt/

├─ public/

│  ├─ index.php

│  ├─ chat.php

│  └─ assets/

│     └─ app.js

├─ app/

│  ├─ Config/

│  │  └─ Config.php

│  ├─ Controllers/

│  │  └─ ChatController.php

│  ├─ Models/

│  │  └─ Conversacion.php

│  ├─ Services/

│  │  └─ ClienteLLM.php

│  └─ Views/

│     └─ chat.view.php

├─ .env

├─ .env.example

├─ .gitignore

├─ AGENTS.md

├─ PRD.md

└─ README.md

```

## Arquitectura

### `Config`

Lee las variables del archivo `.env` y permite obtener la clave y el modelo sin escribirlos directamente en el código.

### `Conversacion`

Gestiona el historial almacenado en `$\_SESSION`.

Conserva:

- Un mensaje inicial con rol `system`.

- Los veinte mensajes más recientes del usuario y del asistente.

### `ClienteLLM`

Realiza la petición HTTP a OpenRouter mediante cURL.

Gestiona:

- Fallos de conexión.

- Tiempo de espera agotado.

- Clave incorrecta.

- Límite de peticiones.

- Respuestas JSON no válidas.

- Respuestas vacías o malformadas.

### `ChatController`

Valida la petición, coordina la conversación y el cliente de OpenRouter y devuelve una respuesta estructurada.

### `public/chat.php`

Es el endpoint JSON utilizado por la interfaz.

### `public/assets/app.js`

Gestiona:

- Envío mediante `fetch`.

- Burbuja inmediata del usuario.

- Indicador de espera.

- Bloqueo temporal del formulario.

- Mensajes de error.

- Reinicio de la conversación.

- Envío con `Enter`.

- Salto de línea con `Mayús + Enter`.

- Desplazamiento automático.

## Contrato de la API interna

### Enviar un mensaje

Petición:

```json

{

&#x20; "mensaje": "Hola"

}

```

Respuesta correcta:

```json

{

&#x20; "ok": true,

&#x20; "respuesta": "Hola, ¿en qué puedo ayudarte?"

}

```

Respuesta con error:

```json

{

&#x20; "ok": false,

&#x20; "error": "Mensaje comprensible para el usuario"

}

```

### Reiniciar la conversación

Petición:

```json

{

&#x20; "accion": "reiniciar"

}

```

Respuesta:

```json

{

&#x20; "ok": true

}

```

## Seguridad

- La clave vive exclusivamente en `.env`.

- `.env` está ignorado por Git.

- La clave nunca se envía al navegador.

- JavaScript nunca llama directamente a OpenRouter.

- OpenRouter se consulta únicamente desde PHP.

- No se desactiva la verificación SSL.

- El contenido recibido se inserta mediante `textContent`.

- El texto del modelo no se introduce mediante `innerHTML`.

## Errores gestionados

La aplicación muestra mensajes comprensibles cuando:

- Falta `.env`.

- Falta la clave de OpenRouter.

- El modelo no está configurado.

- La clave no es válida.

- No existe conexión con OpenRouter.

- Se agota el tiempo de espera.

- Se alcanza el límite de peticiones.

- OpenRouter devuelve un error HTTP.

- La respuesta contiene JSON no válido.

- La respuesta no contiene texto.

- El mensaje está vacío.

- El mensaje supera los 2000 caracteres.

## Limitaciones conocidas

- Los modelos gratuitos pueden estar saturados.

- OpenRouter puede devolver errores `429`.

- Los modelos gratuitos pueden cambiar o desaparecer.

- El historial solo permanece durante la sesión de PHP.

- No existe almacenamiento en base de datos.

- Al cerrar o perder la sesión se pierde la conversación.

- No hay streaming palabra por palabra.

- No se admite Markdown enriquecido.

- No se pueden adjuntar archivos ni imágenes.

- La disponibilidad y velocidad dependen del proveedor seleccionado por OpenRouter.

## Pruebas

Comprobar todos los archivos PHP:

```powershell

Get-ChildItem -Recurse -Filter \*.php | ForEach-Object {

&#x20;   php -l $\_.FullName

}

```

Comprobar que `.env` no está controlado por Git:

```powershell

git status --short

git ls-files .env

```

El segundo comando no debe mostrar ningún resultado.

Probar la memoria:

1\. Envía un dato al asistente.

2\. Pregunta qué dato acabas de proporcionar.

3\. Comprueba que lo recuerda.

4\. Pulsa \*\*Nueva conversación\*\*.

5\. Pregunta de nuevo.

6\. Comprueba que ya no recuerda la conversación anterior.

## Licencia y finalidad

Este proyecto tiene finalidad educativa y forma parte de un ejercicio de integración de PHP con OpenRouter.

