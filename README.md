# MiniChatGPT

MiniChatGPT es una aplicación web de chat conversacional que se comunica con un modelo de lenguaje a través de la API de OpenRouter. Proyecto docente construido con PHP 8, POO y MVC, sin frameworks, sin Composer y sin base de datos.

## Captura

```
┌───────────────────────────────────────────────┐
│  MiniChatGPT                     [Nueva conv] │
│  Chat construido con PHP y OpenRouter         │
├───────────────────────────────────────────────┤
│                                               │
│  ┌──────────────────────────────────┐         │
│  │ Asistente                        │         │
│  │ Hola. Soy MiniChatGPT.           │         │
│  └──────────────────────────────────┘         │
│              ┌────────────────────────────┐   │
│              │ Tú                        │   │
│              │ ¿Qué puedes hacer?        │   │
│              └────────────────────────────┘   │
│  ┌──────────────────────────────────┐         │
│  │ Asistente                        │         │
│  │ Puedo responder preguntas,       │         │
│  │ ayudarte con código, etc.       │         │
│  └──────────────────────────────────┘         │
│                                               │
├───────────────────────────────────────────────┤
│ [Escribe un mensaje...]           [Enviar]   │
│ Escribiendo…                                 │
└───────────────────────────────────────────────┘
```

## Funcionalidades

- **Envío sin recarga**: los mensajes se envían con `fetch` sin recargar la página.
- **Burbuja inmediata**: el mensaje del usuario aparece al instante, sin esperar la respuesta.
- **Indicador de escritura**: muestra "Escribiendo…" mientras OpenRouter genera la respuesta.
- **Enter y Mayús+Enter**: Enter envía; Mayús+Enter inserta un salto de línea.
- **Nueva conversación**: un botón reinicia el historial de la sesión.
- **Errores visibles**: los errores de red, API o validación se muestran como burbujas rojas.
- **Historial en sesión**: la conversación se conserva en `$_SESSION` (máximo 20 mensajes + sistema).
- **Escapado seguro**: todo texto se inserta con `textContent`, nunca con `innerHTML`.

## Stack

| Capa | Tecnología |
|------|-----------|
| Backend | PHP 8, POO, MVC |
| Frontend | HTML5, Tailwind CSS (CDN), JavaScript nativo |
| API | OpenRouter (`https://openrouter.ai/api/v1/chat/completions`) |
| HTTP | cURL nativo de PHP |
| Comunicación | `fetch` (JSON) |
| Sesión | `$_SESSION` de PHP |
| Dependencias | Ninguna (sin Composer, sin npm, sin frameworks) |

## Estructura del proyecto

```
minichatgpt/
├── public/
│   ├── index.php          # Punto de entrada (renderiza la vista)
│   ├── chat.php           # Endpoint JSON para las peticiones AJAX
│   └── assets/
│       └── app.js         # Lógica del lado del cliente (fetch, DOM)
├── app/
│   ├── Config/
│   │   └── Config.php     # Lectura del archivo .env
│   ├── Controllers/
│   │   └── ChatController.php  # Valida entrada, coordina Modelo y Servicio
│   ├── Models/
│   │   └── Conversacion.php    # Gestiona el historial en $_SESSION
│   ├── Services/
│   │   └── ClienteLLM.php     # Llama a OpenRouter con cURL
│   └── Views/
│       └── chat.view.php      # Plantilla HTML de la interfaz
├── scripts/
│   └── backup.ps1         # Script de copia de seguridad (Windows)
├── .env                   # Clave de API y modelo (NO subir a Git)
├── .env.example           # Plantilla para .env
├── .gitignore
├── AGENTS.md              # Instrucciones para asistentes de IA
├── PRD.md                 # Documento de requisitos
└── README.md              # Este archivo
```

## Requisitos

