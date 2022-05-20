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
use Magento\Quote\Api\CartRepositoryInterface;
use Webjump\BraspagPagador\Gateway\Transaction\Base\Config\InstallmentsConfigInterface;

class AddBraspagFeesToQuoteObserver implements ObserverInterface
{
    /** @var string */
    const CC_PAYMENT_METHOD = 'braspag_pagador_creditcard';

    /** @var InstallmentsConfigInterface */
    private $installmentsConfig;

    /** @var CartRepositoryInterface */
    private $quoteRepository;

    /**
     * Construct method
     *
     * @param InstallmentsConfigInterface $installmentsConfig
     * @param CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        InstallmentsConfigInterface $installmentsConfig,
        CartRepositoryInterface $quoteRepository
    ) {
        $this->installmentsConfig = $installmentsConfig;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Add/Update attributes in quote
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $quote = $observer->getData('quote');
        $payment = $quote
            ->getPayment();
        $method = $payment
            ->getData('method');

        if ($this->installmentsConfig->isActive()
            && $method == self::CC_PAYMENT_METHOD
            && ($ccInstallments = $this->getCCInstallments($payment)) > (int) $this->installmentsConfig->getInstallmentsMaxWithoutInterest()
            && (bool) $interestRate = $this->installmentsConfig->getInterestRate()
        ) {
            list($newBaseGrandTotal, $newGrandTotal) = $this
                ->getNewPricesWithBraspagFees($ccInstallments, $quote, $interestRate);
            list($braspagFees, $braspagFeesAmount) = $this
                ->getTotalsInterestRate($quote->getBaseGrandTotal(), $newBaseGrandTotal);

            $quote->setBaseGrandTotal($newBaseGrandTotal)
                ->setGrandTotal($newGrandTotal)
                ->setBraspagFees($braspagFees)
                ->setBraspagFeesAmount($braspagFeesAmount);

            $quote->collectTotals();
            $this->quoteRepository
                ->save($quote);
        }
    }

    /**
     * Get credit card instalments
     *
     * @param mixed $payment
     * @return void
     */
    private function getCCInstallments($payment)
    {
        return $payment
            ->getData('additional_information')['cc_installments'] ?? 1;
    }

    /**
     * Get all the new prices according with braspag fees
     *
     * @param mixed $ccInstallments
     * @param mixed $quote
     * @param mixed $interestRate
     * @return void
     */
    private function getNewPricesWithBraspagFees($ccInstallments, $quote, $interestRate)
    {
        $newBaseGrandTotal = $this
            ->calcTotalPriceWithInterest($ccInstallments, $quote->getBaseGrandTotal(), $interestRate);
        $newGrandTotal = $this
            ->calcTotalPriceWithInterest($ccInstallments, $quote->getGrandTotal(), $interestRate);
        return [
            $newBaseGrandTotal,
            $newGrandTotal
        ];
    }

    /**
     * Calc total price according with interest rate
     *
     * @param mixed $ccInstallments
     * @param mixed $total
     * @param mixed $interestRate
     * @return void
     */
    private function calcTotalPriceWithInterest($ccInstallments, $total, $interestRate)
    {
        $price = $total * $interestRate / (1 - (1 / pow((1 + $interestRate), $ccInstallments)));
        return  $ccInstallments * (float) sprintf("%01.2f", $price);
    }

    /**
     * Get interest rate and interest rate amount
     *
     * @param mixed $total
     * @param mixed $totalWithInterestRate
     * @return void
     */
    private function getTotalsInterestRate($total, $totalWithInterestRate)
    {
        $totalInterestRate = (($totalWithInterestRate/ $total) - 1) * 100;
        $totalInterestRateAmount = ($totalWithInterestRate -  $total);
        return [
            $totalInterestRate,
            $totalInterestRateAmount
        ];
    }
}
