import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        url: String,
        interval: { type: Number, default: 10000 },
    };

    static targets = ['count', 'badge'];

    connect() {
        this.poll();
        this.timer = window.setInterval(() => this.poll(), this.intervalValue);
    }

    disconnect() {
        if (this.timer) {
            clearInterval(this.timer);
        }
    }

    async poll() {
        if (!this.hasUrlValue) {
            return;
        }

        try {
            const response = await fetch(this.urlValue);
            if (!response.ok) {
                throw new Error(`Request failed with status ${response.status}`);
            }

            const payload = await response.json();
            const pending = Number.parseInt(payload.pending ?? 0, 10);

            if (this.hasCountTarget) {
                this.countTarget.textContent = pending;
            }

            if (this.hasBadgeTarget) {
                if (pending > 0) {
                    this.badgeTarget.textContent = `${pending} pending`;
                    this.badgeTarget.className = 'badge bg-warning text-dark';
                } else {
                    this.badgeTarget.textContent = 'All caught up';
                    this.badgeTarget.className = 'badge bg-success';
                }
            }
        } catch (error) {
            console.error('Failed to fetch pending orders status:', error);
        }
    }
}
