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
            totals.grand_total = value * this.allInstallments().find(installment => installment.value == value)?.price
            this.totals(totals)
        },

        getTotalLabel: function() {
            return 'Total: ' + priceUtils.formatPrice(this.totals().grand_total)
        },

        getCcInstallments: function() {
            var self = this;

            fullScreenLoader.startLoader();
            $.when(
                installments(),
                getTotalsAction([], )
            ).done(function (transport, totals) {
                const total = totals[0].grand_total
                self.allInstallments.removeAll();

                _.map(transport[0], function (value, key) {
                    const totalPrice = value.price * value.id
                    const interestLabel = value.interest ? $t("with additional of") + ' ' + parseInt((totalPrice / total - 1) * 100) + '%' : $t("without additional") 
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

