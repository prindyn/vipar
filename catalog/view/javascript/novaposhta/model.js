var np = (function () {

    return {
        serviceType: '',

        initialize: function () {
            this.set('serviceType', $('[name=shipping_method]').find(':selected').val().split('.'));
        },

        settlements: function () {
            $('#shipping_address_city').select2({
                ajax: {
                    url: 'index.php?route=extension/shipping/novaposhta/settlements',
                    data: function (params) {
                        return { search: params.term };
                    },
                    processResults: function (data) {
                        return { results: data };
                    }
                }
            });
        },

        warehouses: function (settlementRef = '') {
            $('#shipping_address_address_1').select2();

            if (settlementRef.length) {
                $.get('index.php?route=extension/shipping/novaposhta/warehouses&settlementRef=' + settlementRef, function(data) {
                    $.each(data, function(k, v) {
                        var newOption = new Option(v.id, v.text, false, false);
                        $('#shipping_address_address_1').append(newOption);
                    });
                });
                $('#shipping_address_address_1').trigger('change');
            }
        },

        set: function (prop, value) {
            this[prop] = value;
        }
    }
})($);

window.onload = function () {
    np.initialize();
    np.settlements();
    np.warehouses();
}

$(document).on('change', '[name=shipping_method]', function () {
    np.initialize();
});

$(document).on('select2:select', '#shipping_address_city', function (e) {
    var data = e.params.data;
    np.warehouses(data.ref);
});