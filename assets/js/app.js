let categoriaActual = null;

function filtrarCategoria(categoriaId, btn) {
    categoriaActual = categoriaId;

    document.querySelectorAll('.cat-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    const grid = document.getElementById('productos-grid');
    const cards = grid.querySelectorAll('.product-card');
    let visibles = 0;

    cards.forEach(card => {
        const cat = card.getAttribute('data-categoria');
        if (!categoriaId || cat == categoriaId) {
            card.style.display = '';
            visibles++;
        } else {
            card.style.display = 'none';
        }
    });

    document.getElementById('no-results').style.display = visibles === 0 ? 'block' : 'none';
}

function buscarProductos() {
    const termino = document.getElementById('buscador').value.toLowerCase().trim();
    const grid = document.getElementById('productos-grid');
    const cards = grid.querySelectorAll('.product-card');
    let visibles = 0;

    cards.forEach(card => {
        const nombre = card.getAttribute('data-nombre') || '';
        const coincide = nombre.includes(termino);
        const coincideCategoria = !categoriaActual || card.getAttribute('data-categoria') == categoriaActual;

        if (coincide && coincideCategoria) {
            card.style.display = '';
            visibles++;
        } else {
            card.style.display = 'none';
        }
    });

    document.getElementById('no-results').style.display = visibles === 0 ? 'block' : 'none';
}

document.addEventListener('DOMContentLoaded', () => {
    const buscador = document.getElementById('buscador');
    if (buscador) {
        buscador.addEventListener('keyup', (e) => {
            if (e.key === 'Enter') buscarProductos();
            if (buscador.value === '') buscarProductos();
        });
    }

    document.querySelectorAll('.product-card').forEach(card => {
        card.addEventListener('click', (e) => {
            const id = card.getAttribute('data-id');
            if (id) abrirModal(id);
        });
    });

    const modal = document.getElementById('producto-modal');
    if (modal) {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) cerrarModal();
        });
    }
});

function confirmarEliminar(nombre) {
    return confirm(`¿Eliminar "${nombre}"? Esta acción no se puede deshacer.`);
}

function previewImage(input) {
    const preview = document.getElementById('image-preview');
    if (!preview) return;
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = (e) => {
            preview.src = e.target.result;
            preview.style.display = 'block';
            const placeholder = document.getElementById('image-placeholder');
            if (placeholder) placeholder.style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function abrirModal(productoId) {
    const modal = document.getElementById('producto-modal');
    const titulo = document.getElementById('modal-titulo');
    const grid = document.getElementById('sabores-grid');
    const loading = document.getElementById('modal-loading');

    modal.style.display = 'flex';
    grid.innerHTML = '';
    loading.style.display = 'block';
    titulo.textContent = 'Cargando...';

    fetch('ajax/get_sabores.php?producto_id=' + productoId)
        .then(res => {
            if (!res.ok) throw new Error('Error al cargar');
            return res.json();
        })
        .then(data => {
            loading.style.display = 'none';
            titulo.textContent = data.producto.nombre;

            if (!data.tiene_sabores) {
                grid.innerHTML = '<div class="modal-sin-sabores"><span>&#127856;</span><p>Este producto no tiene variedades disponibles</p></div>';
                return;
            }

            data.sabores.forEach(s => {
                const card = document.createElement('div');
                card.className = 'sabor-card';

                const imgHtml = s.imagen
                    ? '<img src="' + s.imagen + '" alt="' + s.nombre + '">'
                    : '<div class="sabor-placeholder"><span>&#127856;</span></div>';

                const stockClass = s.stock === 0 ? 'out' : (s.stock_low ? 'low' : '');

                card.innerHTML =
                    '<div class="sabor-imagen">' + imgHtml + '</div>' +
                    '<div class="sabor-info">' +
                        '<div class="sabor-nombre">' + s.nombre + '</div>' +
                        '<div class="sabor-detalles">' +
                            '<span class="sabor-precio">' + s.precio + '</span>' +
                            '<span class="sabor-stock ' + stockClass + '">' + s.stock_label + '</span>' +
                        '</div>' +
                    '</div>';

                grid.appendChild(card);
            });
        })
        .catch(err => {
            loading.style.display = 'none';
            titulo.textContent = 'Error';
            grid.innerHTML = '<div class="modal-sin-sabores"><span>&#9888;&#65039;</span><p>No se pudieron cargar los sabores. Intenta de nuevo.</p></div>';
        });
}

function cerrarModal() {
    document.getElementById('producto-modal').style.display = 'none';
}


