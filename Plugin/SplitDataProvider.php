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
    protected $ordersRepository;
    protected $webkulPayment;
    protected $webkulHelperData;
    protected $serializerJson;

    /**
     * SplitDataProvider constructor.
     * @param \Webjump\BraspagPagador\Gateway\Transaction\PaymentSplit\Config\Config $paymentSplitConfig
     * @param \Magento\Framework\DataObjectFactory $objectFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param SessionManager $session
     * @param \Webkul\Marketplace\Model\OrdersRepository $ordersRepository
     * @param \Webkul\Marketplace\Helper\Payment $webkulPayment
     * @param \Webkul\Marketplace\Helper\Data $webkulHelperData
     * @param \Magento\Framework\Serialize\Serializer\Json $serializerJson
     */
    public function __construct(
        \Webjump\BraspagPagador\Gateway\Transaction\PaymentSplit\Config\Config $paymentSplitConfig,
        \Magento\Framework\DataObjectFactory $objectFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\App\ResourceConnection $resource,
        SessionManager $session,
        \Webkul\Marketplace\Model\OrdersRepository $ordersRepository,
        \Webkul\Marketplace\Helper\Payment $webkulPayment,
        \Webkul\Marketplace\Helper\Data $webkulHelperData,
        \Magento\Framework\Serialize\Serializer\Json $serializerJson
    ) {
        $this->paymentSplitConfig = $paymentSplitConfig;
        $this->objectFactory = $objectFactory;
        $this->customerFactory = $customerFactory;
        $this->resource = $resource;
        $this->session = $session;
        $this->ordersRepository = $ordersRepository;
        $this->webkulPayment = $webkulPayment;
        $this->webkulHelperData = $webkulHelperData;
        $this->serializerJson = $serializerJson;

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
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
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

        $subjectData = $this->getSubjectData($subject);
        $entityType = $subjectData['entityType'];
        $entityItems = $subjectData['entityItems'];
        $entityData = $subjectData['entityData'];

        foreach ($entityItems as $item) {

            $product = $item->getProduct();
            
            $sellerId = $this->getItemSellerId($item, $entityType, $entityData);

            $sellerInfo = $this->getSellerInfo($sellerId);

            $subordinateMerchantId = $sellerInfo->getData('braspag_subordinate_merchantid');

            if (empty($subordinateMerchantId)) {
                $subordinateMerchantId = $this->marketplaceMerchantId;
            }

            $this->populeSubordinateMdrFeeData($subject, $product, $sellerInfo, $subordinateMerchantId);

            $itemQty = $this->getItemQty($item, $entityType);

            $itemPrice = floatval(($item->getPriceInclTax()*$itemQty) - $item->getDiscountAmount());

            $this->subordinates[$subordinateMerchantId]['amount'] += ($itemPrice * 100);

            $itemsObject = $this->objectFactory->create();
            $items = [
                "item_id" => $item->getId(),
                "item_type" => $entityType,
                "sku" => $product->getSku()
            ];

            $itemsObject->addData($items);

            $this->subordinates[$subordinateMerchantId]['items'][] =  $itemsObject;
        }

        if ($this->marketplaceSalesParticipation) {
            $this->removeMarketplaceParticipationValuesFromSubordinates();
            $this->addMarketplaceParticipationValues();
        }

        $result = $subject->getSplitAdapter()->adapt($this->subordinates, $this->marketplaceMerchantId);

        return $result;
    }

    /**
     * @param $subject
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getSubjectData($subject)
    {
        $entityType = 'quote';

        $entityData = null;

        if (!empty($subject->getQuote())) {
            $items = $subject->getQuote()->getAllVisibleItems();
            $entityType = 'quote';
            $entityData = $this->webkulPayment->prepareSplitPaymentData($subject->getQuote());
        }

        if (empty($items) && !empty($subject->getOrder())) {
            $items = $subject->getOrder()->getAllVisibleItems();
            $entityType = 'order';
            $entityData = $this->ordersRepository->getByOrderId($subject->getOrder()->getId());
        }

        if (empty($items)) {
            $items = $subject->getSession()->getQuote()->getAllVisibleItems();
            $entityType = 'quote';
            $entityData = $this->webkulPayment->prepareSplitPaymentData($subject->getSession()->getQuote());
        }

        return [
            'entityType' => $entityType,
            'entityItems' => !empty($items) ? $items : [],
            'entityData' => $entityData,
        ];
    }

    /**
     * @param $item
     * @param $entityType
     * @param $entityData
     * @return int|string
     */
    private function getItemSellerId($item, $entityType, $entityData)
    {
        $sellerId = '';
        $product = $item->getProduct();

        if ($entityType === 'quote' && !empty($entityData)) {

            $itemOptionsData = $item->getOptionByCode('info_buyRequest');
            $itemOptionsDataUnserialized = $this->serializerJson->unserialize($itemOptionsData->getValue());

            if (isset($itemOptionsDataUnserialized['product'])
                && $itemOptionsDataUnserialized['product'] == $product->getId()
            ) {
                if (isset($itemOptionsDataUnserialized['mpassignproduct_id'])
                    && $itemOptionsDataUnserialized['mpassignproduct_id'] != '0') {
                    $sellerId = $this->webkulHelperData->getSellerId(
                        $itemOptionsDataUnserialized['mpassignproduct_id']
                        , $product->getId()
                    );

                } else {
                    $sellerId = $this->webkulHelperData->getSellerId(
                        ''
                        , $product->getId());
                }
            }
        }

        if ($entityType === 'order' && !empty($entityData)) {

            foreach ($entityData as $webkulOrderItemsData) {
                $webkulOrderItemProductIds = explode(",", $webkulOrderItemsData->getData('product_ids'));
                if (in_array($product->getId(), $webkulOrderItemProductIds)) {
                    $sellerId = $webkulOrderItemsData->getData('seller_id');
                    break;
                }
            }
        }

        return $sellerId;
    }

    /**
     * @param $item
     * @param $entityType
     * @return int
     */
    private function getItemQty($item, $entityType)
    {
        $itemQty = 1;

        if ($entityType === 'quote') {
            $itemQty = $item->getQty();
        }

        if ($entityType === 'order') {
            $itemQty = $item->getQtyOrdered()-$item->getQtyCanceled()-$item->getQtyShipped();
        }

        return $itemQty;
    }

    /**
     * @param $subject
     * @param $product
     * @param $sellerInfo
     * @param $subordinateMerchantId
     * @return $this
     */
    private function populeSubordinateMdrFeeData($subject, $product, $sellerInfo, $subordinateMerchantId)
    {
        $subordinateMdr = floatval($this->getProductAttributeByCode($product,
            'braspag_subordinate_mdr', $subject->getStoreManager()->getStore()->getId()
        ));

        $subordinateFee = floatval($this->getProductAttributeByCode($product,
            'braspag_subordinate_fee', $subject->getStoreManager()->getStore()->getId()
        ));

        if (!isset($this->subordinates[$subordinateMerchantId])) {
            $this->subordinates[$subordinateMerchantId] = [];
            $this->subordinates[$subordinateMerchantId]['amount'] = 0;

            if ($subordinateMerchantId !== $this->marketplaceMerchantId) {
                $this->subordinates[$subordinateMerchantId]['fares'] = [
                    "mdr" => floatval($this->marketplaceDefaultMdr),
                    "fee" => floatval($this->marketplaceDefaultFee)
                ];
            }
            $this->subordinates[$subordinateMerchantId]['skus'] = [];
        }

        $subordinateMdr = $this->getSubordinateItemMdr($subordinateMdr, $subject, $sellerInfo, $product);
        $subordinateFee = $this->getSubordinateItemFee($subordinateFee, $subject, $sellerInfo, $product);

        if (isset($this->subordinates[$subordinateMerchantId]['fares'])) {
            $this->subordinates[$subordinateMerchantId]['fares']['mdr'] = $subordinateMdr;
            $this->subordinates[$subordinateMerchantId]['fares']['fee'] = $subordinateFee;
        }

        return $this;
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
        $subordinateMdr = null;

        if (!empty($vendorProductMdr)) {
            $subordinateMdr = $vendorProductMdr;
        }

        if (empty($subordinateMdr)) {
            $subordinateMdr = $sellerInfo->getData('braspag_subordinate_mdr');
        }

        if (empty($subordinateMdr)) {
            $subordinateMdr = $product->getResource()
                ->getAttributeRawValue(
                    $product->getId(),
                    'braspag_subordinate_mdr',
                    $subject->getStoreManager()->getStore()->getId()
                );
        }

        if (empty($subordinateMdr)) {
            $subordinateMdr = $this->marketplaceDefaultMdr;
        }

        return $subordinateMdr;
    }

    /**
     * @param $vendorProductFee
     * @param $subject
     * @param $sellerInfo
     * @param $product
     * @return int|null
     */
    protected function getSubordinateItemFee($vendorProductFee, $subject, $sellerInfo, $product)
    {
        $subordinateFee = null;

        if (!empty($vendorProductFee)) {
            $subordinateFee = $vendorProductFee;
        }

        if (empty($subordinateFee)) {
            $subordinateFee = $sellerInfo->getData('braspag_subordinate_fee');
        }

        if (empty($subordinateFee)) {
            $subordinateFee = $product->getResource()
                ->getAttributeRawValue(
                    $product->getId(),
                    'braspag_subordinate_fee',
                    $subject->getStoreManager()->getStore()->getId()
                );
        }

        if (empty($subordinateFee)) {
            $subordinateFee = $this->marketplaceDefaultFee;
        }

        return $subordinateFee;
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
     * @return \Magento\Framework\DataObject
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
