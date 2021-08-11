<?php

/**
 * @author      Webjump Core Team <dev@webjump.com.br>
 * @copyright   2020 Webjump (http://www.webjump.com.br)
 * @license     http://www.webjump.com.br  Copyright
 *
 * @link        http://www.webjump.com.br
 */

namespace Braspag\Webkul\Plugin;

use Magento\Framework\Session\SessionManager;

/**
 * Class SplitDataProvider
 * @package Braspag\Webkul\Plugin
 */
class SplitDataProvider
{
    protected $paymentSplitConfig;
    protected $webkulHelper;
    protected $objectFactory;
    protected $marketplaceMerchantId;
    protected $marketplaceDefaultMdr = 0;
    protected $marketplaceDefaultFee = 0;
    protected $marketplaceSalesParticipation;
    protected $marketplaceSalesParticipationType;
    protected $marketplaceSalesParticipationPercent = 0;
    protected $marketplaceSalesParticipationFixedValue = 0;
    protected $marketplaceParticipationFinalValue = 0;
    protected $subordinates = [];
    protected $customerFactory;
    protected $resource;

    /**
     * SplitDataProvider constructor.
     * @param \Webjump\BraspagPagador\Gateway\Transaction\PaymentSplit\Config\Config $paymentSplitConfig
     * @param \Webkul\MpAssignProduct\Helper\Data $webkulHelper
     * @param \Magento\Framework\DataObjectFactory $objectFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param SessionManager $session
     */
    public function __construct(
        \Webjump\BraspagPagador\Gateway\Transaction\PaymentSplit\Config\Config $paymentSplitConfig,
        \Webkul\MpAssignProduct\Helper\Data $webkulHelper,
        \Magento\Framework\DataObjectFactory $objectFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\App\ResourceConnection $resource,
        SessionManager $session
    ) {
        $this->paymentSplitConfig = $paymentSplitConfig;
        $this->webkulHelper = $webkulHelper;
        $this->objectFactory = $objectFactory;
        $this->customerFactory = $customerFactory;
        $this->resource = $resource;
        $this->session = $session;

        $this->marketplaceMerchantId = $this->paymentSplitConfig
            ->getPaymentSplitMarketPlaceCredendialsMerchantId();

        $this->marketplaceSalesParticipation = (bool) $this->paymentSplitConfig
            ->getPaymentSplitMarketPlaceGeneralSalesParticipation();

        $this->marketplaceSalesParticipationType = $this->paymentSplitConfig
            ->getPaymentSplitMarketPlaceGeneralSalesParticipationType();

        $this->marketplaceSalesParticipationPercent = floatval($this->paymentSplitConfig
            ->getPaymentSplitMarketPlaceGeneralSalesParticipationPercent());

        $this->marketplaceSalesParticipationFixedValue = floatval($this->paymentSplitConfig
            ->getPaymentSplitMarketPlaceGeneralSalesParticipationFixedValue());
    }

