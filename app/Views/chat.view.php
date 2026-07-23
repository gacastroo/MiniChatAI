<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>MiniChatGPT</title>

    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-slate-100 text-slate-900">
    <main class="mx-auto flex min-h-screen max-w-4xl flex-col px-4 py-6">
        <header class="mb-4 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">
                    MiniChatGPT
                </h1>

                <p class="text-sm text-slate-500">
                    Chat construido con PHP y OpenRouter
                </p>
            </div>

            <button
                id="boton-reiniciar"
                type="button"
                class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium hover:bg-slate-50"
            >
                Nueva conversación
            </button>
        </header>

        <section
            id="mensajes"
            aria-live="polite"
            aria-label="Historial de conversación"
            class="flex flex-1 flex-col gap-4 overflow-y-auto rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
        >
            <article class="flex justify-start">
                <div class="max-w-[80%] rounded-2xl rounded-bl-md bg-slate-200 px-4 py-3">
                    <p class="text-sm font-semibold text-slate-600">
                        Asistente
                    </p>

                    <p class="mt-1">
                        Hola. Soy MiniChatGPT. Todavía estoy en la fase de maquetación.
                    </p>
                </div>
            </article>

            <article class="flex justify-end">
                <div class="max-w-[80%] rounded-2xl rounded-br-md bg-blue-600 px-4 py-3 text-white">
                    <p class="text-sm font-semibold text-blue-100">
                        Tú
                    </p>

                    <p class="mt-1">
                        Perfecto, ya puedo ver cómo quedará la conversación.
                    </p>
                </div>
            </article>
        </section>

        <form
            id="formulario-chat"
            class="mt-4 flex gap-3"
        >
            <label for="campo-mensaje" class="sr-only">
                Escribe tu mensaje
            </label>

            <textarea
                id="campo-mensaje"
                name="mensaje"
                rows="2"
                maxlength="2000"
                placeholder="Escribe un mensaje..."
                class="min-h-16 flex-1 resize-none rounded-xl border border-slate-300 bg-white px-4 py-3 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
            ></textarea>

            <button
                id="boton-enviar"
                type="submit"
                class="rounded-xl bg-blue-600 px-6 py-3 font-semibold text-white hover:bg-blue-700"
            >
                Enviar
            </button>
        </form>

        <p
            id="estado-chat"
            class="mt-2 min-h-5 text-sm text-slate-500"
        ></p>
    </main>
    <script src="assets/app.js"></script>
</body>
</html>