import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['input', 'list'];
    addTodo() {
        if (this.inputTarget.value === '') {
            alert('Please enter a task');
            return;
        }
        this.listTarget.innerHTML += '<li>' + this.inputTarget.value + '</li>';
        this.inputTarget.value = '';
    }
}