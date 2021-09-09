<?php

namespace Braspag\Webkul\Gateway\Transaction\PaymentSplit\Config;

/**
 * Interface ConfigInterface
 * @package Braspag\Webkul\Gateway\Transaction\PaymentSplit\Config
 */
interface ConfigInterface
{
    const CONFIG_XML_BRASPAG_PAYMENTSPLIT_MARKETPLACEVENDOR_PAYMENT_TYPES_TO_APPLY_WEBKUL_COMMISSION = 'webjump_braspag/paymentsplit_marketplacewebkul/marketplacewebkul_payment_types_to_apply_webkul_commission';

    public function getPaymentSplitMarketPlaceVendorPaymentTypesToApplyWebkulCommission();
}
