// Módulo de Gráficos
const Graficos = {
    init() {
        this.initProductosVendidos();
    },

    initProductosVendidos() {
        const ctx = document.getElementById('productosVendidosChart');
        if (!ctx) return;

        const config = {
            type: 'pie',
            data: this.getProductosVendidosData(),
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    title: {
                        display: true,
                        text: 'Productos Más Vendidos'
                    }
                }
            }
        };

        new Chart(ctx, config);
    },

    getProductosVendidosData() {
        return {
            labels: this.getProductosVendidosLabels(),
            datasets: [{
                data: this.getProductosVendidosValues(),
                backgroundColor: [
                    '#FF6384',
                    '#36A2EB',
                    '#FFCE56',
                    '#4BC0C0',
                    '#9966FF'
                ]
            }]
        };
    },

    getProductosVendidosLabels() {
        const labels = [];
        document.querySelectorAll('[data-producto-nombre]').forEach(element => {
            labels.push(element.dataset.productoNombre);
        });
        return labels;
    },

    getProductosVendidosValues() {
        const values = [];
        document.querySelectorAll('[data-producto-cantidad]').forEach(element => {
            values.push(parseInt(element.dataset.productoCantidad));
        });
        return values;
    }
};

// Inicialización de gráficos cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    Graficos.init();
});
