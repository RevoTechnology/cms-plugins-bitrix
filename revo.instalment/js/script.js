BX.ready(function() {
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
            showModal();
            return BX.PreventDefault(e);
        }
    );

    document.getElementsByName('PAY_SYSTEM_ID').forEach(function (a) {
        if (parseInt(a.value) === parseInt(REVO_PAY_SYSTEM_ID)) {
            BX.bind(a.parentNode, 'click', function () {
                showModal();
            });
        }
    });

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
        }
    });


    function showModal(iframeLink) {
        let successCallback = function(data) {
            var iframe = BX.create('iframe', {
                    attrs: {
                        'src': data.url
                    }
                }),
                container = document.getElementById('revo-iframe-container');

            BX.append(iframe, container);

            var modal = document.getElementById('revo-modal-window');
            modal.style.display = 'block';
        };
        let failureCallback = function () {

        };
        let data = {
            'action': 'registration_url'
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

    function updatePrice() {
        var priceEl = document.getElementsByClassName('product-item-detail-price-current')[0];
        if (priceEl) {
            var price = parseFloat(document.getElementsByClassName('product-item-detail-price-current')[0].innerText);
            var btnEl = document.getElementsByClassName('product-item-detail-buy-button')[0];

            if (btnEl) {
                var priceMonthly = Math.round(price / 3);
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
});