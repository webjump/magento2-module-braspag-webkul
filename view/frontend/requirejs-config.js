var config = {
    paths: {
        'Webjump_BraspagPagador/payment/cc-form': 'Braspag_Webkul/payment/cc-form'
    },
    config: {
        mixins: {
            'Webjump_BraspagPagador/js/view/payment/method-renderer/creditcard': {
                'Braspag_Webkul/js/view/payment-method-renderer/creditcard-mixin': true
            }
        }
    }
}