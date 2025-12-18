document.addEventListener("DOMContentLoaded", function () {

    function createChart(canvasId, type, labelName, borderColor = null) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return;

        let labels, data, colors;
        try {
            labels = JSON.parse(canvas.dataset.labels);
            data = JSON.parse(canvas.dataset.data);
            colors = JSON.parse(canvas.dataset.colors);
        } catch (e) {
            console.error(`Gagal parsing data chart ${canvasId}:`, e);
            return;
        }

        let bgColors = colors;
        if (type === 'bar' && Array.isArray(colors) && colors.length === 1) {
            bgColors = colors[0];
        }

        let datasetsConfig = [{
            label: labelName,
            data: data,
            backgroundColor: bgColors,
            borderWidth: 1
        }];

        if (borderColor) {
            datasetsConfig[0].borderColor = borderColor;
            datasetsConfig[0].fill = true;
            datasetsConfig[0].tension = 0.4;
            datasetsConfig[0].pointRadius = 3;
        } else if (type === 'bar') {
            datasetsConfig[0].borderColor = bgColors;
            datasetsConfig[0].borderWidth = 1;
        }

        new Chart(canvas, {
            type: type,
            data: {
                labels: labels,
                datasets: datasetsConfig
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: (type === 'doughnut' || type === 'pie'),
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null && context.parsed.y !== undefined) {
                                    label += context.parsed.y;
                                } else {
                                    label += context.parsed;
                                }
                                const unit = canvas.dataset.tooltipUnit || "Orang";
                                return label + " " + unit;
                            }
                        }
                    }
                },
                scales: (type === 'bar' || type === 'line') ? {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0,
                            callback: function (value) {
                                if (value % 1 === 0) return value;
                            }
                        }
                    },
                    x: {
                        ticks: {
                            autoSkip: false,
                            maxRotation: 45,
                            minRotation: 0
                        }
                    }
                } : {}
            }
        });
    }

    createChart('c1', 'doughnut', 'Jumlah');

    const c2 = document.getElementById('c2');
    if (c2) {
        const type = c2.dataset.chartType || 'line';
        const borderColor = type === 'line' ? '#0d6efd' : null;
        createChart('c2', type, 'Peserta', borderColor);
    }
});