import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        end: Number,
        duration: { type: Number, default: 1200 },
        decimals: { type: Number, default: 0 },
        suffix: String,
    };

    connect() {
        this.startValue = 0;
        this.currentValue = this.startValue;
        this.startTime = null;
        window.requestAnimationFrame(this.animate.bind(this));
    }

    disconnect() {
        if (this.animationFrameId) {
            cancelAnimationFrame(this.animationFrameId);
        }
    }

    animate(timestamp) {
        if (this.startTime === null) {
            this.startTime = timestamp || performance.now();
        }

        const progress = Math.min(
            (timestamp - this.startTime) / this.durationValue,
            1
        );

        const value =
            this.startValue +
            (this.endValue - this.startValue) * this.easeOutCubic(progress);

        this.updateDisplay(value);

        if (progress < 1) {
            this.animationFrameId = window.requestAnimationFrame(
                this.animate.bind(this)
            );
        } else {
            this.updateDisplay(this.endValue);
        }
    }

    updateDisplay(value) {
        const formattedValue = this.formatValue(value);
        this.element.textContent = formattedValue;
    }

    formatValue(value) {
        const decimals = this.decimalsValue ?? 0;
        const rounded = Number.parseFloat(value).toFixed(decimals);
        if (this.hasSuffixValue) {
            return `${rounded}${this.suffixValue}`;
        }

        return rounded;
    }

    easeOutCubic(t) {
        return 1 - Math.pow(1 - t, 3);
    }
}
