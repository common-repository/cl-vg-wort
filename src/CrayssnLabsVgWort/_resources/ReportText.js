class ReportText {

    ajaxRequestUrl = null;
    pixels = [];
    messageWrapper = null;

    constructor(options) {

        this.ajaxRequestUrl = options.ajaxRequestUrl;
        this.pixels = options.pixels;
        this.messageWrapper = document.getElementById(options.messageWrapperId);
    }

    start() {

        const self = this;

        setTimeout(function () {
            self.run();
        }, 500);
    }

    run() {

        this.addMessage('Start reporting ' + this.pixels.length + ' texts', 'success');

        for (let i = 0; i < this.pixels.length; ++i)
        {
            this.reportText(this.pixels[i]);
        }

        setTimeout(function () {
            location.reload();
        }, 2000);
    }

    reportText(pixelIdentifier)
    {
        const self = this;

        const xhr = new XMLHttpRequest();
        xhr.open("POST", this.ajaxRequestUrl, true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.onload = function () {
            if (xhr.status === 200) {
                const response = JSON.parse(xhr.response);

                self.addMessage(response.message, response.status)
            }
        };
        xhr.send("action=vg_wort_report_text&pixel=" + pixelIdentifier);
    }

    addMessage(messageString, type = null) {
        const message = document.createElement('div');

        message.classList.add('notice');

        if(type !== null)
        {
            message.classList.add('notice-' + type);
        }

        message.innerHTML = `<p>${messageString}</p>`;

        this.messageWrapper.append(message);
    }
}