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
            $newShippingAmount = $this->getNewShippingAmount($order, $braspagFees);

            $order->setBaseGrandTotal($quote->getBaseGrandTotal())
                ->setGrandTotal($quote->getGrandTotal())
                ->setBraspagFees($braspagFees)
                ->setBraspagFeesAmount($quote->getBraspagFeesAmount())
                ->setBaseShippingAmount($newShippingAmount)
                ->setShippingAmount($newShippingAmount);

            $this->setOrderItemsWithInterestRate($order);
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
            ->getData('additional_information')['cc_installments'];
    }

    /**
     * Get new Shipping amount according with braspag_fees
     *
     * @param mixed $order
     * @param mixed $braspagFees
     * @return void
     */
    private function getNewShippingAmount($order, $braspagFees)
    {
        return $order->getShippingAmount() * (1 + ($braspagFees/100));
    }

    /**
     * Get all the new prices according with braspag fees
     *
     * @param mixed $orderItem
     * @param mixed $interestRate
     * @return void
     */
    private function getNewPricesWithBraspagFees($orderItem, $interestRate)
    {
        $baseRowTotal = $this
            ->calcTotalPriceWithInterest($orderItem->getBaseRowTotal(), $interestRate);
        $rowTotal = $this
            ->calcTotalPriceWithInterest($orderItem->getRowTotal(), $interestRate);
        return [
            $baseRowTotal,
            $rowTotal
        ];
    }

    /**
     * Calculate total price according with the interest rate
     *
     * @param mixed $total
     * @param mixed $interestRate
     * @return void
     */
    private function calcTotalPriceWithInterest($total, $interestRate)
    {
        return $total * (1 + ($interestRate/100));
    }

    /**
     * Total Interest Rate
     *
     * @param mixed $total
     * @param mixed $totalWithInterestRate
     * @return void
     */
    private function totalInterestRateAmount($total, $totalWithInterestRate)
    {
        return ($totalWithInterestRate -  $total);
    }

    private function setOrderItemsWithInterestRate($order)
    {
        $braspagFees = $order->getBraspagFees();
        $orderItems = $order->getItems();
        foreach ($orderItems as $item) {
            list($baseRowTotal, $rowTotal) = $this
                ->getNewPricesWithBraspagFees($item, $braspagFees);
            $braspagFeesAmount = $this
                ->totalInterestRateAmount($item->getRowTotal(), $rowTotal);
            $item->setData('base_row_total', $baseRowTotal);
            $item->setData('row_total', $rowTotal);
            $item->setData('braspag_fees', $braspagFees);
            $item->setData('braspag_fees_amount', $braspagFeesAmount);
        }
    }
}
