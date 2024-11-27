import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['input'];

    log() {
        console.log(this.inputTarget.value);
    };
}