<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Braspag\Webkul\Block\Order\Invoice;

/**
 * @api
 * @since 100.0.2
 */
class Totals extends \Magento\Sales\Block\Order\Invoice\Totals
{
    /**
     * Initialize order totals array
     *
     * @return $this
     */
    protected function _initTotals()
    {
        parent::_initTotals();
        $this->removeTotal('base_grandtotal');
        
        /**
        * Add Braspag Fees
        */
        if ((double)$this->getSource()->getBraspagFeesAmount() != 0) {
            $total = new \Magento\Framework\DataObject(
                [
                   'code' => 'braspag_fees_amount',
                   'value' => $this->getSource()->getBraspagFeesAmount(),
                   'label' => __('Fees of the Card'),
               ]
            );
            $this->addTotal($total);
        }
        return $this;
    }
}
