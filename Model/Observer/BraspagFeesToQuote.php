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

namespace Braspag\Webkul\Model\Observer;

use Webjump\BraspagPagador\Gateway\Transaction\Base\Config\InstallmentsConfigInterface;
use Braspag\Webkul\Model\BraspagFees\TaxCalculator;

class BraspagFeesToQuote
{

    /** @var InstallmentsConfigInterface */
    private $installmentsConfig;

    /** @var TaxCalculator */
    private $taxCalculator;

    /**
     * Construct method
     *
     * @param InstallmentsConfigInterface $installmentsConfig
     */
    public function __construct(
        InstallmentsConfigInterface $installmentsConfig,
        TaxCalculator $taxCalculator
    ) {
        $this->installmentsConfig = $installmentsConfig;
        $this->taxCalculator = $taxCalculator;
    }

    /**
     * Get quote and payment information
     *
     * @param mixed $observer
     * @return array
     */
    public function getPaymentInformation($observer)
    {
        $quote = $observer
            ->getData('quote');
        $payment = $quote
            ->getPayment();
        $method = $payment
            ->getData('method');
        $ccInstallments = $this
            ->getCCInstallments($payment);
        return [$quote, $method, $ccInstallments];
    }

    /**
     * Get base grand total and grand total of quote according installments
     *
     * @param mixed $quote
     * @param mixed $ccInstallments
     * @return array
     */
    public function getNewQuoteInformation($quote, $ccInstallments)
    {
        $interestRate = $this->installmentsConfig
            ->getInterestRate();
        list($newBaseGrandTotal, $newGrandTotal) = $this->taxCalculator
            ->getOrderTotalsWithBraspagFees($ccInstallments, $quote, $interestRate);
        list($braspagFees, $braspagFeesAmount) = $this->taxCalculator
            ->getBraspagFeesInformation($quote->getBaseGrandTotal(), $newBaseGrandTotal);
        return [
            $newBaseGrandTotal,
            $newGrandTotal,
            $braspagFees,
            $braspagFeesAmount
        ];
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
}
