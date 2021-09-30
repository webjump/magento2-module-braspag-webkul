<?php
/**
 * @author      Webjump Core Team <dev@webjump.com.br>
 * @copyright   2021 Webjump (http://www.webjump.com.br)
 * @license     http://www.webjump.com.br  Copyright
 *
 * @link        http://www.webjump.com.br
 */

namespace Braspag\Webkul\Plugin\Observer;

use Webjump\Checkout\Model\ResourceModel\SellerDataRepository;

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
     * @var Webkul\Marketplace\Helper\Data
     */
    protected $marketplaceHelperData;

    /**
     * @var Webjump\BraspagPagador\Gateway\Transaction\PaymentSplit\Command\GetSubordinateCommand
     */
    protected $getSubordinateCommand;

    /**
     * @var Webjump\Checkout\Model\ResourceModel\SellerDataRepository
     */
    protected $sellerDataRepository;

    /**
     * @var Magento\Framework\Serialize\Serializer\Json
     */
    protected $serializerJson;

    /**
     * SalesOrderPlaceAfterObserverPlugin constructor.
     * @param \Webjump\BraspagPagador\Gateway\Transaction\PaymentSplit\Config\ConfigInterface $braspagPagadorConfig
     * @param \Braspag\Webkul\Gateway\Transaction\PaymentSplit\Config\ConfigInterface $braspagWebkulConfig
     * @param \Webkul\Marketplace\Helper\Data $marketplaceHelperData
     * @param \Webjump\BraspagPagador\Gateway\Transaction\PaymentSplit\Command\GetSubordinateCommand $getSubordinateCommand
     * @param SellerDataRepository $sellerDataRepository
     * @param \Magento\Framework\Serialize\Serializer\Json $serializerJson
     */
    public function __construct(
        \Webjump\BraspagPagador\Gateway\Transaction\PaymentSplit\Config\ConfigInterface $braspagPagadorConfig,
        \Braspag\Webkul\Gateway\Transaction\PaymentSplit\Config\ConfigInterface $braspagWebkulConfig,
        \Webkul\Marketplace\Helper\Data $marketplaceHelperData,
        \Webjump\BraspagPagador\Gateway\Transaction\PaymentSplit\Command\GetSubordinateCommand $getSubordinateCommand,
        SellerDataRepository $sellerDataRepository,
        \Magento\Framework\Serialize\Serializer\Json $serializerJson
    ) {
        $this->setBraspagPagadorConfig($braspagPagadorConfig);
        $this->setBraspagWebkulConfig($braspagWebkulConfig);
        $this->setMarketplaceHelperData($marketplaceHelperData);
        $this->setGetSubordinateCommand($getSubordinateCommand);
        $this->setSellerDataRepository($sellerDataRepository);
        $this->setSerializerJson($serializerJson);
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
     * @return mixed
     */
    public function getMarketplaceHelperData()
    {
        return $this->marketplaceHelperData;
    }

    /**
     * @param mixed $marketplaceHelperData
     */
    public function setMarketplaceHelperData($marketplaceHelperData)
    {
        $this->marketplaceHelperData = $marketplaceHelperData;
    }

    /**
     * @return mixed
     */
    public function getGetSubordinateCommand()
    {
        return $this->getSubordinateCommand;
    }

    /**
     * @param mixed $getSubordinateCommand
     */
    public function setGetSubordinateCommand($getSubordinateCommand)
    {
        $this->getSubordinateCommand = $getSubordinateCommand;
    }

    /**
     * @return SellerDataRepository
     */
    public function getSellerDataRepository(): SellerDataRepository
    {
        return $this->sellerDataRepository;
    }

    /**
     * @param SellerDataRepository $sellerDataRepository
     */
    public function setSellerDataRepository(SellerDataRepository $sellerDataRepository): void
    {
        $this->sellerDataRepository = $sellerDataRepository;
    }

    /**
     * @return \Magento\Framework\Serialize\Serializer\Json
     */
    public function getSerializerJson(): \Magento\Framework\Serialize\Serializer\Json
    {
        return $this->serializerJson;
    }

    /**
     * @param \Magento\Framework\Serialize\Serializer\Json $serializerJson
     */
    public function setSerializerJson(\Magento\Framework\Serialize\Serializer\Json $serializerJson): void
    {
        $this->serializerJson = $serializerJson;
    }

    /**
     * @param \Webkul\Marketplace\Observer\SalesOrderPlaceAfterObserver $subject
     * @param $result
     * @param $sellerId
     * @param $totalamount
     * @param $item
     * @param $advanceCommissionRule
     * @return float|int
     */
    public function afterGetCommission(\Webkul\Marketplace\Observer\SalesOrderPlaceAfterObserver $subject,
                                       $result,
                                       $sellerId,
                                       $totalamount,
                                       $item,
                                       $advanceCommissionRule
    ) {
        $paymentMethodsToApplyBraspagCommission = $this->getBraspagPagadorConfig()
            ->getPaymentSplitMarketPlaceVendorPaymentTypesToApplyBraspagCommission();

        $paymentMethodsToApplyWebkulCommission = $this->getBraspagWebkulConfig()
            ->getPaymentSplitMarketPlaceVendorPaymentTypesToApplyWebkulCommission();

        $orderPayment = $item->getOrder()->getPayment();

        $order = $item->getOrder();

        $paymentMethodsToApplyBraspagCommission = explode(",", $paymentMethodsToApplyBraspagCommission);

        if (!empty($paymentMethodsToApplyBraspagCommission)
            && in_array($orderPayment->getMethod(), $paymentMethodsToApplyBraspagCommission)
        ) {
            $subordinate = $this->getMarketplaceHelperData()->getSellerCollectionObj($sellerId)->getFirstItem();

            if (empty($subordinate->getId())) {
                return $result;
            }

            $subordinateDataFromBraspag = $this->getGetSubordinateCommand()->execute([
                'subordinate' => $sellerId,
                'merchantId' => $subordinate->getBraspagSubordinateMerchantid()
            ]);

            $commissionPercent = $this->getSellerCommissionPercentageFromBraspag(
                $orderPayment,
                $sellerId,
                $subordinateDataFromBraspag
            );

            $commissionFeeAmount = $this->getSellerCommissionFeeAmountFromBraspag(
                $orderPayment,
                $sellerId,
                $subordinateDataFromBraspag
            );

            $sellerQuoteData = $this->getSellerDataRepository()
                ->getByQuoteIdAndSellerId($order->getQuoteId(), $sellerId)
                ->getFirstItem();


            $sellerQuoteItemsJson = $sellerQuoteData->getQuoteItems();
            $sellerQuoteItems = $this->getSerializerJson()->unserialize($sellerQuoteItemsJson);
            $totalSellerQuoteItems = count($sellerQuoteItems);

            $commissionFeeAmountItem = $commissionFeeAmount / (empty($totalSellerQuoteItems) ? 1 : $totalSellerQuoteItems);

            $shippingAmount = 0;
            if (!empty($sellerQuoteData->getShippingTotals())) {
                $shippingAmount = $sellerQuoteData->getShippingTotals() / (empty($totalSellerQuoteItems) ? 1 : $totalSellerQuoteItems);
            }

            $totalAmount = $totalamount + floatval($shippingAmount);

            return (($totalAmount / 100) * $commissionPercent) + $commissionFeeAmountItem;
        }

        $paymentMethodsToApplyWebkulCommission = explode(",", $paymentMethodsToApplyWebkulCommission);

        if (!empty($paymentMethodsToApplyWebkulCommission)
            && in_array($orderPayment->getMethod(), $paymentMethodsToApplyWebkulCommission)
        ) {
            return $result;
        }

        return 0;
    }

    /**
     * @param $magentoPayment
     * @param $sellerId
     * @param $subordinateDataFromBraspag
     * @return float|null
     */
    protected function getSellerCommissionPercentageFromBraspag($magentoPayment, $sellerId, $subordinateDataFromBraspag)
    {
        $agreementMerchantDiscountRates = $subordinateDataFromBraspag->getAgreementMerchantDiscountRates();

        $magentoOrderProduct = $magentoOrderBrand = null;

        switch ($magentoPayment->getMethod()) {
            case  'braspag_pagador_creditcard':
                $magentoOrderProduct = "CreditCard";
                break;
            default:
                return null;
                break;
        }

        if (preg_match("/Visa/is",  $magentoPayment->getCcType())) {
            $magentoOrderBrand = "Visa";
        }

        if (preg_match("/Master/is",  $magentoPayment->getCcType())) {
            $magentoOrderBrand = "Master";
        }

        if (empty($magentoOrderBrand)) {
            return null;
        }

        $magentoOrderInstallments = $magentoPayment->getAdditionalInformation('cc_installments');
        $magentoOrderDiscountPercent = 0;

        foreach($agreementMerchantDiscountRates as $agreementMerchantDiscountRate) {
            if ($agreementMerchantDiscountRate['PaymentArrangement']['Product'] == $magentoOrderProduct
                && $agreementMerchantDiscountRate['PaymentArrangement']['Brand'] == $magentoOrderBrand
                && $agreementMerchantDiscountRate['InitialInstallmentNumber'] >= $magentoOrderInstallments
                && $agreementMerchantDiscountRate['FinalInstallmentNumber'] <= $magentoOrderInstallments
            ) {
                $magentoOrderDiscountPercent = $agreementMerchantDiscountRate['Percent'];
            }
        }

        return floatval($magentoOrderDiscountPercent);
    }

    /**
     * @param $orderPayment
     * @param $sellerId
     * @param $subordinateDataFromBraspag
     * @return float|int
     */
    protected function getSellerCommissionFeeAmountFromBraspag($orderPayment, $sellerId, $subordinateDataFromBraspag)
    {
        $total = 0;
        $total = $total + floatval($subordinateDataFromBraspag->getAgreementFee());
        $total = $total + floatval($subordinateDataFromBraspag->getAgreementAntiFraudFee());
        $total = $total + floatval($subordinateDataFromBraspag->getAgreementAntiFraudFeeWithReview());

        return $total/100;
    }
}
