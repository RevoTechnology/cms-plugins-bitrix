BX.ready(function() {
    document.getElementsByName('PAY_SYSTEM_ID').forEach(function (a) {
        if (parseInt(a.value) === parseInt(REVO_PAY_SYSTEM_ID)) {
            BX.bind(a.parentNode, 'click', function () {
                let successCallback = function(data) {
                    window.open(data.url);
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
            });
        }
    });
});