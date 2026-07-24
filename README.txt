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

El selector excluye modelos de seguridad/moderación, embeddings,
reranking, clasificación, OCR y voz. Solo conserva modelos gratuitos
con entrada y salida de texto y parámetros normales de generación.

Prioridad:
- latency: menor tiempo hasta empezar la respuesta.
- throughput: más tokens generados por segundo.
