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
        $ccInstallments = $payment
            ->getData('additional_information')['cc_installments'];
        $method = $payment
            ->getData('method');

        if (!$quote->getId()
        ) {
            return $this;
        }

        if ($this->installmentsConfig->isActive()
            && $method == self::PAYMENT_METHOD
            && $ccInstallments > (int) $this->installmentsConfig->getInstallmentsMaxWithoutInterest()
            && (bool) $this->installmentsConfig->getInterestRate()
        ) {
            $order->setBaseGrandTotal($quote->getBaseGrandTotal())
            ->setGrandTotal($quote->getGrandTotal())
            ->setBaseSubtotal($quote->getBaseSubtotal())
            ->setSubtotal($quote->getSubtotal())
            ->setBraspagFees($quote->getBraspagFees());
        }
    }
}
