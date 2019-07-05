var REVO_GLOBALS = {
    'DEFAULT_BUY_BTN_CLASS': 'product-item-detail-buy-button',
    'DEFAULT_CHECKOUT_BTN_CLASS': 'basket-btn-checkout',
    'DEFAULT_DETAIL_PRICE_CLASS': 'product-item-detail-price-current',
    'DEFAULT_CART_PRICE_CLASS': 'basket-coupon-block-total-price-current',
};

function revoModal() {
    return document.getElementById('revo-modal-window');
}

function tryToClickAddCart() {
    return;

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
        node = document.getElementsByClassName(REVO_GLOBALS.DEFAULT_BUY_BTN_CLASS)[0] ||
            document.getElementsByClassName(REVO_GLOBALS.DEFAULT_CHECKOUT_BTN_CLASS)[0];
    }


    node && node.click();
}

function revoShowModal(fastBuyMode, orderModeUrl) {
    let successCallback = function(data) {
        if (data.message === 'declined') {
            alert('Вам отказано в кредитном лимите.');
            return false;
        } else if (data.url && !orderModeUrl) {
            REVO.Form.show(data.url, '#revo-iframe-container');

            revoModal().style.display = 'block';
            window.revoSent = false;
            // window.ORDER_MODE_URL = orderModeUrl;
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

    if (window.revoLoaded) return;
    window.revoLoaded = true;

    setTimeout(function () {updatePrice();}, 1000);

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
            if (window.revoCloseTrigger) {
                window.revoCloseTrigger();
            }
        });

        REVO.Form.onResult(function(result) {
            if (window.ORDER_MODE_URL) {
                REVO.Form.show(window.ORDER_MODE_URL, '#revo-iframe-container');
                window.ORDER_MODE_URL = false;
                window.ORDER_MODE = true;
                revoModal().style.display = 'block';
            } else {
                window.revoCloseTrigger = function() {
                    if (REVO_ORDERS_URL) {
                        // location.href = REVO_ORDERS_URL;
                    }
                }
            }
        });
    }

    let clickBound = false;

    setInterval(function () {
        document.getElementsByName('PAY_SYSTEM_ID').forEach(function (a) {
            if (parseInt(a.value) === parseInt(REVO_PAY_SYSTEM_ID)) {
                if (REVO_REQUEST_DECLINED) {
                    a.disabled = 'disabled';
                }
                if (!clickBound) {
                    clickBound = true;

                    BX.bindDelegate(document.body, 'click', {'callback': function(obj) {
                        let a = false;
                        for (let i in obj.childNodes) {
                            if (obj.childNodes[i].id === 'ID_PAY_SYSTEM_ID_'+REVO_PAY_SYSTEM_ID) {
                                a = true;
                            }
                        }
                        return a;
                    }}, function () {
                        if (REVO_REQUEST_DECLINED) {
                            alert('Вам отказано в кредитном лимите.');
                            return;
                        }
                        if (window.revoSent) return;
                        window.revoSent = true;

                        revoShowModal();
                    });
                }
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
        if (REVO_ADD_PRICE_BLOCK > 0) {
            var priceEl = document.getElementsByClassName(REVO_GLOBALS.DEFAULT_DETAIL_PRICE_CLASS)[0]
                || document.getElementsByClassName(REVO_GLOBALS.DEFAULT_CART_PRICE_CLASS)[0];

            if (priceEl) {
                var price = parseFloat(priceEl.innerText.replace(/[^0-9]/, ''));

                var btnEl = document.getElementsByClassName(REVO_GLOBALS.DEFAULT_BUY_BTN_CLASS)[0] ||
                    document.getElementsByClassName(REVO_GLOBALS.DEFAULT_CHECKOUT_BTN_CLASS)[0];

                if (btnEl && price >= REVO_MIN_PRICE && (REVO_MAX_PRICE > 0 && price <= REVO_MAX_PRICE)) {
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
    }

    BX.addCustomEvent('OnBasketChange', function () {
        setTimeout(function () {updatePrice();}, 1000);
    });
});