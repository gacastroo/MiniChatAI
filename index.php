<?php

declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >
    <title>Chat IA con OpenRouter</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: #f2f4f7;
            color: #1f2937;
            font-family: Arial, sans-serif;
        }

        .contenedor {
            width: min(900px, calc(100% - 32px));
            margin: 32px auto;
        }

        .cabecera {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 16px;
        }

        h1 {
            margin: 0;
            font-size: 24px;
        }

        button,
        textarea {
            font: inherit;
        }

        #reiniciar {
            border: 0;
            border-radius: 8px;
            padding: 10px 14px;
            cursor: pointer;
        }

        #chat {
            min-height: 500px;
            max-height: 65vh;
            overflow-y: auto;
            padding: 20px;
            border: 1px solid #d1d5db;
            border-radius: 12px;
            background: white;
        }

        .mensaje {
            max-width: 80%;
            margin-bottom: 14px;
            padding: 12px 14px;
            border-radius: 12px;
            white-space: pre-wrap;
            overflow-wrap: anywhere;
            line-height: 1.45;
        }

        .usuario {
            margin-left: auto;
            background: #dbeafe;
        }

        .asistente {
            margin-right: auto;
            background: #f3f4f6;
        }

        .error {
            margin-right: auto;
            color: #991b1b;
            background: #fee2e2;
        }

        .modelo {
            display: block;
            margin-top: 8px;
            color: #6b7280;
            font-size: 12px;
        }

        form {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 12px;
            margin-top: 16px;
        }

        textarea {
            min-height: 70px;
            max-height: 180px;
            padding: 12px;
            resize: vertical;
            border: 1px solid #d1d5db;
            border-radius: 10px;
        }

        #enviar {
            min-width: 110px;
            border: 0;
            border-radius: 10px;
            padding: 0 18px;
            color: white;
            background: #111827;
            cursor: pointer;
        }

        #enviar:disabled,
        #reiniciar:disabled {
            cursor: not-allowed;
            opacity: 0.55;
        }

        #estado {
            min-height: 20px;
            margin-top: 10px;
            color: #6b7280;
            font-size: 14px;
        }

        @media (max-width: 640px) {
            form {
                grid-template-columns: 1fr;
            }

            #enviar {
                min-height: 46px;
            }

            .mensaje {
                max-width: 92%;
            }
        }
    </style>
</head>

<body>
    <main class="contenedor">
        <div class="cabecera">
            <h1>Chat IA</h1>
            <button id="reiniciar" type="button">
                Reiniciar conversación
            </button>
        </div>

        <section
            id="chat"
            aria-live="polite"
            aria-label="Conversación"
        ></section>

        <form id="formulario">
            <textarea
                id="mensaje"
                placeholder="Escribe tu mensaje..."
                required
            ></textarea>

            <button id="enviar" type="submit">
                Enviar
            </button>
        </form>

        <div id="estado"></div>
    </main>

    <script>
        const formulario = document.getElementById('formulario');
        const campoMensaje = document.getElementById('mensaje');
        const botonEnviar = document.getElementById('enviar');
        const botonReiniciar = document.getElementById('reiniciar');
        const chat = document.getElementById('chat');
        const estado = document.getElementById('estado');

        function agregarMensaje(texto, tipo, modelo = '') {
            const bloque = document.createElement('div');
            bloque.className = `mensaje ${tipo}`;
            bloque.textContent = texto;

            if (modelo) {
                const meta = document.createElement('span');
                meta.className = 'modelo';
                meta.textContent = `Modelo utilizado: ${modelo}`;
                bloque.appendChild(meta);
            }

            chat.appendChild(bloque);
            chat.scrollTop = chat.scrollHeight;
        }

        function bloquear(bloqueado) {
            botonEnviar.disabled = bloqueado;
            botonReiniciar.disabled = bloqueado;
            campoMensaje.disabled = bloqueado;
        }

        async function llamarApi(datos) {
            const respuesta = await fetch('api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(datos),
            });

            let contenido;

            try {
                contenido = await respuesta.json();
            } catch (error) {
                throw new Error(
                    'El servidor devolvió una respuesta no válida.'
                );
            }

            if (!respuesta.ok) {
                throw new Error(
                    contenido.error || 'Se produjo un error.'
                );
            }

            return contenido;
        }

        formulario.addEventListener('submit', async (evento) => {
            evento.preventDefault();

            const texto = campoMensaje.value.trim();

            if (!texto) {
                return;
            }

            agregarMensaje(texto, 'usuario');
            campoMensaje.value = '';
            bloquear(true);
            estado.textContent =
                'Buscando el modelo gratuito más rápido disponible...';

            try {
                const datos = await llamarApi({
                    mensaje: texto,
                });

                agregarMensaje(
                    datos.respuesta,
                    'asistente',
                    datos.modelo
                );

                estado.textContent =
                    `Prioridad utilizada: ${datos.prioridad}`;
            } catch (error) {
                agregarMensaje(
                    error.message,
                    'error'
                );

                estado.textContent = '';
            } finally {
                bloquear(false);
                campoMensaje.focus();
            }
        });

        botonReiniciar.addEventListener('click', async () => {
            bloquear(true);
            estado.textContent = 'Reiniciando conversación...';

            try {
                await llamarApi({
                    accion: 'reiniciar',
                });

                chat.innerHTML = '';
                estado.textContent = 'Conversación reiniciada.';
            } catch (error) {
                agregarMensaje(
                    error.message,
                    'error'
                );

                estado.textContent = '';
            } finally {
                bloquear(false);
                campoMensaje.focus();
            }
        });

        campoMensaje.addEventListener('keydown', (evento) => {
            if (
                evento.key === 'Enter' &&
                !evento.shiftKey
            ) {
                evento.preventDefault();
                formulario.requestSubmit();
            }
        });
    </script>
</body>
</html>
