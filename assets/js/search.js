document.addEventListener('DOMContentLoaded', () => {
    // Crear el modal de búsqueda
    const modal = document.createElement('div');
    modal.id = 'gs-global-search';
    modal.style.display = 'none';
    modal.innerHTML = `
        <div class="gs-search-overlay"></div>
        <div class="gs-search-box">
            <input type="text" id="gs-search-input" placeholder="Buscar leads por nombre, correo o mensaje...">
            <ul id="gs-search-results"></ul>
        </div>
    `;
    document.body.appendChild(modal);

    // Estilos rápidos
    const style = document.createElement('style');
    style.innerHTML = `
        #gs-global-search { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 9999; }
        .gs-search-overlay { position: absolute; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
        .gs-search-box {
            position: absolute; top: 20%; left: 50%; transform: translateX(-50%);
            background: white; padding: 20px; border-radius: 8px; width: 90%; max-width: 600px;
            box-shadow: 0 0 10px rgba(0,0,0,0.2);
        }
        #gs-search-input {
            width: 100%; padding: 10px; font-size: 16px; border: 1px solid #ccc; margin-bottom: 10px;
        }
        #gs-search-results { list-style: none; padding: 0; margin: 0; max-height: 300px; overflow-y: auto; }
        #gs-search-results li {
            padding: 8px; border-bottom: 1px solid #eee; cursor: pointer;
        }
        #gs-search-results li:hover {
            background: #f2f2f2;
        }
    `;
    document.head.appendChild(style);

    // Mostrar u ocultar el modal con Ctrl+K o Cmd+K
    document.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
            e.preventDefault();
            modal.style.display = modal.style.display === 'none' ? 'block' : 'none';
            if (modal.style.display === 'block') {
                document.getElementById('gs-search-input').focus();
            }
        }

        // Escape para cerrar
        if (e.key === 'Escape' && modal.style.display === 'block') {
            modal.style.display = 'none';
        }
    });

    // Evento de búsqueda
    document.getElementById('gs-search-input').addEventListener('input', function () {
        const query = this.value.trim();
        const results = document.getElementById('gs-search-results');
        results.innerHTML = '';

        if (query.length < 3) return;

        results.innerHTML = '<li>Cargando...</li>';

        fetch(gsSearch.ajax_url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                action: 'gs_search_leads',
                security: gsSearch.nonce,
                query: query
            })
        })
        .then(response => response.json())
        .then(data => {
            results.innerHTML = '';
            if (data.success && data.results.length > 0) {
                data.results.forEach(item => {
                    const li = document.createElement('li');
                    li.innerHTML = `<strong>${item.nombre}</strong><br><small>${item.correo}</small><br>${item.mensaje}`;
                    li.addEventListener('click', () => {
                        window.location.href = item.link;
                    });
                    results.appendChild(li);
                });
            } else {
                results.innerHTML = '<li>No se encontraron resultados.</li>';
            }
        })
        .catch(err => {
            results.innerHTML = `<li>Error: ${err.message}</li>`;
        });
    });
});