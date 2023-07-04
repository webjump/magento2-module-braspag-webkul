define([
    'jquery',
    'Webjump_BraspagPagador/js/action/installments',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/action/get-totals',
    'Magento_Catalog/js/price-utils',
    'Magento_SalesRule/js/model/coupon',
    'mage/translate'
],function ($, installments,fullScreenLoader, quote, getTotalsAction, priceUtils, coupon, $t) {
    'use strict';

    var mixin = {
        defaults: {
            template: 'Braspag_Webkul/payment/creditcard',
            totals: quote.getTotals()
        },

        initialize: function () {
            this._super();
            coupon.isApplied.subscribe(newValue => {
                this.getCcInstallments()
            })
        },

        updateTotals: function(component, event) {
            const totals = this.totals()
            const value = event.target.value
            if (value === '') {
                getTotalsAction([], )
                return;
            }
            const installment = this.allInstallments().find(installment => installment.value == value);
            if (!installment || !installment.price) {
                return;
            }
            totals.grand_total = installment.price
            this.totals(totals)
        },

        getCcInstallments: function() {
            var self = this;

            fullScreenLoader.startLoader();
            $.when(
                installments(),
            ).done(function (transport) {
                self.allInstallments.removeAll();

                _.map(transport, function (value, key) {
                    const totalPrice = value.price * value.id
                    const interestLabel = value.interest ? $t("with interest*") : $t("without interest") 
                    self.allInstallments.push({
                        'value': value.id,
                        'installments': `${value.id}x ${priceUtils.formatPrice(value.price)} ${interestLabel} (${priceUtils.formatPrice(totalPrice)})`,
                        'price': value.price
                    });
                });


            }).always(function () {
                fullScreenLoader.stopLoader();
            });
        },
    };

    return function (target) { 
        return target.extend(mixin);
    };
});

