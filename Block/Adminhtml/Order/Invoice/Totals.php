<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Braspag\Webkul\Block\Adminhtml\Order\Invoice;

/**
 * Adminhtml order invoice totals block
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 100.0.2
 */
class Totals extends \Magento\Sales\Block\Adminhtml\Totals
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
        if ((double)$this->getSource()->getBraspagFees() != 0) {
            $this->_totals['braspag_fees'] = new \Magento\Framework\DataObject(
                [
                    'code' => 'braspag_fees',
                    'field' => 'braspag_fees',
                    'strong' => false,
                    'value' => $this->getSource()->getBraspagFees(),
                    'label' => __('Fees of the Card'),
                ]
            );
        }
        return $this;
    }
}
