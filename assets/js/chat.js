const ChatUI = {
    esc(s) {
        const d = document.createElement('div');
        d.textContent = s == null ? '' : String(s);
        return d.innerHTML;
    },
    fmtPrecio(n) {
        const num = (typeof n === 'number' ? n : parseFloat(n)) || 0;
        return '$' + num.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    },
    estadoLabel(e) {
        return {
            pendiente: 'Pendiente',
            confirmada: 'Confirmada',
            cancelada: 'Cancelada',
            entregada: 'Entregada',
        }[e] || e;
    },
    ordenHTML(o) {
        const items = (o.items || []).map(function (it) {
            return '<div class="orden-item">' +
                '<span class="orden-item-nombre">' + ChatUI.esc(it.nombre) + '</span>' +
                '<span class="orden-item-qty">' + ChatUI.esc(it.cantidad) + ' x ' + ChatUI.esc(it.precio_formato) + '</span>' +
                '<span>' + ChatUI.esc(it.subtotal_formato) + '</span>' +
            '</div>';
        }).join('');
        const nota = o.nota
            ? '<div class="orden-nota">&#128221; ' + ChatUI.esc(o.nota) + '</div>'
            : '';
        return '<div class="orden-card">' +
            '<div class="orden-card-header">' +
                '<span>&#128203; Orden #' + o.id + '</span>' +
                '<span class="orden-estado ' + ChatUI.esc(o.estado) + '">' + ChatUI.estadoLabel(o.estado) + '</span>' +
            '</div>' +
            '<div class="orden-card-body">' + items + nota +
                '<div class="orden-total"><span>Total</span><span>' + ChatUI.esc(o.total_formato) + '</span></div>' +
            '</div>' +
        '</div>';
    },
    scrollBottom(el) {
        if (el) {
            el.scrollTop = el.scrollHeight;
        }
    },
};

