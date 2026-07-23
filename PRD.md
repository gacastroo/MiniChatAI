# PRD — MiniChatGPT



## 1. Objetivo



MiniChatGPT es una aplicación web de chat conversacional que permite al usuario comunicarse con un modelo de lenguaje mediante la API de OpenRouter.



El proyecto tiene una finalidad docente y debe servir para practicar PHP 8, programación orientada a objetos, arquitectura MVC, sesiones, JavaScript con `fetch` e integración de APIs mediante cURL.



## 2. Alcance



### Incluido



\* Una única pantalla de conversación.

\* Envío de mensajes sin recargar la página.

\* Respuestas generadas mediante OpenRouter.

\* Historial de conversación almacenado en la sesión de PHP.

\* Diferenciación visual entre usuario y asistente.

\* Indicador de escritura mientras se espera la respuesta.

\* Botón para iniciar una conversación nueva.

\* Gestión de errores de configuración, conexión y API.

\* Configuración de la clave y del modelo mediante `.env`.



### Fuera de alcance



\* Registro e inicio de sesión de usuarios.

\* Base de datos.

\* Historial permanente entre sesiones.

\* Subida de archivos o imágenes.

\* Generación de imágenes.

\* Respuestas por voz.

\* Aplicación móvil.

\* Panel de administración.

\* Uso de frameworks PHP o JavaScript.

\* Instalación de dependencias mediante Composer o npm.

\* Streaming de respuestas palabra por palabra.



## 3. Stack y restricciones



\* PHP 8.

\* Programación orientada a objetos.

\* Patrón MVC.

\* HTML5.

\* Tailwind CSS cargado mediante CDN.

\* JavaScript nativo.

\* `fetch` para la comunicación entre navegador y servidor.

\* cURL nativo de PHP para llamar a OpenRouter.

\* Sesiones de PHP para conservar el historial.

\* Sin frameworks.

\* Sin Composer.

\* Sin base de datos.

\* Sin librerías JavaScript externas.

\* Solo la carpeta `public/` puede exponerse mediante el servidor web.

\* La clave de API vive en `.env` y solo se lee desde el servidor.

\* El navegador nunca conoce la clave ni llama a OpenRouter directamente.

\* El modelo se cambia modificando una sola variable de configuración.



## 4. Requisitos funcionales



### RF1. Pantalla de chat



La aplicación debe mostrar una pantalla con:



\* Zona de historial.

\* Campo de texto.

\* Botón de enviar.

\* Botón de nueva conversación.



### RF2. Envío sin recarga



El usuario debe poder enviar un mensaje mediante `fetch` sin recargar la página.



### RF3. Visualización inmediata



El mensaje del usuario debe aparecer en la conversación inmediatamente después de enviarlo.



### RF4. Respuesta del asistente



PHP debe enviar el historial a OpenRouter mediante cURL y devolver al navegador el texto generado por el modelo.



### RF5. Diferenciación visual



Los mensajes del usuario y del asistente deben diferenciarse mediante alineación y color.



### RF6. Estado de espera



Mientras OpenRouter genera una respuesta, la interfaz debe:



\* Mostrar un indicador de escritura.

\* Deshabilitar temporalmente el campo.

\* Deshabilitar temporalmente el botón de envío.



### RF7. Uso del teclado



\* `Enter` debe enviar el mensaje.

\* `Mayús + Enter` debe insertar un salto de línea.



### RF8. Historial de conversación



El modelo debe recibir los mensajes anteriores de la conversación para mantener el contexto.



El historial debe almacenarse en `$\_SESSION`.



### RF9. Límite del historial



La conversación debe conservar:



\* Un mensaje inicial con rol `system`.

\* Como máximo los veinte mensajes más recientes del usuario y del asistente.



### RF10. Nueva conversación



El botón Nueva conversación debe:



\* Borrar el historial de la sesión.

\* Vaciar visualmente la zona de mensajes.

\* Mantener la aplicación preparada para seguir conversando.



### RF11. Validación de mensajes



El servidor debe rechazar mensajes:



\* Vacíos.

\* Formados únicamente por espacios.

\* Con más de 2000 caracteres.



### RF12. Configuración del modelo



El identificador del modelo debe leerse desde:



```text

OPENROUTER\_MODEL

```



en el archivo `.env`.



Debe ser posible cambiar de modelo modificando únicamente esa variable.



### RF13. Seguridad de la clave



La clave de OpenRouter debe leerse desde:



```text

OPENROUTER\_API\_KEY

```



La clave nunca debe:



\* Aparecer en JavaScript.

\* Enviarse al navegador.

\* Escribirse dentro de una clase PHP.

\* Subirse al repositorio Git.



### RF14. Escapado seguro



Todo contenido recibido desde el usuario o desde el modelo debe mostrarse usando `textContent` o un mecanismo equivalente de escapado.



No se permite insertar contenido del modelo directamente mediante `innerHTML`.



## 5. Requisitos no funcionales



### RNF1. Tiempo de espera



El cliente de OpenRouter debe usar:



\* Tiempo máximo de conexión: 5 segundos.

\* Tiempo máximo total: 30 segundos.



### RNF2. Gestión de errores



La aplicación debe mostrar mensajes comprensibles en los siguientes casos:



\* Falta la API key.

\* API key incorrecta.

\* Error de conexión.

\* Tiempo de espera agotado.

\* Límite de peticiones alcanzado.

\* OpenRouter devuelve un código HTTP de error.

\* OpenRouter devuelve JSON no válido.

\* La respuesta no contiene texto.

\* El mensaje está vacío.

\* El mensaje supera la longitud máxima.



