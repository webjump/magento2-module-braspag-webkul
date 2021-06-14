<?php
/**
 * Copyright © Braspag, Inc. All rights reserved.
 */

namespace Braspag\Webkul\Plugin;

/**
 * Class PaymentSplitVendorSourceListObserver
 * @package Braspag\Webkul\Observer
 */
class PaymentSplitVendorSourceList
{
    const PAYMENT_SPLIT_MARKETPLACE_VENDOR_CODE_WEBKUL = 'webkul';
    const PAYMENT_SPLIT_MARKETPLACE_VENDOR_NAME_WEBKUL = 'Webkul';

    /**
     * @param \Webjump\BraspagPagador\Model\Source\PaymentSplitMarketplaceVendor $subject
     * @param $result
     * @return array
     */
    public function afterToOptionArray(\Webjump\BraspagPagador\Model\Source\PaymentSplitMarketplaceVendor $subject, $result)
    {
        $result[] = [
            'value' => self::PAYMENT_SPLIT_MARKETPLACE_VENDOR_CODE_WEBKUL,
            'label' => self::PAYMENT_SPLIT_MARKETPLACE_VENDOR_NAME_WEBKUL,
        ];

        return $result;
    }
}