const ChatCliente = {
    state: {
        identificado: false,
        conversacionId: null,
        clienteNombre: null,
        mensajes: [],
        noLeidos: 0,
    },
    panelOpen: false,

    init() {
        if (!document.getElementById('chat-widget')) {
            return;
        }
        document.getElementById('chat-login-btn').addEventListener('click', () => this.iniciarChat());
        document.getElementById('chat-login-nombre').addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.iniciarChat();
            }
        });
        document.getElementById('chat-input').addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.enviar();
            }
        });
        document.getElementById('chat-send').addEventListener('click', () => this.enviar());
        document.getElementById('chat-mensajes').addEventListener('click', (e) => {
            const btn = e.target.closest('[data-accion]');
            if (btn) {
                const ordenId = parseInt(btn.getAttribute('data-orden'), 10);
                const accion = btn.getAttribute('data-accion');
                this.actualizarOrden(ordenId, accion === 'confirmar' ? 'confirmada' : 'cancelada');
            }
        });

        setInterval(() => this.poll(), 4000);

        fetch('ajax/chat_init.php')
            .then((r) => r.json())
            .then((d) => {
                if (d.identificado && d.conversacion_id) {
                    this.state.identificado = true;
                    this.state.conversacionId = d.conversacion_id;
                    this.state.clienteNombre = d.cliente ? d.cliente.nombre : null;
                    this.mostrarChat();
                }
                this.poll();
            })
            .catch(() => {});
    },

    abrirCerrar() {
        this.panelOpen = !this.panelOpen;
        document.getElementById('chat-widget').classList.toggle('open', this.panelOpen);
        if (this.panelOpen) {
            if (this.state.identificado) {
                this.render();
                this.poll();
            } else {
                this.mostrarLogin();
                const nombre = document.getElementById('chat-login-nombre');
                if (nombre) {
                    nombre.focus();
                }
            }
        }
    },

    mostrarChat() {
        document.getElementById('chat-login').classList.remove('visible');
        document.getElementById('chat-form').classList.add('visible');
        if (this.state.mensajes.length) {
            document.getElementById('chat-mensajes').classList.add('visible');
            this.render();
        }
    },

    mostrarLogin() {
        document.getElementById('chat-form').classList.remove('visible');
        document.getElementById('chat-mensajes').classList.remove('visible');
        document.getElementById('chat-login').classList.add('visible');
    },

    iniciarChat() {
        const nombre = document.getElementById('chat-login-nombre').value.trim();
        const telefono = document.getElementById('chat-login-telefono').value.trim();
        const errorEl = document.getElementById('chat-login-error');
        if (!nombre) {
            if (errorEl) {
                errorEl.style.display = 'block';
                errorEl.textContent = 'Escribe tu nombre para continuar';
            }
            return;
        }
        if (errorEl) {
            errorEl.style.display = 'none';
        }
        fetch('ajax/chat_identificar.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'nombre=' + encodeURIComponent(nombre) + '&telefono=' + encodeURIComponent(telefono),
        })
            .then((r) => r.json())
            .then((d) => {
                if (d.ok) {
                    this.state.identificado = true;
                    this.state.conversacionId = d.conversacion_id;
                    this.state.clienteNombre = d.cliente_nombre;
                    this.mostrarChat();
                    this.poll();
                } else if (d.error && errorEl) {
                    errorEl.style.display = 'block';
                    errorEl.textContent = d.error;
                }
            })
            .catch(() => {
                if (errorEl) {
                    errorEl.style.display = 'block';
                    errorEl.textContent = 'No se pudo iniciar el chat. Intenta de nuevo.';
                }
            });
    },

    poll() {
        if (!this.state.identificado || !this.state.conversacionId) {
            return;
        }
        const leer = this.panelOpen ? 1 : 0;
        fetch('ajax/chat_mensajes.php?conversacion_id=' + this.state.conversacionId + '&leer=' + leer)
            .then((r) => r.json())
            .then((d) => {
                if (d.identificado === false) {
                    this.state.identificado = false;
                    this.state.conversacionId = null;
                    this.mostrarLogin();
                    return;
                }
                if (d.conversacion_id) {
                    this.state.conversacionId = d.conversacion_id;
                }
                this.state.mensajes = d.mensajes || [];
                this.state.noLeidos = d.no_leidos || 0;
                if (this.panelOpen) {
                    this.render();
                }
                this.actualizarBadge();
            })
            .catch(() => {});
    },

    render() {
        const cont = document.getElementById('chat-mensajes');
        cont.innerHTML = '';
        this.state.mensajes.forEach((m) => cont.appendChild(this.renderMensaje(m)));
        if (this.state.mensajes.length) {
            cont.classList.add('visible');
        }
        ChatUI.scrollBottom(cont);
    },

    renderMensaje(m) {
        const div = document.createElement('div');
        if (m.tipo === 'orden' && m.orden) {
            const o = m.orden;
            div.innerHTML = ChatUI.ordenHTML(o);
            if (o.estado === 'pendiente') {
                div.innerHTML += '<div class="orden-acciones">' +
                    '<button class="btn btn-success" data-accion="confirmar" data-orden="' + o.id + '">&#10004; Confirmar</button>' +
                    '<button class="btn btn-danger" data-accion="cancelar" data-orden="' + o.id + '">&#10007; Cancelar</button>' +
                '</div>';
            }
        } else {
            div.innerHTML = '<div class="msg ' + ChatUI.esc(m.remitente) + '">' +
                '<span>' + ChatUI.esc(m.texto) + '</span>' +
                '<span class="msg-hora">' + ChatUI.esc(m.hora) + '</span>' +
            '</div>';
        }
        return div;
    },

    actualizarBadge() {
        const badge = document.getElementById('chat-unread-badge');
        if (!badge) {
            return;
        }
        if (this.state.noLeidos > 0 && !this.panelOpen) {
            badge.textContent = this.state.noLeidos > 99 ? '99+' : this.state.noLeidos;
            badge.style.display = 'flex';
        } else {
            badge.style.display = 'none';
        }
    },

    enviar() {
        const input = document.getElementById('chat-input');
        const texto = input.value.trim();
        if (!texto || !this.state.conversacionId) {
            return;
        }
        input.value = '';
        fetch('ajax/chat_envio.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'conversacion_id=' + encodeURIComponent(this.state.conversacionId) + '&texto=' + encodeURIComponent(texto),
        })
            .then((r) => r.json())
            .then((d) => {
                if (d.ok) {
                    this.poll();
                } else {
                    input.value = texto;
                }
            })
            .catch(() => {
                input.value = texto;
            });
    },

    actualizarOrden(ordenId, estado) {
        fetch('ajax/orden_estado.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'orden_id=' + ordenId + '&estado=' + encodeURIComponent(estado),
        })
            .then((r) => r.json())
            .then((d) => {
                if (d.ok) {
                    this.poll();
                } else {
                    alert(d.error || 'No se pudo actualizar la orden');
                }
            })
            .catch(() => alert('No se pudo actualizar la orden'));
    },
};

