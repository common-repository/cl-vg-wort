class VgWort {

    constructor(options)
    {
        this.ajaxRequestUrl = options.ajaxRequestUrl;
        this.minContentLength = options.minContentLength;
        this.postId = options.postId;
    }
    integrateCounter()
    {
        setTimeout(() => this.analyse(), 1000)
    }

    analyse()
    {
        if (this.trackingPixelAlreadyExists()) {
            this.adminMessage('A VG Wort tracking pixel has already been found. The integration of the current pixel was cancelled.');

            this.transferExistingTrackingPixelData();

            return;
        }

        const fullContent = this.getExtractFullTextContent();

        if (fullContent.length >= this.minContentLength) {
            this.orderCounter(fullContent.length);
        } else {
            this.adminMessage(`The content of the page is less than the minimum length of ${this.minContentLength} characters. No counter is integrated.`)
        }
    }

    getExtractFullTextContent()
    {

        const blocks = this.getContentBlocks();
        let content, fullContent = '';

        for (let i = 0; i < blocks.length; ++i) {
            content = this.extractTextContent(blocks[i]);

            if (content === false) {
                continue;
            }

            fullContent = fullContent + "\n" + content;
        }

        return fullContent;
    }

    getContentBlocks()
    {
        const query = ['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'].map((tag) => {
            return tag + ':not(footer *, header *, nav *, aside *)';
        }).join(', ');

        const elements = document.querySelectorAll(query);
        let parents = [];
        let i, ii;

        for (i = 0; i < elements.length; ++i) {
            if (parents.indexOf(elements[i].parentNode) === -1) {
                parents.push(elements[i].parentNode)
            }
        }

        for (i = 0; i < parents.length; ++i) {
            for (ii = 0; ii < parents.length; ++ii) {
                if (parents[i] === parents[ii]) {
                    continue;
                }

                if (parents[i].contains(parents[ii])) {
                    parents.splice(ii, 1);
                    i = 0;
                    ii = 0;
                }
            }
        }

        return parents;
    }
    extractTextContent(block)
    {
        const helperElement = document.createElement('div');

        helperElement.innerHTML = block.innerHTML;

        this.removeNodeByTag(helperElement, 'figure');
        this.removeNodeByTag(helperElement, 'figcaption');

        const content = helperElement.textContent.trim().replace(/(\r\n|\n|\r)/gm, '').replace(/\s\s/g, ' ').replace(/\s\s/g, ' ').replace(/\s\s/g, ' ');

        if (block.querySelectorAll('h1, h2, h3, h4, h5, h6').length === 0 && content.length < 100) {
            return false;
        }

        return content;
    }
    orderCounter(contentLength)
    {
        const xhr = new XMLHttpRequest();
        xhr.open("POST", this.ajaxRequestUrl, true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.onload = function () {
            if (xhr.status === 200) {
                const response = JSON.parse(xhr.response);
                const img = document.createElement('IMG');

                img.src = response.counter;

                document.body.appendChild(img);
            }
        };
        xhr.send("action=vg_wort_order_counter&content-length=" + contentLength + "&post-id=" + this.postId);
    }

    transferExistingTrackingPixelData()
    {

        const pixelElement = document.querySelector("img[src*='vgwort.de']");

        const matches = pixelElement.src.match(/\/\/([^/]+).*\/([^/]+)$/);

        if (matches.length > 0) {
            const xhr = new XMLHttpRequest();
            xhr.open("POST", this.ajaxRequestUrl, true);
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhr.onload = () => {
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.response);

                    this.adminMessage(response.status === 'success' ? 'Pixel was transferred successful.' : 'Pixel transfer failed.');
                }
            };
            xhr.send("action=vg_wort_transfer_pixel&domain=" + matches[1] + "&public-identifier=" + matches[2] + "&post-id=" + this.postId);
        }
    }

    trackingPixelAlreadyExists()
    {
        return document.querySelector("img[src*='vgwort.de']") !== null;
    }

    adminMessage(message)
    {
        if (!document.body.classList.contains('admin-bar')) {
            return;
        }

        console.group('[VGW-T(A)OM]');
        console.info(message);
        console.groupEnd();
    }

    removeNodeByTag(helperElement, tag)
    {
        const figures = helperElement.querySelectorAll(tag);

        for (let i = 0; i < figures.length; ++i) {
            figures[i].parentNode.removeChild(figures[i]);
        }
    }
}