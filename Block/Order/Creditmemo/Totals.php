<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Braspag\Webkul\Block\Order\Creditmemo;

/**
 * @api
 * @since 100.0.2
 */
class Totals extends \Magento\Sales\Block\Order\Creditmemo\Totals
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
        if ((double)$this->getSource()->getOrder()->getBraspagFeesAmount() != 0) {
            $total = new \Magento\Framework\DataObject(
                [
                    'code' => 'braspag_fees_amount',
                    'value' => $this->getSource()->getOrder()->getBraspagFeesAmount(),
                    'label' => __('Fees of the Card'),
                ]
            );
            $this->addTotal($total);
        }

        if ((double)$this->getSource()->getAdjustmentPositive()) {
            $total = new \Magento\Framework\DataObject(
                [
                    'code' => 'adjustment_positive',
                    'value' => $this->getSource()->getAdjustmentPositive(),
                    'label' => __('Adjustment Refund'),
                ]
            );
            $this->addTotal($total);
        }
        if ((double)$this->getSource()->getAdjustmentNegative()) {
            $total = new \Magento\Framework\DataObject(
                [
                    'code' => 'adjustment_negative',
                    'value' => $this->getSource()->getAdjustmentNegative(),
                    'label' => __('Adjustment Fee'),
                ]
            );
            $this->addTotal($total);
        }

        return $this;
    }
}
