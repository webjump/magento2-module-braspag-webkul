<?php

namespace Braspag\Webkul\Gateway\Transaction\PaymentSplit\Config;

use Webjump\BraspagPagador\Gateway\Transaction\Base\Config\Config as BaseConfig;

/**
 * Class Config
 * @package Webjump\BraspagPagador\Gateway\Transaction\PaymentSplit\Config
 */
class Config extends BaseConfig implements ConfigInterface
{
    /**
     * @return bool
     */
    public function getPaymentSplitMarketPlaceVendorPaymentTypesToApplyWebkulCommission()
    {
        return $this->_getConfig(self::CONFIG_XML_BRASPAG_PAYMENTSPLIT_MARKETPLACEVENDOR_PAYMENT_TYPES_TO_APPLY_WEBKUL_COMMISSION);
    }
}
