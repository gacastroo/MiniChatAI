# Chat IA con OpenRouter

Chat con modelos gratuitos conversacionales via OpenRouter.

## Requisitos

- PHP 8+ con extension cURL
- Clave de API de [OpenRouter](https://openrouter.ai)

## Instalacion

1. Abre `config.php`.
2. Busca la linea:
   ```php
   define('OPENROUTER_API_KEY', 'PEGA_AQUI_TU_NUEVA_CLAVE');
   ```
3. Pega una **clave nueva** de OpenRouter entre las comillas.
4. Borra `cache_modelos_openrouter.json` si existe.

## Uso

```powershell
php -S 127.0.0.1:8000
```

Abre `http://127.0.0.1:8000`.

## Selector de modelos

Al cargar la pagina se obtienen los modelos gratuitos disponibles. Puedes elegir entre:

- **Auto** (mas rapido disponible): OpenRouter selecciona el mejor segun la prioridad.
- **Un modelo concreto**: fuerza el uso de ese modelo en todos los mensajes de la sesion.

## Filtro de modelos

El selector excluye automaticamente modelos de:

- Seguridad/moderacion
- Embeddings
- Reranking y clasificacion
- OCR y voz

Solo conserva modelos gratuitos con entrada y salida de texto y parametros normales de generacion.

## Prioridad (config.php)

| Valor | Comportamiento |
|---|---|
| `latency` | Menor tiempo hasta empezar la respuesta |
| `throughput` | Mas tokens generados por segundo |

## Rendimiento

- Los modelos se cachean en sesion para evitar consultar el catalogo en cada mensaje.
- El bloqueo de sesion se libera durante la llamada HTTP a OpenRouter para que varias pestanas del mismo usuario no se encolen.
- Cache de archivo: 1 hora (configurable en `OPENROUTER_CACHE_SEGUNDOS`).