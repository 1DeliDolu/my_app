import { Controller } from '@hotwired/stimulus';
import Chart from 'chart.js/auto';

export default class extends Controller {
    static values = {
        url: String,
        labels: Array,
        data: Array,
    };

    connect() {
        this.registeredHandlers = [];
        this.initializeChart();
        this.registerButtons();
    }

    disconnect() {
        if (this.chart) {
            this.chart.destroy();
            this.chart = null;
        }

        this.unregisterButtons();
    }

    initializeChart() {
        const canvas = this.element.querySelector('canvas');
        if (!canvas) {
            return;
        }

        const labels = this.hasLabelsValue ? this.labelsValue : [];
        const data = this.hasDataValue ? this.dataValue : [];

        this.chart = new Chart(canvas, {
            type: 'line',
            data: {
                labels,
                datasets: [
                    {
                        label: 'Revenue',
                        data,
                        borderColor: '#0d6efd',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: false,
                    },
                ],
            },
            options: {
                scales: {
                    y: { beginAtZero: true },
                },
                plugins: {
                    legend: { display: false },
                },
            },
        });
    }

    registerButtons() {
        const buttons = this.element.querySelectorAll('[data-range]');

        buttons.forEach((button) => {
            const handler = (event) => {
                event.preventDefault();
                this.updateChart(button.dataset.range, button);
            };
            button.addEventListener('click', handler);
            this.registeredHandlers.push({ button, handler });
        });
    }

    unregisterButtons() {
        this.registeredHandlers.forEach(({ button, handler }) => {
            button.removeEventListener('click', handler);
        });
        this.registeredHandlers = [];
    }

    async updateChart(range, button) {
        if (!this.hasUrlValue) {
            return;
        }

        try {
            if (button) {
                button.disabled = true;
            }

            const response = await fetch(`${this.urlValue}?range=${encodeURIComponent(range)}`);
            if (!response.ok) {
                throw new Error(`Request failed with status ${response.status}`);
            }

            const payload = await response.json();
            const labels = Array.isArray(payload.labels) ? payload.labels : [];
            const data = Array.isArray(payload.data) ? payload.data : [];

            this.chart.data.labels = labels;
            this.chart.data.datasets[0].data = data;
            this.chart.update();
        } catch (error) {
            console.error('Failed to update chart:', error);
        } finally {
            if (button) {
                button.disabled = false;
            }
        }
    }
}
