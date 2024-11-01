import $ from 'jquery';

export const generate_order_PDFs_preview = (payload: string, parent: JQuery<HTMLElement>) => {
    parent.append($('<div class="wetail-shipping-engine-order-pdf-iframe-wrapper" style="position: relative; height: 95%; width: 100%;"></div>'));

    const byteCharacters = atob(payload);
    const byteArrays = [];
    for (let i = 0; i < byteCharacters.length; i++) {
        byteArrays.push(byteCharacters.charCodeAt(i));
    }
    const byteArray = new Uint8Array(byteArrays);
    const blob = new Blob([byteArray], {type: 'application/pdf'});

    let iframe: JQuery<HTMLIFrameElement> = $('<iframe></iframe>');
    iframe.addClass('wetail-shipping-engine-order-pdf-iframe');
    iframe.attr('src', URL.createObjectURL(blob));
    iframe.attr('id', 'wetail-shipping-engine-order-pdf-iframe');
    iframe.css('position', 'relative');
    iframe.css('height', '100%');
    iframe.css('width', '100%');
    parent.find('.wetail-shipping-engine-order-pdf-iframe-wrapper').append(iframe);
};
