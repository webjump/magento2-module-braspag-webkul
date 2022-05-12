<?php
/**
 *
 * @author      Webjump Core Team <dev@webjump.com.br>
 * @copyright   2022 Webjump (http://www.webjump.com.br)
 * @license     http://www.webjump.com.br  Copyright
 * @link        http://www.webjump.com.br
 *
 */

namespace Braspag\Webkul\Model;

class Order extends \Magento\Sales\Model\Order
{
    /*
     * Braspag Fees
     */
    const BRASPAG_FEES = 'braspag_fees';

    /**
     * Return braspagg_fee
     *
     * @return float
     */
    public function getBraspagFees()
    {
        return $this->getData(self::BRASPAG_FEES);
    }
}
