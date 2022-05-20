<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Braspag\Webkul\Block\Adminhtml\Order;

/**
 * Adminhtml sales totals block
 */
class Totals extends \Magento\Sales\Block\Adminhtml\Order\Totals
{
    /**
     * Initialize order totals array
     *
     * @return $this
     */
    protected function _initTotals()
    {
        parent::_initTotals();
        /**
         * Add Braspag Fees
        */
        if ((double)$this->getSource()->getBraspagFeesAmount() != 0) {
            $this->_totals['braspag_fees_amount'] = new \Magento\Framework\DataObject(
                [
                    'code' => 'braspag_fees_amount',
                    'field' => 'braspag_fees_amount',
                    'strong' => false,
                    'value' => $this->getSource()->getBraspagFeesAmount(),
                    'label' => __('Fees of the Card'),
                ]
            );
        }
        $this->_totals['paid'] = new \Magento\Framework\DataObject(
            [
                'code' => 'paid',
                'strong' => true,
                'value' => $this->getSource()->getTotalPaid(),
                'base_value' => $this->getSource()->getBaseTotalPaid(),
                'label' => __('Total Paid'),
                'area' => 'footer',
            ]
        );
        $this->_totals['refunded'] = new \Magento\Framework\DataObject(
            [
                'code' => 'refunded',
                'strong' => true,
                'value' => $this->getSource()->getTotalRefunded(),
                'base_value' => $this->getSource()->getBaseTotalRefunded(),
                'label' => __('Total Refunded'),
                'area' => 'footer',
            ]
        );
        $code = 'due';
        $label = 'Total Due';
        $value = $this->getSource()->getTotalDue();
        $baseValue = $this->getSource()->getBaseTotalDue();
        if ($this->getSource()->getTotalCanceled() > 0 && $this->getSource()->getBaseTotalCanceled() > 0) {
            $code = 'canceled';
            $label = 'Total Canceled';
            $value = $this->getSource()->getTotalCanceled();
            $baseValue = $this->getSource()->getBaseTotalCanceled();
        }
        $this->_totals[$code] = new \Magento\Framework\DataObject(
            [
                'code' => 'due',
                'strong' => true,
                'value' => $value,
                'base_value' => $baseValue,
                'label' => __($label),
                'area' => 'footer',
            ]
        );

        return $this;
    }
}
