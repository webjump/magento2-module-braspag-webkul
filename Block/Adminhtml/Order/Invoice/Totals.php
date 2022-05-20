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
        return $this;
    }
}
