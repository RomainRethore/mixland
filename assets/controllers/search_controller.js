import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['input', 'url', 'list'];

    static values = {
        url: String
    };
    connect() {
        this.inputTarget.value = '';
    }

    async searchMix() {
        if (this.inputTarget.value !== '') {
            fetch(`${this.urlValue}?searchTerm=${this.inputTarget.value}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                }
            }).then(response => response.json())
                .then(data => {
                    let responseList = '';
                    data.forEach(element => {
                        responseList = responseList.concat(`<div>${element.title}</div>`);
                    });
                    this.listTarget.innerHTML = responseList;
                    console.log(data);
                });
        }
        else {
            this.listTarget.innerHTML = '';
        }

    }
}