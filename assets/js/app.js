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
