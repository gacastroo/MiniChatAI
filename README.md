# MiniChatAI

Chat con modelos de lenguaje gratuitos via [OpenRouter](https://openrouter.ai). Proyecto docente de PHP 8 con programacion orientada a objetos y MVC sin frameworks.

## Arquitectura

```
PHP 8 + POO + MVC
Sin frameworks, sin Composer, sin base de datos.
```

- **PHP 8** con tipado estricto (`declare(strict_types=1)`)
- **POO**: una clase por archivo, nombre de archivo = nombre de clase
- **MVC**: separacion en controlador (`api.php`), modelos (`ClienteLLM.php`, `SelectorModelosOpenRouter.php`) y vista (`index.php`)
- **Autocarga**: funcion `spl_autoload_register` propia (sin Composer)
- **Namespaces**: `App\Config`, `App\Controllers`, `App\Models`, `App\Services`
- Solo la carpeta `public/` se expone al servidor web

## Requisitos

- PHP 8.0 o superior con extension cURL
- Clave de API de [OpenRouter](https://openrouter.ai/keys)

## Instalacion

### 1. Clonar el repositorio

```bash
git clone https://github.com/gacastroo/MiniChatAI.git
cd MiniChatAI
```

### 2. Configurar la clave de API

Abre `config.php` y busca la linea:

```php
define('OPENROUTER_API_KEY', 'PEGA_AQUI_TU_NUEVA_CLAVE');
```

Pega una **clave nueva** de OpenRouter entre las comillas. No reutilices ninguna clave que hayas publicado anteriormente.

### 3. (Opcional) Limpiar cache

Si existe el archivo `cache_modelos_openrouter.json`, borralo para forzar una carga fresca del catalogo de modelos.

### 4. Iniciar el servidor

```bash
php -S 127.0.0.1:8000
```

### 5. Abrir en el navegador

```
http://127.0.0.1:8000
```

## Estructura del proyecto

| Archivo | Funcion |
|---|---|
| `index.php` | Vista: interfaz de chat en HTML+CSS+JS |
| `api.php` | Controlador: recibe peticiones AJAX y orquesta la logica |
| `config.php` | Configuracion: clave API, URLs, preferencias |
| `ClienteLLM.php` | Modelo: envia mensajes a OpenRouter y procesa la respuesta |
| `SelectorModelosOpenRouter.php` | Modelo: consulta el catalogo y filtra modelos gratuitos |
| `.gitignore` | Archivos excluidos del repositorio |
| `README.md` | Este documento |

## Configuracion

Todas las constantes se definen en `config.php`:

| Constante | Descripcion | Valor por defecto |
|---|---|---|
| `OPENROUTER_API_KEY` | Clave de API de OpenRouter | `PEGA_AQUI_TU_NUEVA_CLAVE` |
| `OPENROUTER_CHAT_URL` | Endpoint de chat | `https://openrouter.ai/api/v1/chat/completions` |
| `OPENROUTER_MODELS_URL` | Endpoint del catalogo | `https://openrouter.ai/api/v1/models` |
| `OPENROUTER_APP_URL` | URL de la app (referer) | `http://127.0.0.1:8000` |
| `OPENROUTER_APP_NOMBRE` | Nombre mostrado a OpenRouter | `Chat IA local` |
| `OPENROUTER_PRIORIDAD` | Criterio de seleccion | `latency` |
| `OPENROUTER_MAX_MODELOS` | Max. modelos a enviar | `3` |
| `OPENROUTER_CACHE_ARCHIVO` | Ruta del archivo de cache | `__DIR__ . '/cache_modelos_openrouter.json'` |
| `OPENROUTER_CACHE_SEGUNDOS` | TTL de cache en segundos | `3600` |
| `LLM_TEMPERATURA` | Creatividad del modelo (0-2) | `0.7` |
| `LLM_MAX_HISTORIAL` | Max. mensajes en contexto | `20` |
| `LLM_SYSTEM` | Prompt del sistema | Texto en espanol |

## Como funciona

### Flujo de una peticion

```
1. Usuario escribe un mensaje en index.php
2. JS lo envia via fetch POST a api.php
3. api.php:
   a. Valida la entrada y la clave API
   b. Inicializa/carga el historial de la sesion
   c. Agrega el mensaje del usuario al historial
   d. Obtiene los modelos disponibles (cache o API)
   e. Libera el bloqueo de sesion
   f. Envia el historial a OpenRouter via ClienteLLM
   g. Recibe la respuesta y la guarda en sesion
4. JS muestra la respuesta en el chat
```

### Seleccion de modelos

Al cargar la pagina, el frontend llama a `api.php?accion=listar_modelos` para obtener los modelos gratuitos disponibles. El backend:

1. Consulta el catalogo de OpenRouter con filtros de precio gratuito y modalidad texto
2. Filtra localmente modelos auxiliares (seguridad, embeddings, reranking, OCR, voz)
3. Cachea el resultado en sesion y en archivo
4. Devuelve la lista al frontend

El usuario puede elegir entre:

- **Auto**: OpenRouter selecciona el modelo mas rapido segun la prioridad
- **Modelo concreto**: se fuerza ese modelo en todas las peticiones de la sesion

### Prioridad

Cuando se usa el modo "Auto", OpenRouter ordena los modelos segun:

| Prioridad | Comportamiento |
|---|---|
| `latency` | Menor tiempo hasta empezar la respuesta (recomendado) |
| `throughput` | Maximiza tokens generados por segundo |

### Filtro de modelos

El selector excluye automaticamente:

- Modelos de seguridad y moderacion (`content-safety`, `guardrail`, `moderation`)
- Clasificadores y detectores (`classifier`, `detector`)
- Embeddings (`embedding`, `embed model`)
- Reranking (`rerank`, `reranker`)
- Modelos de recompensa/score (`reward model`, `scoring model`, `judge model`)
- OCR y transcripcion (`ocr model`, `transcription model`)
- Voz (`speech-to-text`, `text-to-speech`)

### Historial de conversacion

El historial se guarda en la sesion PHP del usuario. Incluye un mensaje de sistema inicial y los ultimos `LLM_MAX_HISTORIAL` mensajes (por defecto 20). Cuando se supera el limite, se descartan los mensajes mas antiguos conservando el de sistema.

## Rendimiento

- **Cache de modelos en sesion**: los modelos disponibles se cachean en `$_SESSION` para evitar consultar el catalogo en cada mensaje
- **Cache de archivo**: respaldo persistente con TTL de 1 hora (`OPENROUTER_CACHE_SEGUNDOS`)
- **Liberacion de bloqueo de sesion**: durante la llamada HTTP a OpenRouter (la parte mas lenta), el bloqueo de sesion se libera para que otras pestanas del mismo usuario no se encolen
- **Payload minimo**: la respuesta JSON solo incluye los datos necesarios para el frontend

## Manejo de errores

- Los errores se propagan con excepciones (nunca `die()` ni `echo` directo)
- Si OpenRouter devuelve un error HTTP, se extrae el mensaje del cuerpo y se relanza como excepcion
- Si la peticion falla, se elimina el ultimo mensaje del usuario del historial para mantener la coherencia
- El frontend muestra los errores en una burbuja roja dentro del chat

## Seguridad

- La clave de API solo se escribe en `config.php`, nunca en el repositorio
- No se envia la clave al navegador ni se llama a OpenRouter desde JavaScript
- Todo texto del modelo se escapa antes de mostrarse (`textContent`, no `innerHTML`)
- El repositorio incluye un `.gitignore` que excluye `.env`, `vendor/`, archivos de cache y la clave

## Desarrollo

Este proyecto sigue las reglas definidas en `AGENTS.md` (fichero interno de trabajo con la IA):
- Codigo y comentarios en espanol
- Tipado explicito en parametros y retorno
- Prohibido escribir la clave en archivos que no sean `.env` o `config.php`
- Sin librerias externas ni herramientas de compilacion