### RNF3. Seguridad



\* `.env` debe aparecer en `.gitignore`.

\* Solo PHP puede leer la clave.

\* No se debe desactivar la verificación SSL.

\* No deben mostrarse trazas internas de PHP al usuario.



### RNF4. Legibilidad



\* Código y comentarios en español.

\* Una clase por archivo.

\* Tipado explícito.

\* `declare(strict\_types=1)` en todos los archivos PHP.

\* Métodos con una responsabilidad clara.



### RNF5. Accesibilidad básica



\* El campo debe recuperar el foco después de cada respuesta.

\* Los botones deben mostrar claramente su función.

\* Los errores deben distinguirse visualmente.

\* La conversación debe desplazarse automáticamente al último mensaje.



## 6. Estructura de archivos



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



No se deben crear archivos fuera de esta estructura sin autorización.



## 7. Contrato de la API interna



### Enviar un mensaje



```http

POST /chat.php

Content-Type: application/json

```



Petición:



```json

{

&#x20; "mensaje": "Texto escrito por el usuario"

}

```



Respuesta correcta:



```json

{

&#x20; "ok": true,

&#x20; "respuesta": "Texto generado por el modelo"

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



### Códigos HTTP



\* `200`: operación correcta.

\* `400`: petición no válida.

\* `401`: clave de OpenRouter incorrecta.

\* `429`: límite de peticiones alcanzado.

\* `500`: error interno o de configuración.

\* `502`: respuesta inválida del proveedor.

\* `504`: tiempo de espera agotado.



## 8. Estado y sesión



El historial debe guardarse en:



```php

$\_SESSION

```



La clase `Conversacion` será la única responsable de gestionar el historial.



Debe proporcionar estos métodos:



```php

anadirUsuario(string $mensaje): void

anadirAsistente(string $mensaje): void

obtenerMensajes(): array

reiniciar(): void

```



El historial comenzará siempre con un mensaje de sistema que indique que el asistente debe responder en español de forma clara, útil y honesta.



Cuando se supere el límite, deben eliminarse los mensajes más antiguos, conservando siempre el mensaje de sistema.



## 9. Criterios de aceptación



### CA1. Inicio



Al ejecutar:



```powershell

php -S localhost:8000 -t public

```



y abrir:



```text

http://localhost:8000

```



debe mostrarse la pantalla del chat.



### CA2. Envío



Al escribir un mensaje y pulsar Enviar:



\* La página no se recarga.

\* El mensaje aparece inmediatamente.

\* Se muestra un indicador de espera.

\* Finalmente aparece la respuesta.



### CA3. Memoria



Después de enviar un mensaje, el usuario puede preguntar qué dijo anteriormente y el modelo debe poder responder usando el historial.



### CA4. Reinicio



Al pulsar Nueva conversación:



\* Desaparecen los mensajes.

\* Se borra la sesión de conversación.

\* El modelo deja de recordar la conversación anterior.



### CA5. Seguridad de la clave



La API key:



\* No aparece en el código JavaScript.

\* No aparece en las herramientas de red del navegador.

\* No aparece en `git status`.

\* No aparece en `git ls-files`.



### CA6. Configuración ausente



Si falta `.env` o la clave está vacía, la aplicación muestra un error comprensible y no una traza de PHP.



### CA7. Clave incorrecta



Si la clave no es válida, la interfaz muestra un mensaje indicando que la autenticación ha fallado.



### CA8. Límite de peticiones



Si OpenRouter devuelve un error 429, la interfaz sugiere esperar unos segundos antes de volver a intentarlo.



### CA9. Mensaje vacío



Un mensaje vacío o compuesto solamente por espacios no debe enviarse a OpenRouter.



### CA10. Mensaje demasiado largo



Un mensaje de más de 2000 caracteres debe rechazarse con un mensaje comprensible.



### CA11. Cambio de modelo



Al modificar únicamente `OPENROUTER\_MODEL` en `.env`, la aplicación debe utilizar el nuevo modelo.



### CA12. Escapado



Si el usuario o el modelo devuelve código HTML, este debe mostrarse como texto y nunca ejecutarse.



## 10. Plan de fases



### Fase A — Andamiaje MVC



\* Crear la estructura de carpetas.

\* Crear el autocargador.

\* Implementar la lectura de `.env`.

\* Crear la vista estática del chat.

\* Declarar las clases principales sin conexión real a OpenRouter.



Resultado esperado: la pantalla se visualiza, pero todavía no conversa.



### Fase B — Cliente cURL, sesión y controlador



\* Implementar `ClienteLLM`.

\* Implementar `Conversacion`.

\* Implementar `ChatController`.

\* Implementar el endpoint `public/chat.php`.

\* Probar el endpoint mediante cURL.



Resultado esperado: el servidor puede conversar con OpenRouter aunque la interfaz todavía no use `fetch`.



### Fase C — Interfaz



\* Implementar `public/assets/app.js`.

\* Enviar mensajes mediante `fetch`.

\* Mostrar mensajes y errores.

\* Mostrar el indicador de escritura.

\* Implementar Enter y Mayús + Enter.

\* Implementar Nueva conversación.

\* Aplicar desplazamiento automático.



Resultado esperado: la aplicación puede utilizarse completamente desde el navegador.



### Fase D — Endurecido y documentación



\* Revisar todos los errores.

\* Comprobar la seguridad de la clave.

\* Completar `README.md`.

\* Comprobar `.env.example`.

\* Ejecutar las pruebas de aceptación.

\* Documentar limitaciones conocidas.



Resultado esperado: aplicación terminada, documentada y preparada para entregarse.



