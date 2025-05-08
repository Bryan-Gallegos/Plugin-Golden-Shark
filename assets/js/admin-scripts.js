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

document.addEventListener('DOMContentLoaded', function () {
    if (typeof gsData !== 'undefined') {
        const ctx = document.getElementById('goldenSharkChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Frases', 'Eventos', 'Leads'],
                    datasets: [{
                        label: 'Resumen de registros',
                        data: [gsData.frases, gsData.eventos, gsData.leads],
                        backgroundColor: ['#f39c12', '#2ecc71', '#3498db']
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { precision:0 }
                        }
                    }
                }
            });
        }
    }
});
