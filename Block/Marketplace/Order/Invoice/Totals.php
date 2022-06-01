<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Braspag\Webkul\Block\Marketplace\Order\Invoice;

class Totals extends \Webkul\Marketplace\Block\Order\Invoice\Totals
{
    protected function _initTotals()
    {
        $this->_totals = [];
        $source = $this->getSource();
        $order = $this->getOrder();
        if (isset($source[0])) {
            $source = $source[0];
            $taxToSeller = $source['tax_to_seller'];
            $currencyRate = $source['currency_rate'];
            $subtotal = $source['magepro_price'];
            $adminSubtotal = $source['total_commission'];
            $shippingamount = $source['shipping_charges'];
            $refundedShippingAmount = $source['refunded_shipping_charges'];
            $couponAmount = $source['applied_coupon_amount'];
            $totaltax = $source['total_tax'];
            $totalCouponAmount = $source['coupon_amount'];
            $braspagFeesAmount = $source['braspag_fees_amount'];

            $admintotaltax = 0;
            $vendortotaltax = 0;
            if (!$taxToSeller) {
                $admintotaltax = $totaltax;
            } else {
                $vendortotaltax = $totaltax;
            }

            $totalOrdered = $this->getOrderedAmount($source);

            $vendorSubTotal = $this->getVendorSubTotal($source);

            $adminSubTotal = $this->getAdminSubTotal($source);

            $this->_totals = [];

            $this->_totals['subtotal'] = new \Magento\Framework\DataObject(
                [
                    'code' => 'subtotal',
                    'value' => $this->helper->getCurrentCurrencyPrice($currencyRate, $subtotal),
                    'label' => __('Subtotal')
                ]
            );

            $this->_totals['shipping'] = new \Magento\Framework\DataObject(
                [
                    'code' => 'shipping',
                    'value' => $this->helper->getCurrentCurrencyPrice($currencyRate, $shippingamount),
                    'label' => __('Shipping & Handling')
                ]
            );

            $this->_totals['discount'] = new \Magento\Framework\DataObject(
                [
                    'code' => 'discount',
                    'value' => $this->helper->getCurrentCurrencyPrice($currencyRate, $totalCouponAmount),
                    'label' => __('Discount')
                ]
            );

            $this->_totals['tax'] = new \Magento\Framework\DataObject(
                [
                    'code' => 'tax',
                    'value' => $this->helper->getCurrentCurrencyPrice($currencyRate, $totaltax),
                    'label' => __('Total Tax')
                ]
            );

            /**
             * Add Braspag Fees Amount
             */
            if ($braspagFeesAmount != 0) {
                $this->_totals['braspag_fees_amount'] = new \Magento\Framework\DataObject(
                    [
                        'code' => 'braspag_fees_amount',
                        'strong' => false,
                        'value' => $braspagFeesAmount,
                        'label' => __('Fees of the Card'),
                    ]
                );
            };

            $this->_totals['ordered_total'] = new \Magento\Framework\DataObject(
                [
                    'code' => 'ordered_total',
                    'strong' => 1,
                    'value' => $this->helper->getCurrentCurrencyPrice($currencyRate, $totalOrdered),
                    'label' => __('Total Ordered Amount')
                ]
            );

            if ($order->isCurrencyDifferent()) {
                $this->_totals['base_ordered_total'] = new \Magento\Framework\DataObject(
                    [
                        'code' => 'base_ordered_total',
                        'is_base' => 1,
                        'strong' => 1,
                        'value' => $totalOrdered,
                        'label' => __('Total Ordered Amount(in base currency)')
                    ]
                );
            }

            $this->_totals['vendor_total'] = new \Magento\Framework\DataObject(
                [
                    'code' => 'vendor_total',
                    'value' => $this->helper->getCurrentCurrencyPrice($currencyRate, $vendorSubTotal),
                    'label' => __('Total Vendor Amount')
                ]
            );

            if ($order->isCurrencyDifferent()) {
                $this->_totals['base_vendor_total'] = new \Magento\Framework\DataObject(
                    [
                        'code' => 'base_vendor_total',
                        'is_base' => 1,
                        'value' => $vendorSubTotal,
                        'label' => __('Total Vendor Amount(in base currency)')
                    ]
                );
            }

            $this->_totals['admin_commission'] = new \Magento\Framework\DataObject(
                [
                    'code' => 'admin_commission',
                    'value' => $this->helper->getCurrentCurrencyPrice($currencyRate, $adminSubTotal),
                    'label' => __('Total Admin Commission')
                ]
            );

            if ($order->isCurrencyDifferent()) {
                $this->_totals['base_admin_commission'] = new \Magento\Framework\DataObject(
                    [
                        'code' => 'base_admin_commission',
                        'is_base' => 1,
                        'value' => $adminSubTotal,
                        'label' => __('Total Admin Commission(in base currency)')
                    ]
                );
            }
        }
    }
    public function getOrderedAmount($source)
    {
      return $source['total_amount'];
    }
}