- PHP 8.0 o superior
- Extensión cURL habilitada
- Navegador web moderno
- Conexión a Internet
- Cuenta en [OpenRouter](https://openrouter.ai) con una clave de API

## Instalación

### 1. Clonar o copiar el proyecto

```bash
git clone <url-del-repositorio>
cd minichatgpt
```

O descarga los archivos y colócalos en `C:\xampp\htdocs\minichatgpt` si usas XAMPP.

### 2. Configurar el archivo .env

Copia el archivo de ejemplo:

```bash
cp .env.example .env
```

Edita `.env` y completa los valores:

```env
OPENROUTER_API_KEY=sk-or-tu-clave-aqui
OPENROUTER_MODEL=openrouter/free
```

> La clave se obtiene en [https://openrouter.ai/keys](https://openrouter.ai/keys).  
> El archivo `.env` está en `.gitignore` y nunca debe subirse al repositorio.

### 3. Iniciar el servidor

```bash
php -S localhost:8000 -t public
```

La opción `-t public` expone únicamente la carpeta `public/`.

### 4. Abrir la aplicación

Visita [http://localhost:8000](http://localhost:8000) en el navegador.

## Cambiar de modelo

Edita `OPENROUTER_MODEL` en `.env`. No hace falta modificar ningún otro archivo.

```env
OPENROUTER_MODEL=nvidia/nemotron-3-ultra:free
```

Modelos gratuitos recomendados (julio 2026):
- `openrouter/free` — enrutador automático
- `nvidia/nemotron-3-ultra:free` — 1M de contexto, potente
- `google/gemma-4-26b-a4b:free` — rápido y fiable
- `openai/gpt-oss-20b:free` — versátil

## Probar con curl

```bash
curl -X POST http://localhost:8000/chat.php \
  -H "Content-Type: application/json" \
  -d '{"mensaje":"Hola"}'
```

Respuesta esperada:

```json
{"ok":true,"respuesta":"¡Hola! ¿En qué puedo ayudarte?"}
```

## Contrato de la API interna

### Enviar un mensaje

```
POST /chat.php
Content-Type: application/json

{"mensaje": "Texto del usuario"}
```

Respuesta correcta:
```json
{"ok": true, "respuesta": "Texto generado por el modelo"}
```

Respuesta con error:
```json
{"ok": false, "error": "Mensaje de error comprensible"}
```

### Reiniciar la conversación

```
POST /chat.php
Content-Type: application/json

{"accion": "reiniciar"}
```

Respuesta:
```json
{"ok": true}
```

### Códigos HTTP

| Código | Significado |
|--------|-------------|
| 200 | Operación correcta |
| 400 | Petición no válida |
| 401 | Clave de OpenRouter incorrecta |
| 429 | Límite de peticiones alcanzado |
| 500 | Error interno o de configuración |
| 502 | Respuesta inválida del proveedor |
| 504 | Tiempo de espera agotado |

## Manejo de errores

La aplicación muestra mensajes comprensibles en estos casos:

- Falta la clave de API o el archivo `.env`
- Clave de API incorrecta (401)
- Límite de peticiones alcanzado (429) — sugiere esperar unos segundos
- Tiempo de espera agotado (504)
- Error de conexión con OpenRouter
- OpenRouter devuelve JSON no válido
- La respuesta del modelo está vacía o malformada
- El mensaje del usuario está vacío
- El mensaje del usuario supera los 2000 caracteres

## Limitaciones conocidas

- El historial solo dura lo que dura la sesión de PHP. Al cerrar el navegador se pierde.
- No hay base de datos ni historial permanente entre sesiones.
- No hay streaming palabra por palabra: la respuesta llega completa.
- No se admiten archivos, imágenes ni Markdown enriquecido.
- Los modelos gratuitos de OpenRouter pueden estar saturados (error 429).
- La velocidad de respuesta depende del proveedor que OpenRouter seleccione.

## Autor

Realizado por **Guillermo Castro Abarca**.
