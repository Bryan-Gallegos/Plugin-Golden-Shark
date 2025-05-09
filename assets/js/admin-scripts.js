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

document.addEventListener('DOMContentLoaded', function () {
    if (typeof gsEventos !== 'undefined' && document.getElementById('gs-calendar')) {
        const calendarEl = document.getElementById('gs-calendar');
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            height: 'auto',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,listWeek'
            },
            events: gsEventos,
            eventColor: '#0073aa',
            eventDisplay: 'block',
            eventDidMount: function (info) {
                // Tooltip con la descripción del evento
                if (info.event.extendedProps.description) {
                    const tooltip = document.createElement('div');
                    tooltip.className = 'fc-tooltip';
                    tooltip.style.position = 'absolute';
                    tooltip.style.background = '#333';
                    tooltip.style.color = '#fff';
                    tooltip.style.padding = '5px 10px';
                    tooltip.style.borderRadius = '4px';
                    tooltip.style.fontSize = '12px';
                    tooltip.style.display = 'none';
                    tooltip.innerText = info.event.extendedProps.description;
                    document.body.appendChild(tooltip);

                    info.el.addEventListener('mouseenter', function (e) {
                        tooltip.style.left = e.pageX + 'px';
                        tooltip.style.top = (e.pageY + 15) + 'px';
                        tooltip.style.display = 'block';
                    });

                    info.el.addEventListener('mouseleave', function () {
                        tooltip.style.display = 'none';
                    });
                }
            }
        });
        calendar.render();
    }
});