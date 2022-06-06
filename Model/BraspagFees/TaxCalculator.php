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

namespace Braspag\Webkul\Model\BraspagFees;

class TaxCalculator
{

    /**
     * Get total according number of installments and configured interest rate
     *
     * @param mixed $ccInstallments
     * @param mixed $total
     * @param mixed $interestRate
     * @return float|int
     */
    public function getTotalWithInterestRate($ccInstallments, $total, $interestRate)
    {
        $price = $total * $interestRate / (1 - (1 / pow((1 + $interestRate), $ccInstallments)));
        return  $ccInstallments * (float) sprintf("%01.2f", $price);
    }

    /**
     * Get braspag fees and it real amount
     *
     * @param mixed $total
     * @param mixed $totalWithInterestRate
     * @return array
     */
    public function getBraspagFeesInformation($total, $totalWithInterestRate)
    {
        $totalInterestRate = (($totalWithInterestRate/ $total) - 1) * 100;
        $totalInterestRateAmount = ($totalWithInterestRate -  $total);
        return [
            $totalInterestRate,
            $totalInterestRateAmount
        ];
    }

    /**
     * Get total orders prices including braspag fees
     *
     * @param mixed $ccInstallments
     * @param mixed $quote
     * @param mixed $interestRate
     * @return array
     */
    public function getOrderTotalsWithBraspagFees($ccInstallments, $quote, $interestRate)
    {
        $newBaseGrandTotal = $this
            ->getTotalWithInterestRate($ccInstallments, $quote->getBaseGrandTotal(), $interestRate);
        $newGrandTotal = $this
            ->getTotalWithInterestRate($ccInstallments, $quote->getGrandTotal(), $interestRate);
        return [
            $newBaseGrandTotal,
            $newGrandTotal
        ];
    }

    /**
     * Get item order prices including braspag fees
     *
     * @param mixed $orderItem
     * @param mixed $interestRate
     * @return array
     */
    public function getItemPricesInclBraspagFees($orderItem, $interestRate)
    {
        $basePriceInclTax = $this
            ->getTotalInclBraspagFees(
                $orderItem->getBasePriceInclTax(),
                $interestRate
            );
        $priceIncTax = $this
            ->getTotalInclBraspagFees(
                $orderItem->getPriceInclTax(), $interestRate
            );
        $baseRowTotalInclTax = $this
            ->getTotalInclBraspagFees(
                ($orderItem->getBaseRowTotalInclTax() - $orderItem->getDiscountAmount()),
                $interestRate
            );
        $rowTotalInclTax = $this
            ->getTotalInclBraspagFees(
                ($orderItem->getRowTotalInclTax() - $orderItem->getDiscountAmount()),
                $interestRate
            );
        return [
            $basePriceInclTax,
            $priceIncTax,
            $baseRowTotalInclTax,
            $rowTotalInclTax
        ];
    }

    /**
     * Get some total including braspagfees
     *
     * @param mixed $total
     * @param mixed $rate
     * @return int
     */
    public function getTotalInclBraspagFees($total, $rate)
    {
        $rate = empty($rate) ? 0 : $rate;
        return $total * (1 + ($rate/100));
    }

    /**
     * Get braspag fees amount by rate
     *
     * @param mixed $total
     * @param mixed $rate
     * @return int|float
     */
    public function getBraspagFeesAmountByRate($total, $rate)
    {
        return ($rate / 100) * $total;
    }
}
