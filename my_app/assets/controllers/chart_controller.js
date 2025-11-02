import { Controller } from '@hotwired/stimulus';
import Chart from 'chart.js/auto';

/* stimulusFetch: 'lazy' */

export default class extends Controller {
    static values = {
        url: String,
        labels: Array,
        data: Array,
        categories: Array,
    };

    static targets = ['category', 'product'];

    connect() {
        this.registeredHandlers = [];
        this.categoryOptions = this.hasCategoriesValue ? this.categoriesValue : [];
        this.selectedCategory = 'all';
        this.selectedProduct = 'all';
        this.currentRange = null;

        this.initializeFilters();
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

    initializeFilters() {
        if (this.hasCategoryTarget) {
            this.selectedCategory = this.categoryTarget.value || 'all';
        }

        if (this.hasProductTarget) {
            this.updateProductOptions(this.selectedCategory);
        }
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

        if (!this.currentRange) {
            this.currentRange = this.getDefaultRange();
        }
    }

    unregisterButtons() {
        this.registeredHandlers.forEach(({ button, handler }) => {
            button.removeEventListener('click', handler);
        });
        this.registeredHandlers = [];
    }

    onCategoryChange(event) {
        this.selectedCategory = event.target.value || 'all';
        this.updateProductOptions(this.selectedCategory);
        this.updateChart();
    }

    onProductChange(event) {
        this.selectedProduct = event.target.value || 'all';
        this.updateChart();
    }

    updateProductOptions(categoryId) {
        if (!this.hasProductTarget) {
            return;
        }

        const select = this.productTarget;
        select.innerHTML = '';

        if (!categoryId || categoryId === 'all') {
            select.append(new Option('Select a category first', 'all'));
            select.disabled = true;
            this.selectedProduct = 'all';
            return;
        }

        const category = this.categoryOptions.find(
            (item) => String(item.id) === String(categoryId)
        );
        const products = category && Array.isArray(category.products)
            ? category.products
            : [];

        select.append(new Option('All products', 'all'));
        products.forEach((product) => {
            select.append(new Option(product.name, String(product.id)));
        });

        select.disabled = false;
        select.value = 'all';
        this.selectedProduct = 'all';
    }

    getDefaultRange() {
        const preferred = this.element.querySelector('[data-range="30"]');
        if (preferred && preferred.dataset.range) {
            return preferred.dataset.range;
        }

        const firstButton = this.element.querySelector('[data-range]');
        if (firstButton && firstButton.dataset.range) {
            return firstButton.dataset.range;
        }

        return '30';
    }

    buildQueryString(range) {
        const params = new URLSearchParams();

        if (range) {
            params.set('range', range);
        }

        if (this.selectedCategory && this.selectedCategory !== 'all') {
            params.set('category', this.selectedCategory);
        }

        if (this.selectedProduct && this.selectedProduct !== 'all') {
            params.set('product', this.selectedProduct);
        }

        return params.toString();
    }

    async updateChart(range = null, button = null) {
        if (!this.hasUrlValue) {
            return;
        }

        if (range !== null && range !== undefined) {
            this.currentRange = String(range);
        } else if (!this.currentRange) {
            this.currentRange = this.getDefaultRange();
        }

        const query = this.buildQueryString(this.currentRange);
        const url = query ? `${this.urlValue}?${query}` : this.urlValue;

        try {
            if (button) {
                button.disabled = true;
            }

            const response = await fetch(url);
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
