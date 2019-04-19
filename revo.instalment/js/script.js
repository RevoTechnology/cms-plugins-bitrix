function revoModal() {
    var modal = document.getElementById('revo-modal-window');
    return modal;
}

function tryToClickAddCart() {
    var node = false;
    if (window.buyBtnSelector) {
        if (window.buyBtnSelector[0] === '.') {
            var className = window.buyBtnSelector.replace('.', '');
            node = document.getElementsByClassName(className)[0];
        }
        if (window.buyBtnSelector[0] === '#') {
            var idName = window.buyBtnSelector.replace('#', '');
            node = document.getElementById(idName);
        }
    } else {
        node = document.getElementsByClassName('product-item-detail-buy-button')[0];
    }

    node && node.click();
}

function revoShowModal(fastBuyMode, orderModeUrl) {
    let successCallback = function(data) {
        if (data.url) {
            REVO.Form.show(data.url, '#revo-iframe-container');

            revoModal().style.display = 'block';
            window.revoSent = false;
            window.ORDER_MODE_URL = orderModeUrl;
        } else {
            if (fastBuyMode) {
                tryToClickAddCart();
            }

            if (orderModeUrl) {
                REVO.Form.show(orderModeUrl, '#revo-iframe-container');

                revoModal().style.display = 'block';
                window.ORDER_MODE = true;
            }
        }
    };

    let failureCallback = function () {

    };

    let data = {
        'action': 'register'
    };

    BX.ajax({
        timeout: 120,
        method: 'POST',
        dataType: 'json',
        url: '/ajax/revo.instalment/ajax.php',
        data:  data,
        onsuccess: successCallback,
        onfailure: failureCallback
    });
}

BX.ready(function() {
    var INSTALMENT_PERIOD = 12;


    window.onmessage = function(e) {
        console.log(e);
        return;
    };

    if (window.revoLoaded) return;
    window.revoLoaded = true;

    updatePrice();

    BX.bindDelegate(
        document.body, 'click', {className: 'js-rvo-buy-link' },
        function(e){
            if(!e) {
                e = window.event;
            }
            window.productDetailMode = true;
            window.buyBtnSelector = this.dataset.buybtn;
            revoShowModal(true);
            return BX.PreventDefault(e);
        }
    );

    if (REVO && REVO.Form) {
        REVO.Form.onClose(function() {
            revoModal().style.display = 'none';
        });

        REVO.Form.onResult(function(result) {
            revoModal().style.display = 'none';
            if (window.productDetailMode) {
                tryToClickAddCart();
            }

            if (window.ORDER_MODE) {
                location.href = REVO_ORDERS_URL;
            } else if (window.ORDER_MODE_URL) {
                REVO.Form.show(window.ORDER_MODE_URL, '#revo-iframe-container');

                revoModal().style.display = 'block';
            }
        });
    }

    setInterval(function () {
        document.getElementsByName('PAY_SYSTEM_ID').forEach(function (a) {
            if (parseInt(a.value) === parseInt(REVO_PAY_SYSTEM_ID)) {
                BX.bind(a.parentNode, 'click', function () {
                    if (window.revoSent) return;
                    window.revoSent = true;

                    revoShowModal();
                });
            }
        });
    }, 1000);

    BX.ajax({
        method: 'GET',
        dataType: 'html',
        url: '/local/modules/revo.instalment/html/modal.template.html',
        onsuccess: function (data) {
            var doc = new DOMParser().parseFromString(data, "text/html");
            BX.append(doc.body.firstChild, document.body);
            BX.bind(document.getElementById('revo-modal-window'), 'click', function () {
                this.style.display = 'none' ;
            });

            var event = new Event('revo_modal_ready');
            document.dispatchEvent(event);
        }
    });

    function updatePrice() {
        var priceEl = document.getElementsByClassName('product-item-detail-price-current')[0];
        if (priceEl) {
            var price = parseFloat(document.getElementsByClassName('product-item-detail-price-current')[0].innerText.replace(/[^0-9]/, ''));
            var btnEl = document.getElementsByClassName('product-item-detail-buy-button')[0];

            if (btnEl && price >= REVO_MIN_PRICE) {
                var priceMonthly = Math.round(price / INSTALMENT_PERIOD);
                var link = document.createElement('a');

                link.innerHTML = BX.message('REVO_BUY_DETAIL').replace('#PRICE#', priceMonthly);
                link.href = '#';
                link.className = 'js-rvo-buy-link rvo-buy-link';
                btnEl
                    .parentElement
                    .appendChild(link);
            }
        }
    }


    function fastOrder() {
        var data = {};

        BX.ajax({
            method: 'POST',
            dataType: 'json',
            url: '/ajax/revo.instalment/ajax.php',
            data:  data,
            onsuccess: function (data) {
                location.href = data.url;
            }
        });
    }
});