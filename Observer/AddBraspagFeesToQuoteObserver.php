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
    const PAYMENT_METHOD = 'braspag_pagador_creditcard';

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
        $ccInstallments = $payment
            ->getData('additional_information')['cc_installments'];
        $method = $payment
            ->getData('method');

        if ($this->installmentsConfig->isActive()
            && $method == self::PAYMENT_METHOD
            && $ccInstallments > (int) $this->installmentsConfig->getInstallmentsMaxWithoutInterest()
            && (bool) $interestRate = $this->installmentsConfig->getInterestRate()
        ) {
            $newBaseGrandTotal = $this
                ->calcTotalPriceWithInterest($ccInstallments, $quote->getBaseGrandTotal(), $interestRate);
            $newGrandTotal = $this
                ->calcTotalPriceWithInterest($ccInstallments, $quote->getGrandTotal(), $interestRate);
            $newBaseSubtotal = $this
                ->calcTotalPriceWithInterest($ccInstallments, $quote->getBaseSubtotal(), $interestRate);
            $newSubtotal = $this
                ->calcTotalPriceWithInterest($ccInstallments, $quote->getSubtotal(), $interestRate);
            $braspagFees = $this
                ->totalInterestRate($quote->getBaseGrandTotal(), $newBaseGrandTotal);
            $quote->setBaseGrandTotal($newBaseGrandTotal)
                ->setGrandTotal($newGrandTotal)
                ->setBaseSubtotal($newBaseSubtotal)
                ->setSubtotal($newSubtotal)
                ->setBraspagFees($braspagFees);
            $quote->collectTotals();
            $this->quoteRepository
                ->save($quote);
        }
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
     * Total Interest Rate
     *
     * @param mixed $total
     * @param mixed $totalWithInterestRate
     * @return void
     */
    private function totalInterestRate($total, $totalWithInterestRate)
    {
        return (($totalWithInterestRate/ $total) - 1) * 100;
    }
}