const ChatAdmin = {
    state: {
        activo: null,
        conversaciones: [],
        productos: [],
    },

    init(config) {
        if (!document.getElementById('chat-thread')) {
            return;
        }
        this.ajaxBase = (config && config.ajaxBase) || 'ajax/';
        this.state.productos = config.productos || [];

        document.getElementById('btn-refresh-convs').addEventListener('click', () => this.cargarConversaciones());
        document.getElementById('btn-chat-enviar').addEventListener('click', () => this.enviar());
        document.getElementById('btn-crear-orden').addEventListener('click', () => this.abrirModalOrden());
        const input = document.getElementById('chat-admin-input');
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.enviar();
            }
        });
        input.addEventListener('input', () => {
            input.style.height = 'auto';
            input.style.height = Math.min(input.scrollHeight, 160) + 'px';
        });
        document.getElementById('chat-admin-messages').addEventListener('click', (e) => {
            const btn = e.target.closest('[data-accion]');
            if (btn) {
                this.cambiarEstado(parseInt(btn.getAttribute('data-orden'), 10), btn.getAttribute('data-estado'));
            }
        });
        const modal = document.getElementById('orden-modal');
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                this.cerrarModalOrden();
            }
        });

        this.construirProductos();

        this.cargarConversaciones();
        if (config.conversacionInicial) {
            this.abrirConversacion(config.conversacionInicial);
        }

        setInterval(() => {
            if (this.state.activo) {
                this.cargarMensajes();
            }
        }, 4000);
        setInterval(() => this.cargarConversaciones(true), 15000);
    },

    cargarConversaciones(silencioso) {
        fetch(this.ajaxBase + 'chat_conversaciones.php')
            .then((r) => r.json())
            .then((d) => {
                this.state.conversaciones = d.conversaciones || [];
                this.renderConversaciones();
            })
            .catch(() => {});
    },

    renderConversaciones() {
        const list = document.getElementById('chat-convs-list');
        list.innerHTML = '';
        if (!this.state.conversaciones.length) {
            list.innerHTML = '<p class="chat-empty">Aún no hay conversaciones.</p>';
            return;
        }
        this.state.conversaciones.forEach((c) => {
            const btn = document.createElement('button');
            btn.className = 'chat-conv-item' + (this.state.activo === c.id ? ' active' : '');
            btn.setAttribute('data-conv', c.id);
            let ultimo = c.ultimo_mensaje || 'Sin mensajes';
            if (c.ultimo_remitente === 'cliente') {
                ultimo = '&#128100; ' + ultimo;
            } else if (c.ultimo_remitente === 'admin') {
                ultimo = '&#128736;&#65039; ' + ultimo;
            }
            const badge = c.no_leidos > 0 ? ' <span class="badge-chat">' + c.no_leidos + '</span>' : '';
            btn.innerHTML =
                '<div class="conv-nombre"><span>' + ChatUI.esc(c.cliente_nombre) + badge + '</span><span class="conv-fecha">' + ChatUI.esc(c.fecha) + '</span></div>' +
                '<div class="conv-ultimo">' + ultimo + '</div>';
            btn.addEventListener('click', () => this.abrirConversacion(c.id));
            list.appendChild(btn);
        });
    },

    abrirConversacion(id) {
        this.state.activo = id;
        document.getElementById('btn-crear-orden').disabled = false;
        document.getElementById('chat-admin-input').placeholder = 'Escribe una respuesta...';
        this.renderConversaciones();
        this.cargarMensajes();
    },

    cargarMensajes() {
        if (!this.state.activo) {
            return;
        }
        fetch(this.ajaxBase + 'chat_mensajes.php?conversacion_id=' + this.state.activo)
            .then((r) => r.json())
            .then((d) => {
                if (d.conversacion) {
                    document.getElementById('chat-thread-nombre').textContent = d.conversacion.cliente_nombre;
                    let sub = 'Conversación abierta';
                    if (d.conversacion.cliente_telefono) {
                        sub = 'Tel: ' + d.conversacion.cliente_telefono;
                    }
                    document.getElementById('chat-thread-sub').textContent = sub;
                }
                const cont = document.getElementById('chat-admin-messages');
                cont.innerHTML = '';
                if (!d.mensajes.length) {
                    cont.innerHTML = '<p class="chat-empty">No hay mensajes todavía.</p>';
                } else {
                    d.mensajes.forEach((m) => cont.appendChild(this.renderMensaje(m)));
                }
                ChatUI.scrollBottom(cont);
                this.renderConversaciones();
            })
            .catch(() => {});
    },

    renderMensaje(m) {
        const div = document.createElement('div');
        if (m.tipo === 'orden' && m.orden) {
            const o = m.orden;
            div.innerHTML = ChatUI.ordenHTML(o);
            const acciones = [];
            if (o.estado === 'pendiente') {
                acciones.push(['confirmada', '&#10004; Confirmar', 'btn-success']);
                acciones.push(['cancelada', '&#10007; Cancelar', 'btn-danger']);
            } else if (o.estado === 'confirmada') {
                acciones.push(['entregada', '&#128230; Marcar entregada', 'btn-primary']);
                acciones.push(['cancelada', '&#10007; Cancelar', 'btn-danger']);
            }
            if (acciones.length) {
                div.innerHTML += '<div class="orden-acciones">' + acciones.map((a) =>
                    '<button class="btn btn-sm ' + a[2] + '" data-accion="estado" data-orden="' + o.id + '" data-estado="' + a[0] + '">' + a[1] + '</button>'
                ).join('') + '</div>';
            }
        } else {
            div.innerHTML = '<div class="msg ' + ChatUI.esc(m.remitente) + '">' +
                '<span>' + ChatUI.esc(m.texto) + '</span>' +
                '<span class="msg-hora">' + ChatUI.esc(m.hora) + '</span>' +
            '</div>';
        }
        return div;
    },

    enviar() {
        const input = document.getElementById('chat-admin-input');
        const texto = input.value.trim();
        if (!texto || !this.state.activo) {
            return;
        }
        input.value = '';
        fetch(this.ajaxBase + 'chat_envio.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'conversacion_id=' + encodeURIComponent(this.state.activo) + '&texto=' + encodeURIComponent(texto),
        })
            .then((r) => r.json())
            .then((d) => {
                if (d.ok) {
                    this.cargarMensajes();
                } else {
                    input.value = texto;
                    alert(d.error || 'No se pudo enviar el mensaje');
                }
            })
            .catch(() => {
                input.value = texto;
                alert('No se pudo enviar el mensaje');
            });
    },

    cambiarEstado(ordenId, estado) {
        fetch(this.ajaxBase + 'orden_estado.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'orden_id=' + ordenId + '&estado=' + encodeURIComponent(estado),
        })
            .then((r) => r.json())
            .then((d) => {
                if (d.ok) {
                    this.cargarMensajes();
                } else {
                    alert(d.error || 'No se pudo actualizar la orden');
                }
            })
            .catch(() => alert('No se pudo actualizar la orden'));
    },

    construirProductos() {
        const cont = document.getElementById('orden-productos');
        cont.innerHTML = '';
        this.state.productos.forEach((p) => {
            const row = document.createElement('div');
            row.className = 'orden-producto';
            row.innerHTML =
                '<input type="checkbox" data-pid="' + p.id + '">' +
                '<div class="op-nombre">' + ChatUI.esc(p.nombre) +
                    '<div class="op-stock">Stock: ' + p.stock + ' ' + ChatUI.esc(p.unidad_medida || '') + '</div>' +
                '</div>' +
                '<span class="op-precio">' + ChatUI.esc(p.precio_formato) + '</span>' +
                '<input type="number" class="op-cantidad" min="1" value="1" data-pid="' + p.id + '">';
            row.querySelector('input[type="checkbox"]').addEventListener('change', () => {
                row.classList.toggle('checked', row.querySelector('input[type="checkbox"]').checked);
                this.actualizarResumen();
            });
            row.querySelector('.op-cantidad').addEventListener('input', () => this.actualizarResumen());
            cont.appendChild(row);
        });
    },

    actualizarResumen() {
        const resumen = document.getElementById('orden-resumen');
        let filas = [];
        let total = 0;
        let totalItems = 0;
        this.state.productos.forEach((p) => {
            const cb = document.querySelector('.orden-producto input[type="checkbox"][data-pid="' + p.id + '"]');
            if (cb && cb.checked) {
                const qty = parseInt(document.querySelector('.op-cantidad[data-pid="' + p.id + '"]').value, 10) || 1;
                const subtotal = parseFloat(p.precio) * qty;
                total += subtotal;
                totalItems += qty;
                filas.push('<div class="resumen-linea"><span>' + ChatUI.esc(p.nombre) + ' x ' + qty + '</span><span>' + ChatUI.fmtPrecio(subtotal) + '</span></div>');
            }
        });
        if (!totalItems) {
            resumen.classList.remove('visible');
            resumen.innerHTML = '';
            return;
        }
        resumen.classList.add('visible');
        resumen.innerHTML = filas.join('') +
            '<div class="resumen-linea resumen-total"><span>Total (' + totalItems + ' ítems)</span><span>' + ChatUI.fmtPrecio(total) + '</span></div>';
    },

    abrirModalOrden() {
        if (!this.state.activo) {
            return;
        }
        document.getElementById('orden-modal').style.display = 'flex';
        this.actualizarResumen();
    },

    cerrarModalOrden() {
        document.getElementById('orden-modal').style.display = 'none';
    },

    enviarOrden() {
        if (!this.state.activo) {
            return;
        }
        const items = [];
        this.state.productos.forEach((p) => {
            const cb = document.querySelector('.orden-producto input[type="checkbox"][data-pid="' + p.id + '"]');
            if (cb && cb.checked) {
                const qty = parseInt(document.querySelector('.op-cantidad[data-pid="' + p.id + '"]').value, 10) || 1;
                items.push({ producto_id: p.id, cantidad: Math.max(1, qty) });
            }
        });
        if (!items.length) {
            alert('Selecciona al menos un producto');
            return;
        }
        const nota = document.getElementById('orden-nota').value.trim();
        const btn = document.getElementById('btn-orden-crear');
        btn.disabled = true;
        fetch(this.ajaxBase + 'orden_crear.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'conversacion_id=' + encodeURIComponent(this.state.activo) +
                '&nota=' + encodeURIComponent(nota) +
                '&items=' + encodeURIComponent(JSON.stringify(items)),
        })
            .then((r) => r.json())
            .then((d) => {
                btn.disabled = false;
                if (d.ok) {
                    this.cerrarModalOrden();
                    this.cargarMensajes();
                } else {
                    alert(d.error || 'No se pudo crear la orden');
                }
            })
            .catch(() => {
                btn.disabled = false;
                alert('Error al crear la orden');
            });
    },
};

window.ChatCliente = ChatCliente;
window.ChatAdmin = ChatAdmin;

document.addEventListener('DOMContentLoaded', () => {
    ChatCliente.init();
    if (window.CHAT_CONFIG) {
        ChatAdmin.init(window.CHAT_CONFIG);
    }
});
