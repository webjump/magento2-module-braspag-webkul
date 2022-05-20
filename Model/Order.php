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
    const BRASPAG_FEES_AMOUNT = 'braspag_fees_amount';

    /**
     * Return braspagg_fee
     *
     * @return float
     */
    public function getBraspagFeesAmount()
    {
        return $this->getData(self::BRASPAG_FEES_AMOUNT);
    }
}
