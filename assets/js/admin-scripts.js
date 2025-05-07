// Confirmación personalizada al eliminar
document.addEventListener('DOMContentLoaded', function () {
    const enlacesEliminar = document.querySelectorAll('a[data-confirm]');
    enlacesEliminar.forEach(function (enlace) {
        enlace.addEventListener('click', function (e) {
            if (!confirm(enlace.getAttribute('data-confirm'))) {
                e.preventDefault();
            }
        });
    });
});
