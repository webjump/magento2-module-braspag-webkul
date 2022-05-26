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
use Braspag\Webkul\Model\Observer\BraspagFeesToQuote as BraspagFeesHelper;
use Braspag\Webkul\Model\BraspagFees\PaymentValidator;

class AddBraspagFeesToQuoteObserver implements ObserverInterface
{

    /** @var BraspagFeesHelper */
    private $helper;

    /** @var CartRepositoryInterface */
    private $quoteRepository;

    /** @var PaymentValidator */
    private $paymentValidator;

    /**
     * Construct method
     *
     * @param BraspagFeesHelper $helper
     * @param PaymentValidator $paymentValidator
     * @param CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        BraspagFeesHelper $helper,
        PaymentValidator $paymentValidator,
        CartRepositoryInterface $quoteRepository
    ) {
        $this->helper = $helper;
        $this->paymentValidator = $paymentValidator;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Add/Update attributes in quote
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(
        Observer $observer
    ) {
        list(
            $quote,
            $method,
            $ccInstallments
        ) = $this->helper->getPaymentInformation($observer);

        if ($this->paymentValidator
            ->isValid($method, $ccInstallments)
        ) {
            list(
                $newBaseGrandTotal,
                $newGrandTotal,
                $braspagFees,
                $braspagFeesAmount
            ) = $this->helper
                ->getNewQuoteInformation($quote, $ccInstallments);

            $quote
                ->setBaseGrandTotal($newBaseGrandTotal)
                ->setGrandTotal($newGrandTotal)
                ->setBraspagFees($braspagFees)
                ->setBraspagFeesAmount($braspagFeesAmount);

            $quote->collectTotals();
            $this->quoteRepository
                ->save($quote);
        }
    }
}
