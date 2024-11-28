import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['body'];

    connect() {
        if (localStorage.getItem('theme') === 'dark') {
            this.bodyTarget.classList.add('dark');
        }
    }

    toggleTheme() {
        this.bodyTarget.classList.toggle('dark');
        this.persistTheme();
    }
    persistTheme() {
        localStorage.setItem('theme', this.bodyTarget.classList.contains('dark') ? 'dark' : '');
    }
}