    /**
     * @param \Webjump\BraspagPagador\Model\SplitDataProvider $subject
     * @param $result
     * @param $storeMerchantId
     * @param int $storeDefaultMdr
     * @param int $storeDefaultFee
     * @return mixed
     */
    public function afterGetData(
        \Webjump\BraspagPagador\Model\SplitDataProvider $subject,
        $result,
        $storeMerchantId,
        $storeDefaultMdr = 0,
        $storeDefaultFee = 0
    ) {

        if ($this->paymentSplitConfig->getPaymentSplitMarketPlaceVendor()
            !== \Braspag\Webkul\Plugin\PaymentSplitVendorSourceList::PAYMENT_SPLIT_MARKETPLACE_VENDOR_CODE_WEBKUL
        ) {
            return $result;
        }

        $this->marketplaceDefaultMdr = floatval($storeDefaultMdr);
        $this->marketplaceDefaultFee = floatval($storeDefaultFee);

        $this->subordinates = $items = [];

        $itemType = 'quote';

        if (!empty($subject->getQuote())) {
            $items = $subject->getQuote()->getAllVisibleItems();
            $itemType = 'quote';
        }

        if (empty($items) && !empty($subject->getOrder())) {
            $items = $subject->getOrder()->getAllVisibleItems();
            $itemType = 'order';
        }

        if (empty($items)) {
            $items = $subject->getSession()->getQuote()->getAllVisibleItems();
            $itemType = 'quote';
        }

        foreach ($items as $item) {

            $product = $item->getProduct();

            $sellerId = $this->webkulHelper->getSellerIdByProductId($product->getId());

            $sellerInfo = $this->getSellerInfo($sellerId);

            $braspagSubordinateMdr = floatval($this->getProductAttributeByCode($product,
                'braspag_subordinate_mdr',
                $subject->getStoreManager()->getStore()->getId()
            ));

            $braspagSubordinateFee = floatval($this->getProductAttributeByCode($product,
                'braspag_subordinate_fee',
                $subject->getStoreManager()->getStore()->getId()
            ));

            $braspagSubordinateMerchantId = $sellerInfo->getData('braspag_subordinate_merchantid');

            if (empty($braspagSubordinateMerchantId)) {
                $braspagSubordinateMerchantId = $this->marketplaceMerchantId;
            }

            if (!isset($this->subordinates[$braspagSubordinateMerchantId])) {
                $this->subordinates[$braspagSubordinateMerchantId] = [];
                $this->subordinates[$braspagSubordinateMerchantId]['amount'] = 0;

                if ($braspagSubordinateMerchantId !== $this->marketplaceMerchantId) {
                    $this->subordinates[$braspagSubordinateMerchantId]['fares'] = [
                        "mdr" => floatval($this->marketplaceDefaultMdr),
                        "fee" => floatval($this->marketplaceDefaultFee)
                    ];
                }

                $this->subordinates[$braspagSubordinateMerchantId]['skus'] = [];
            }

            $braspagSubordinateMdr = $this->getSubordinateItemMdr(
                $braspagSubordinateMdr,
                $subject,
                $sellerInfo,
                $product
            );

            $braspagSubordinateFee = $this->getSubordinateItemFee(
                $braspagSubordinateFee,
                $subject,
                $sellerInfo,
                $product
            );

            if (isset($this->subordinates[$braspagSubordinateMerchantId]['fares'])) {
                $this->subordinates[$braspagSubordinateMerchantId]['fares']['mdr'] = $braspagSubordinateMdr;
                $this->subordinates[$braspagSubordinateMerchantId]['fares']['fee'] = $braspagSubordinateFee;
            }

            $itemQty = $item->getQtyOrdered()-$item->getQtyCanceled()-$item->getQtyShipped();

            $itemPrice = floatval(($item->getPriceInclTax()*$itemQty) - $item->getDiscountAmount());

            $this->subordinates[$braspagSubordinateMerchantId]['amount'] += ($itemPrice * 100);

            $itemsObject = $this->objectFactory->create();
            $items = [
                "item_id" => $item->getId(),
                "item_type" => $itemType,
                "sku" => $product->getSku()
            ];

            $itemsObject->addData($items);

            $this->subordinates[$braspagSubordinateMerchantId]['items'][] =  $itemsObject;
        }

        if ($this->marketplaceSalesParticipation) {
            $this->removeMarketplaceParticipationValuesFromSubordinates();
            $this->addMarketplaceParticipationValues();
        }

        $result = $subject->getSplitAdapter()->adapt($this->subordinates, $this->marketplaceMerchantId);

        return $result;
    }

    /**
     * @param $vendorProductMdr
     * @param $subject
     * @param $sellerInfo
     * @param $product
     * @return int|null
     */
    protected function getSubordinateItemMdr($vendorProductMdr, $subject, $sellerInfo, $product)
    {
        $braspagSubordinateMdr = null;

        if (!empty($vendorProductMdr)) {
            $braspagSubordinateMdr = $vendorProductMdr;
        }

        if (empty($braspagSubordinateMdr)) {
            $braspagSubordinateMdr = $sellerInfo->getData('braspag_subordinate_mdr');
        }

        if (empty($braspagSubordinateMdr)) {
            $braspagSubordinateMdr = $product->getResource()
                ->getAttributeRawValue(
                    $product->getId(),
                    'braspag_subordinate_mdr',
                    $subject->getStoreManager()->getStore()->getId()
                );
        }

        if (empty($braspagSubordinateMdr)) {
            $braspagSubordinateMdr = $this->marketplaceDefaultMdr;
        }

        return $braspagSubordinateMdr;
    }

