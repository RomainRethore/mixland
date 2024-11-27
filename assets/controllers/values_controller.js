import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        greeting: String,
        name: String
    }

    showAlert() {
        alert(this.nameValue + ' ' + this.greetingValue);
    }
}