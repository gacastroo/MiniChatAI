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

function crearBurbuja(texto, autor, tipo) {
    const articulo = document.createElement('article');
    const burbuja = document.createElement('div');
    const nombre = document.createElement('p');
    const contenido = document.createElement('p');

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

    contenido.className = 'mt-1 leading-relaxed whitespace-pre-wrap';
    contenido.textContent = texto;

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

async function enviarPeticion(datos) {
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
        throw new Error(
            cuerpo.error || 'No se pudo completar la petición.'
        );
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