    /**
     * @param $vendorProductFee
     * @param $subject
     * @param $vendor
     * @param $product
     * @return int|null
     */
    protected function getSubordinateItemFee($vendorProductFee, $subject, $sellerInfo, $product)
    {
        $braspagSubordinateFee = null;

        if (!empty($vendorProductFee)) {
            $braspagSubordinateFee = $vendorProductFee;
        }

        if (empty($braspagSubordinateFee)) {
            $braspagSubordinateFee = $sellerInfo->getData('braspag_subordinate_fee');
        }

        if (empty($braspagSubordinateFee)) {
            $braspagSubordinateFee = $product->getResource()
                ->getAttributeRawValue(
                    $product->getId(),
                    'braspag_subordinate_fee',
                    $subject->getStoreManager()->getStore()->getId()
                );
        }

        if (empty($braspagSubordinateFee)) {
            $braspagSubordinateFee = $this->marketplaceDefaultFee;
        }

        return $braspagSubordinateFee;
    }

    /**
     * @return $this
     */
    protected function removeMarketplaceParticipationValuesFromSubordinates()
    {
        foreach ($this->subordinates as $subordinateId => $subordinateData) {

            $subordinateAmountOriginal = floatval($subordinateData['amount']) / 100;

            if ($this->marketplaceSalesParticipation && $subordinateId !== $this->marketplaceMerchantId) {

                $subordinateAmount = $subordinateAmountOriginal;

                if ($this->marketplaceSalesParticipationType === '1') {
                    $subordinateAmount = (floatval($this->marketplaceSalesParticipationPercent) / 100) * $subordinateAmount;
                }

                if ($this->marketplaceSalesParticipationType === '2'
                    && $subordinateAmount >= $this->marketplaceSalesParticipationFixedValue
                ) {
                    $subordinateAmount = floatval($subordinateAmount) - floatval($this->marketplaceSalesParticipationFixedValue);
                }

                $this->subordinates[$subordinateId]['amount'] = $subordinateAmount * 100;

                $this->marketplaceParticipationFinalValue += $subordinateAmountOriginal-$subordinateAmount;
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function addMarketplaceParticipationValues()
    {
        if (!isset($this->subordinates[$this->marketplaceMerchantId])) {
            $this->subordinates[$this->marketplaceMerchantId] = [];
            $this->subordinates[$this->marketplaceMerchantId]['amount'] = 0;
        }

        $this->subordinates[$this->marketplaceMerchantId]['amount'] += $this->marketplaceParticipationFinalValue * 100;

        return $this;
    }

    /**
     * @param $customerId
     * @return mixed
     */
    protected function getSellerInfo($customerId)
    {
        $result = [];
        $collection = $this->customerFactory->create()->getCollection();
        $joinTable = $this->resource->getTableName('marketplace_userdata');
        $sql = 'mpud.seller_id = e.entity_id';
        $fields = [];
        $fields[] = 'shop_url';
        $fields[] = 'shop_title';
        $fields[] = 'is_seller';
        $fields[] = 'braspag_subordinate_merchantid';
        $fields[] = 'braspag_subordinate_mdr';
        $fields[] = 'braspag_subordinate_fee';
        $collection->getSelect()->joinLeft($joinTable.' as mpud', $sql, $fields);
        $collection->addFieldToFilter("entity_id", $customerId);
        $collection->getSelect()->where("mpud.store_id = 0");
        return $collection->getFirstItem();
    }

    /**
     * @param $product
     * @param $code
     * @param $storeId
     * @return mixed
     */
    private function getProductAttributeByCode($product, $code, $storeId)
    {
        return $product->getResource()->getAttributeRawValue(
                $product->getId(),
                $code,
                $storeId
            );
    }
}
