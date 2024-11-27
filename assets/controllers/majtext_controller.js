import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['text'];
    updateText(event) {
        this.textTarget.innerText = 'Nouveau texte';
    }
}