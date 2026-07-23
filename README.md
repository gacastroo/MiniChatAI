# MiniChatGPT

Aplicación web de chat conversacional que se comunica con un modelo de lenguaje a través de la API de OpenRouter. Construida con PHP 8, POO y MVC. Sin frameworks, sin Composer, sin base de datos.

## Requisitos

- PHP 8.0 o superior
- Extensión cURL habilitada
- Navegador web moderno
- Conexión a Internet
- Cuenta en [OpenRouter](https://openrouter.ai) con una clave de API válida

## Instalación

### 1. Descargar el proyecto

```powershell
cd C:\xampp\htdocs
git clone <url-del-repositorio> minichatgpt
```

O copia los archivos manualmente dentro de `C:\xampp\htdocs\minichatgpt`.

### 2. Configurar el archivo .env

Copia el archivo de ejemplo:

```powershell
Copy-Item .env.example .env
```

Abre `.env` y completa los valores:

```env
OPENROUTER_API_KEY=sk-or-tu-clave-aqui
OPENROUTER_MODEL=openrouter/free
```

La clave de API se obtiene desde [https://openrouter.ai/keys](https://openrouter.ai/keys).  
El archivo `.env` está en `.gitignore` y nunca debe subirse al repositorio.

### 3. Iniciar el servidor

```powershell
php -S localhost:8000 -t public
```

La opción `-t public` hace que solo la carpeta `public/` quede expuesta al navegador.

### 4. Abrir la aplicación

Visita [http://localhost:8000](http://localhost:8000) en el navegador.

## Cambiar de modelo

Edita la variable `OPENROUTER_MODEL` en `.env`. Por ejemplo:

```env
OPENROUTER_MODEL=openrouter/free
```

El identificador debe coincidir exactamente con un modelo disponible en OpenRouter. No hace falta modificar ningún otro archivo.

## Limitaciones conocidas

- El historial solo dura lo que dura la sesión de PHP. Al cerrar el navegador se pierde.
- No hay almacenamiento en base de datos ni historial permanente entre sesiones.
- No hay streaming palabra por palabra: la respuesta llega completa.
- No se admiten archivos, imágenes ni Markdown enriquecido.
- Los modelos gratuitos de OpenRouter pueden estar saturados y responder con error 429.
- La velocidad de respuesta depende del proveedor que OpenRouter seleccione.
