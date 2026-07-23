<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MiniChatGPT</title>
    <script>
        const warnOriginal = console.warn;
        console.warn = function () {
            if (arguments[0] && typeof arguments[0] === 'string' &&
                arguments[0].includes('should not be used in production')) {
                return;
            }
            warnOriginal.apply(console, arguments);
        };
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-gradient-to-b from-slate-50 to-slate-100 text-slate-900">
    <main class="mx-auto flex min-h-screen max-w-4xl flex-col px-4 py-6">
        <header class="mb-4 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-800">
                    MiniChatGPT
                </h1>
                <p class="text-sm text-slate-500">
                    Chat con IA mediante PHP y OpenRouter
                </p>
            </div>
            <button
                id="boton-reiniciar"
                type="button"
                class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-600 transition hover:border-slate-400 hover:bg-slate-50"
            >
                Nueva conversación
            </button>
        </header>

        <section
            id="mensajes"
            aria-live="polite"
            aria-label="Historial de conversación"
            class="flex flex-1 flex-col gap-4 overflow-y-auto rounded-2xl border border-slate-200 bg-white p-5 shadow-lg shadow-slate-200/50"
        >
            <article class="flex justify-start">
                <div class="max-w-[80%] rounded-2xl rounded-bl-md bg-slate-100 px-4 py-3">
                    <p class="text-sm font-semibold text-slate-600">
                        Asistente
                    </p>
                    <p class="mt-1 leading-relaxed">
                        ¡Hola! Soy MiniChatGPT, un chat conectado a un modelo de lenguaje a través de OpenRouter. Puedes preguntarme lo que quieras.
                    </p>
                </div>
            </article>
        </section>

        <div id="indicador-escribiendo" class="hidden mt-2 flex items-center gap-2 px-1 text-sm text-slate-500">
            <span class="h-2 w-2 animate-pulse rounded-full bg-slate-400"></span>
            <span class="h-2 w-2 animate-pulse rounded-full bg-slate-400" style="animation-delay: 0.2s"></span>
            <span class="h-2 w-2 animate-pulse rounded-full bg-slate-400" style="animation-delay: 0.4s"></span>
            <span>El asistente está escribiendo…</span>
        </div>

        <form
            id="formulario-chat"
            class="mt-4 flex gap-3"
        >
            <label for="campo-mensaje" class="sr-only">
                Escribe tu mensaje
            </label>

            <div class="relative flex-1">
                <textarea
                    id="campo-mensaje"
                    name="mensaje"
                    rows="2"
                    maxlength="2000"
                    placeholder="Escribe un mensaje..."
                    class="min-h-16 w-full resize-none rounded-xl border border-slate-300 bg-white px-4 py-3 pr-16 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                ></textarea>
                <span
                    id="contador-caracteres"
                    class="absolute bottom-2 right-3 text-xs text-slate-400"
                >0 / 2000</span>
            </div>

            <button
                id="boton-enviar"
                type="submit"
                class="flex items-center gap-2 self-end rounded-xl bg-blue-600 px-6 py-3 font-semibold text-white transition hover:bg-blue-700"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="22" y1="2" x2="11" y2="13"></line>
                    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                </svg>
                Enviar
            </button>
        </form>

        <p
            id="estado-chat"
            class="mt-2 min-h-5 text-sm text-red-500"
        ></p>
    </main>
    <script src="assets/app.js"></script>
</body>
</html>
