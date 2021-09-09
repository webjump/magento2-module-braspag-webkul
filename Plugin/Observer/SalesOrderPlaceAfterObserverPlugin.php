<?php
/**
 * @author      Webjump Core Team <dev@webjump.com.br>
 * @copyright   2021 Webjump (http://www.webjump.com.br)
 * @license     http://www.webjump.com.br  Copyright
 *
 * @link        http://www.webjump.com.br
 */

namespace Braspag\Webkul\Plugin\Observer;

/**
 * Class SalesOrderPlaceAfterObserverPlugin
 * @package Braspag\Webkul\Plugin\Observer
 */
class SalesOrderPlaceAfterObserverPlugin
{
    /**
     * @var Webjump\BraspagPagador\Gateway\Transaction\PaymentSplit\Config\Config
     */
    protected $braspagPagadorConfig;

    /**
     * @var Braspag\Webkul\Gateway\Transaction\PaymentSplit\Config\Config
     */
    protected $braspagWebkulConfig;

    /**
     * SalesOrderPlaceAfterObserverPlugin constructor.
     * @param \Webjump\BraspagPagador\Gateway\Transaction\PaymentSplit\Config\ConfigInterface $braspagPagadorConfig
     * @param \Braspag\Webkul\Gateway\Transaction\PaymentSplit\Config\ConfigInterface $braspagWebkulConfig
     */
    public function __construct(
        \Webjump\BraspagPagador\Gateway\Transaction\PaymentSplit\Config\ConfigInterface $braspagPagadorConfig,
        \Braspag\Webkul\Gateway\Transaction\PaymentSplit\Config\ConfigInterface $braspagWebkulConfig
    ) {
        $this->setBraspagPagadorConfig($braspagPagadorConfig);
        $this->setBraspagWebkulConfig($braspagWebkulConfig);
    }

    /**
     * @return Webjump\BraspagPagador\Gateway\Transaction\PaymentSplit\Config\Config
     */
    public function getBraspagPagadorConfig()
    {
        return $this->braspagPagadorConfig;
    }

    /**
     * @param Webjump\BraspagPagador\Gateway\Transaction\PaymentSplit\Config\Config $braspagPagadorConfig
     */
    public function setBraspagPagadorConfig($braspagPagadorConfig)
    {
        $this->braspagPagadorConfig = $braspagPagadorConfig;
    }

    /**
     * @return Braspag\Webkul\Gateway\Transaction\PaymentSplit\Config\Config
     */
    public function getBraspagWebkulConfig()
    {
        return $this->braspagWebkulConfig;
    }

    /**
     * @param Braspag\Webkul\Gateway\Transaction\PaymentSplit\Config\Config $braspagWebkulConfig
     */
    public function setBraspagWebkulConfig($braspagWebkulConfig)
    {
        $this->braspagWebkulConfig = $braspagWebkulConfig;
    }

    /**
     * @param \Webkul\Marketplace\Observer\SalesOrderPlaceAfterObserver $subject
     * @param $result
     * @param $sellerId
     * @param $totalamount
     * @param $item
     * @param $advanceCommissionRule
     * @return int
     */
    public function afterGetCommission(\Webkul\Marketplace\Observer\SalesOrderPlaceAfterObserver $subject,
                                       $result,
                                       $sellerId,
                                       $totalamount,
                                       $item,
                                       $advanceCommissionRule
    ) {
        $paymentMethodsToApplyBraspagCommission = $this->getBraspagPagadorConfig()->getPaymentSplitMarketPlaceVendorPaymentTypesToApplyBraspagCommission();
        $paymentMethodsToApplyWebkulCommission = $this->getBraspagWebkulConfig()->getPaymentSplitMarketPlaceVendorPaymentTypesToApplyWebkulCommission();

        $orderPayment = $item->getOrder()->getPayment();

        $paymentMethodsToApplyBraspagCommission = explode(",", $paymentMethodsToApplyBraspagCommission);

        if (!empty($paymentMethodsToApplyBraspagCommission)
            && in_array($orderPayment->getMethod(), $paymentMethodsToApplyBraspagCommission)
        ) {
            //https://braspag.github.io//manual/manual-api-de-cadastro-de-sellers#consulta-de-subordinados
            return 0;
        }

        $paymentMethodsToApplyWebkulCommission = explode(",", $paymentMethodsToApplyWebkulCommission);

        if (!empty($paymentMethodsToApplyWebkulCommission)
            && in_array($orderPayment->getMethod(), $paymentMethodsToApplyWebkulCommission)
        ) {
            $result;
        }

        return 0;
    }
}
