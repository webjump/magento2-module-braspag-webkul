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
use Braspag\Webkul\Model\BraspagFees\PaymentValidator;
use Braspag\Webkul\Model\BraspagFees\TaxCalculator;

class AddBraspagFeesToOrderObserver implements ObserverInterface
{

    /** @var PaymentValidator */
    private $paymentValidator;
    
    /** @var TaxCalculator */
    private $taxCalculator;

    /**
     * Construct method
     *
     * @param PaymentValidator $paymentValidator
     * @param TaxCalculator $taxCalculator
     */
    public function __construct(
        PaymentValidator $paymentValidator,
        TaxCalculator $taxCalculator
    ) {
        $this->paymentValidator = $paymentValidator;
        $this->taxCalculator = $taxCalculator;
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
        $ccInstallments = $this
            ->getCCInstalments($payment);
        
        if (!$quote->getId()
        ) {
            return $this;
        }

        if ($this->paymentValidator
            ->isValid($method, $ccInstallments)
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
            ) = $this->taxCalculator
                ->getItemPricesInclBraspagFees($item, $braspagFees);

            $item->setData('braspag_fees', $braspagFees)
                ->setData('base_price_incl_tax', $basePriceInclTax)
                ->setData('price_incl_tax', $priceIncTax)
                ->setData('base_row_total_incl_tax', $baseRowTotalInclTax)
                ->setData('row_total_incl_tax', $rowTotalInclTax);
        }
    }
}
