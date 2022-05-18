<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Braspag\Webkul\Block\Adminhtml\Order\Creditmemo;

/**
 * Adminhtml order creditmemo totals block
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 100.0.2
 */
class Totals extends \Magento\Sales\Block\Adminhtml\Order\Creditmemo\Totals
{
    /**
     * Initialize creditmemo totals array
     *
     * @return $this
     */
    protected function _initTotals()
    {
        parent::_initTotals();
        /**
         * Add Braspag Fees
        */
        if ((double)$this->getSource()->getOrder()->getBraspagFees() != 0) {
            $this->addTotal(
                new \Magento\Framework\DataObject(
                    [
                        'code' => 'braspag_fees',
                        'value' => $this->getSource()->getOrder()->getBraspagFees(),
                        'base_value' => $this->getSource()->getOrder()->getBraspagFees(),
                        'label' => __('Fees of the Card'),
                    ]
                )
            );
        }
        $this->addTotal(
            new \Magento\Framework\DataObject(
                [
                    'code' => 'adjustment_positive',
                    'value' => $this->getSource()->getAdjustmentPositive(),
                    'base_value' => $this->getSource()->getBaseAdjustmentPositive(),
                    'label' => __('Adjustment Refund'),
                ]
            )
        );
        $this->addTotal(
            new \Magento\Framework\DataObject(
                [
                    'code' => 'adjustment_negative',
                    'value' => $this->getSource()->getAdjustmentNegative(),
                    'base_value' => $this->getSource()->getBaseAdjustmentNegative(),
                    'label' => __('Adjustment Fee'),
                ]
            )
        );
        return $this;
    }
}
