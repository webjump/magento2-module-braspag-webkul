<?php
/**
 * @author      Webjump Core Team <dev@webjump.com.br>
 * @copyright   2021 Webjump (http://www.webjump.com.br)
 * @license     http://www.webjump.com.br  Copyright
 *
 * @link        http://www.webjump.com.br
 */

namespace Braspag\Webkul\Plugin;

/**
 * Class PaymentSplitVendorSourceListObserver
 * @package Braspag\Webkul\Observer
 */
class PaymentSplitVendorSourceListPlugin
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
