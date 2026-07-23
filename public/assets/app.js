'use strict';

const formulario = document.getElementById('formulario-chat');
const campoMensaje = document.getElementById('campo-mensaje');
const botonEnviar = document.getElementById('boton-enviar');
const botonReiniciar = document.getElementById('boton-reiniciar');
const zonaMensajes = document.getElementById('mensajes');
const estadoChat = document.getElementById('estado-chat');
const indicadorEscribiendo = document.getElementById('indicador-escribiendo');
const contadorCaracteres = document.getElementById('contador-caracteres');

function actualizarContador() {
    const len = campoMensaje.value.length;
    contadorCaracteres.textContent = len + ' / 2000';
}

campoMensaje.addEventListener('input', actualizarContador);

function formatearTexto(texto) {
    const mapa = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;',
    };
    const escapado = texto.replace(/[&<>"']/g, (c) => mapa[c]);

    let dentroListaOl = false;
    let dentroListaUl = false;
    let dentroBloqueCodigo = false;

    const lineas = escapado.split('\n');
    const bloques = [];

    for (let i = 0; i < lineas.length; i++) {
        let linea = lineas[i];

        if (linea.startsWith('```')) {
            if (!dentroBloqueCodigo) {
                dentroBloqueCodigo = true;
                bloques.push('<pre><code>');
                continue;
            } else {
                dentroBloqueCodigo = false;
                bloques.push('</code></pre>');
                continue;
            }
        }

        if (dentroBloqueCodigo) {
            bloques.push(linea + '\n');
            continue;
        }

        if (/^#{1,3}\s/.test(linea)) {
            cerrarListas();
            const nivel = linea.match(/^(#+)\s/)[1].length;
            const contenido = linea.replace(/^#+\s/, '');
            const etiqueta = 'h' + nivel;
            bloques.push('<' + etiqueta + '>' + inline(contenido) + '</' + etiqueta + '>');
            continue;
        }

        if (/^___$|^---$|^\*\*\*$/.test(linea.trim())) {
            cerrarListas();
            bloques.push('<hr>');
            continue;
        }

        if (/^\d+\.\s/.test(linea)) {
            if (!dentroListaOl) {
                cerrarListas();
                dentroListaOl = true;
                bloques.push('<ol>');
            }
            const contenido = linea.replace(/^\d+\.\s/, '');
            bloques.push('<li>' + inline(contenido) + '</li>');
            continue;
        }

        if (/^[-*]\s/.test(linea)) {
            if (!dentroListaUl) {
                cerrarListas();
                dentroListaUl = true;
                bloques.push('<ul>');
            }
            const contenido = linea.replace(/^[-*]\s/, '');
            bloques.push('<li>' + inline(contenido) + '</li>');
            continue;
        }

        if (/^>\s/.test(linea)) {
            cerrarListas();
            const contenido = linea.replace(/^>\s/, '');
            bloques.push('<blockquote>' + inline(contenido) + '</blockquote>');
            continue;
        }

        cerrarListas();

        if (linea.trim() === '') {
            bloques.push('<br>');
        } else {
            bloques.push('<p>' + inline(linea) + '</p>');
        }
    }

    cerrarListas();
    if (dentroBloqueCodigo) {
        bloques.push('</code></pre>');
    }

    return bloques.join('');

    function cerrarListas() {
        if (dentroListaOl) {
            bloques.push('</ol>');
            dentroListaOl = false;
        }
        if (dentroListaUl) {
            bloques.push('</ul>');
            dentroListaUl = false;
        }
    }

    function inline(texto) {
        return texto
            .replace(/\*\*\*(.+?)\*\*\*/g, '<strong><em>$1</em></strong>')
            .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
            .replace(/\*(.+?)\*/g, '<em>$1</em>')
            .replace(/`(.+?)`/g, '<code>$1</code>');
    }
}

function crearBurbuja(texto, autor, tipo) {
    const articulo = document.createElement('article');
    const burbuja = document.createElement('div');
    const nombre = document.createElement('p');
    const contenido = document.createElement('div');

    articulo.className = autor === 'usuario'
        ? 'flex justify-end'
        : 'flex justify-start';

    if (tipo === 'error') {
        burbuja.className =
            'max-w-[80%] rounded-2xl rounded-bl-md ' +
            'bg-red-50 px-4 py-3 text-red-800 border border-red-200';
    } else if (autor === 'usuario') {
        burbuja.className =
            'max-w-[80%] rounded-2xl rounded-br-md ' +
            'bg-blue-600 px-4 py-3 text-white';
    } else {
        burbuja.className =
            'max-w-[80%] rounded-2xl rounded-bl-md ' +
            'bg-slate-100 px-4 py-3 text-slate-800';
    }

    nombre.className = autor === 'usuario'
        ? 'text-sm font-semibold text-blue-100'
        : 'text-sm font-semibold text-slate-600';

    nombre.textContent = autor === 'usuario'
        ? 'Tú'
        : tipo === 'error'
            ? 'Error'
            : 'Asistente';

    contenido.className = 'mt-1 leading-relaxed [&_h1]:text-lg [&_h1]:font-bold [&_h2]:text-base [&_h2]:font-bold [&_h3]:text-sm [&_h3]:font-bold [&_ol]:list-decimal [&_ol]:pl-5 [&_ul]:list-disc [&_ul]:pl-5 [&_li]:mt-1 [&_blockquote]:border-l-4 [&_blockquote]:border-slate-300 [&_blockquote]:pl-3 [&_blockquote]:italic [&_blockquote]:text-slate-600 [&_pre]:bg-slate-200 [&_pre]:p-3 [&_pre]:rounded-lg [&_pre]:overflow-x-auto [&_pre]:text-sm [&_code]:bg-slate-200 [&_code]:px-1 [&_code]:rounded [&_code]:text-sm [&_hr]:my-3 [&_hr]:border-slate-300 [&_hr]:border-t [&_p]:mt-1';

    if (autor === 'asistente' && tipo !== 'error') {
        contenido.innerHTML = formatearTexto(texto);
    } else {
        contenido.textContent = texto;
    }

    burbuja.appendChild(nombre);
    burbuja.appendChild(contenido);
    articulo.appendChild(burbuja);
    zonaMensajes.appendChild(articulo);

    desplazarAlFinal();

    return articulo;
}

function desplazarAlFinal() {
    zonaMensajes.scrollTop = zonaMensajes.scrollHeight;
}

function cambiarEstadoCarga(cargando) {
    campoMensaje.disabled = cargando;
    botonEnviar.disabled = cargando;

    botonEnviar.classList.toggle('opacity-50', cargando);
    botonEnviar.classList.toggle('cursor-not-allowed', cargando);

    if (cargando) {
        indicadorEscribiendo.classList.remove('hidden');
    } else {
        indicadorEscribiendo.classList.add('hidden');
    }
}

async function enviarPeticion(datos, intento = 0) {
    const respuesta = await fetch('chat.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json; charset=utf-8',
        },
        body: JSON.stringify(datos),
    });

    let cuerpo;

    try {
        cuerpo = await respuesta.json();
    } catch (error) {
        throw new Error(
            'El servidor devolvió una respuesta no válida.'
        );
    }

    if (!respuesta.ok || cuerpo.ok !== true) {
        const errorMsg = cuerpo.error || 'No se pudo completar la petición.';

        if (respuesta.status === 504 && intento < 1) {
            estadoChat.textContent = 'El servidor tardó demasiado. Reintentando…';
            return enviarPeticion(datos, intento + 1);
        }

        throw new Error(errorMsg);
    }

    return cuerpo;
}

formulario.addEventListener('submit', async (evento) => {
    evento.preventDefault();

    const mensaje = campoMensaje.value.trim();

    if (mensaje === '') {
        estadoChat.textContent = 'Escribe un mensaje antes de enviarlo.';
        campoMensaje.focus();
        return;
    }

    if (mensaje.length > 2000) {
        estadoChat.textContent =
            'El mensaje no puede superar los 2000 caracteres.';
        campoMensaje.focus();
        return;
    }

    estadoChat.textContent = '';

    crearBurbuja(mensaje, 'usuario');

    campoMensaje.value = '';
    actualizarContador();
    cambiarEstadoCarga(true);

    try {
        const resultado = await enviarPeticion({
            mensaje: mensaje,
        });

        crearBurbuja(
            resultado.respuesta,
            'asistente'
        );
    } catch (error) {
        crearBurbuja(
            error instanceof Error
                ? error.message
                : 'Se produjo un error inesperado.',
            'asistente',
            'error'
        );
    } finally {
        cambiarEstadoCarga(false);
        campoMensaje.focus();
    }
});

botonReiniciar.addEventListener('click', async () => {
    cambiarEstadoCarga(true);
    estadoChat.textContent = 'Reiniciando conversación…';

    try {
        await enviarPeticion({
            accion: 'reiniciar',
        });

        zonaMensajes.replaceChildren();
        estadoChat.textContent = '';
    } catch (error) {
        crearBurbuja(
            error instanceof Error
                ? error.message
                : 'No se pudo reiniciar la conversación.',
            'asistente',
            'error'
        );
    } finally {
        cambiarEstadoCarga(false);
        campoMensaje.focus();
    }

    const bienvenida = document.createElement('article');
    const burbuja = document.createElement('div');
    const nombre = document.createElement('p');
    const contenido = document.createElement('p');

    bienvenida.className = 'flex justify-start';
    burbuja.className = 'max-w-[80%] rounded-2xl rounded-bl-md bg-slate-100 px-4 py-3';
    nombre.className = 'text-sm font-semibold text-slate-600';
    nombre.textContent = 'Asistente';
    contenido.className = 'mt-1 leading-relaxed';
    contenido.textContent = '¡Hola! Soy MiniChatGPT, un chat conectado a un modelo de lenguaje a través de OpenRouter. Puedes preguntarme lo que quieras.';

    burbuja.appendChild(nombre);
    burbuja.appendChild(contenido);
    bienvenida.appendChild(burbuja);
    zonaMensajes.appendChild(bienvenida);
    desplazarAlFinal();
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
