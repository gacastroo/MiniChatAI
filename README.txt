CHAT IA CON OPENROUTER — MODELOS GRATUITOS CONVERSACIONALES

1. Abre config.php.
2. Busca:
   define('OPENROUTER_API_KEY', 'PEGA_AQUI_TU_NUEVA_CLAVE');
3. Pega una CLAVE NUEVA de OpenRouter entre las comillas.
4. No reutilices ninguna clave que hayas publicado.
5. Borra cache_modelos_openrouter.json si existe.
6. Abre PowerShell en esta carpeta.
7. Ejecuta:
   php -S 127.0.0.1:8000
8. Abre:
   http://127.0.0.1:8000

SELECTOR DE MODELOS
Al cargar la página se obtienen los modelos gratuitos disponibles.
Puedes elegir entre:

- Auto (más rápido disponible): OpenRouter selecciona el mejor
  según la prioridad configurada (latency o throughput).
- Un modelo concreto: fuerza el uso de ese modelo en todos los
  mensajes de la sesión.

FILTRO DE MODELOS
El selector excluye modelos de seguridad/moderación, embeddings,
reranking, clasificación, OCR y voz. Solo conserva modelos gratuitos
con entrada y salida de texto y parámetros normales de generación.

PRIORIDAD (config.php)
- latency: menor tiempo hasta empezar la respuesta.
- throughput: más tokens generados por segundo.

RENDIMIENTO
- Los modelos se cachean en sesión para evitar consultar el catálogo
  de OpenRouter en cada mensaje.
- El bloqueo de sesión se libera durante la llamada HTTP a OpenRouter
  para que varias pestañas del mismo usuario no se encolen.
- Cache de archivo: 1 hora (configurable en OPENROUTER_CACHE_SEGUNDOS).