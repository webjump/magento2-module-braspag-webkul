<?php
/**
 *
 * @author      Webjump Core Team <dev@webjump.com.br>
 * @copyright   2022 Webjump (http://www.webjump.com.br)
 * @license     http://www.webjump.com.br  Copyright
 * @link        http://www.webjump.com.br
 *
 */

declare(strict_types=1);

namespace Braspag\Webkul\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Webjump\BraspagPagador\Gateway\Transaction\Base\Config\InstallmentsConfigInterface;

class AddBraspagFeesToOrderObserver implements ObserverInterface
{

    /** @var string */
    const PAYMENT_METHOD = 'braspag_pagador_creditcard';

    /** @var InstallmentsConfigInterface */
    private $installmentsConfig;

    public function __construct(
        InstallmentsConfigInterface $installmentsConfig
    ) {
        $this->installmentsConfig = $installmentsConfig;
    }

    /**
     * Add/Update attributes to order according with quote
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getData('order');
        $quote = $observer->getData('quote');
        $payment = $quote
            ->getPayment();
        $method = $payment
            ->getData('method');

        if (!$quote->getId()
        ) {
            return $this;
        }

        if ($this->installmentsConfig->isActive()
            && $method == self::PAYMENT_METHOD
            && $this->getCCInstalments($payment) > (int) $this->installmentsConfig->getInstallmentsMaxWithoutInterest()
            && (bool) $this->installmentsConfig->getInterestRate()
        ) {
            $braspagFees = $quote->getBraspagFees();
    
            $order->setBaseGrandTotal($quote->getBaseGrandTotal())
                ->setGrandTotal($quote->getGrandTotal())
                ->setBraspagFees($braspagFees)
                ->setBraspagFeesAmount($quote->getBraspagFeesAmount());
            
            $this->setOrderItemsWithBraspagFees($order);
        }
    }

    /**
     * Get credit card instalments
     *
     * @param mixed $payment
     * @return void
     */
    private function getCCInstalments($payment)
    {
        return $payment
            ->getData('additional_information')['cc_installments'] ?? 1;
    }

    /**
     * Set Order Items with cc Installments fees
     *
     * @param mixed $order
     * @return void
     */
    private function setOrderItemsWithBraspagFees($order)
    {
        $braspagFees = $order->getBraspagFees();
        $orderItems = $order->getItems();
        foreach ($orderItems as $item) {
            list(
                $basePriceInclTax,
                $priceIncTax,
                $baseRowTotalInclTax,
                $rowTotalInclTax
            ) = $this->getPricesInclBraspagFees($item, $braspagFees);
            $item->setData('braspag_fees', $braspagFees)
                ->setData('base_price_incl_tax', $basePriceInclTax)
                ->setData('price_incl_tax', $priceIncTax)
                ->setData('base_row_total_incl_tax', $baseRowTotalInclTax)
                ->setData('row_total_incl_tax', $rowTotalInclTax);
        }
    }

    /**
    * Get all the new prices according with braspag fees
    *
    * @param mixed $orderItem
    * @param mixed $interestRate
    * @return void
    */
    private function getPricesInclBraspagFees($orderItem, $interestRate)
    {
        $basePriceInclTax = $this
            ->calcTotalPriceWithInterestRate($orderItem->getBasePriceInclTax(), $interestRate);
        $priceIncTax = $this
            ->calcTotalPriceWithInterestRate($orderItem->getPriceInclTax(), $interestRate);
        $baseRowTotalInclTax = $this
            ->calcTotalPriceWithInterestRate($orderItem->getBaseRowTotalInclTax(), $interestRate);
        $rowTotalInclTax = $this
            ->calcTotalPriceWithInterestRate($orderItem->getRowTotalInclTax(), $interestRate);
        return [
            $basePriceInclTax,
            $priceIncTax,
            $baseRowTotalInclTax,
            $rowTotalInclTax
        ];
    }

    /**
     * Calculate total price according with the braspag fees
     *
     * @param mixed $total
     * @param mixed $interestRate
     * @return void
     */
    private function calcTotalPriceWithInterestRate($total, $interestRate)
    {
        return $total * (1 + ($interestRate/100));
    }
}
