class Search {

    urls = [];
    processedUrls = [];

    constructor(options) {

        this.urls = options.urls;
        this.messageWrapper = document.getElementById(options.messageWrapperId);
    }

    start() {

        const self = this;

        setTimeout(function () {
            self.run();
        }, 500);
    }

    run() {

        this.addMessage('Process started - <strong>'  + this.urls.length + ' URLs</strong> are in the queue');

        this.iframe = document.createElement('iframe');

        this.iframe.style.position = 'absolute';
        this.iframe.style.opacity = '0';
        this.iframe.style.zIndex = '-1';

        this.iframe.addEventListener('load', () => {
            setTimeout(() => {
                this.loadIframe();
            }, 2000);
        });

        document.body.appendChild(this.iframe);

        this.loadIframe();
    }

    loadIframe() {

        if(this.urls.length === 0) {

            this.addMessage('Process completed', 'success');

            setTimeout(() => {
                location.reload();
            }, 2000);

            return;
        }

        const url = this.urls.pop();

        this.processedUrls.push(url);

        if(url.indexOf(location.origin) === -1)
        {
            this.addMessage(this.processedUrls.length + '. URL - Analyse skipped: ' + url, 'error');

            this.loadIframe();

            return ;
        }

        this.addMessage(this.processedUrls.length + '. Url - Analyse started: ' + url, 'success');

        this.iframe.src = url + (url.indexOf('?') >= 0 ? '&' : '?') + 'verifyPixel=true';
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