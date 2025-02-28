import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['input', 'url', 'list'];

    static values = {
        url: String,
        imagepath: String,
        audiopath: String
    };
    connect() {
        this.inputTarget.value = '';
        console.log(this.imagepathValue);
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
                        responseList = responseList.concat(`
                            <div class="mixBox">
                            <h2>${element.title}</h2>
                            <h3>Uploaded by:${element.user}</h3>
                            <img src=${this.imagepathValue + element.cover} alt="">
                            <audio controls src=${this.audiopathValue + element.audio}></audio>
                            <p>${element.description}</p>
                            </div>`);
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