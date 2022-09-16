<?php
/**
 * @author      Webjump Core Team <dev@webjump.com.br>
 * @copyright   2022 Webjump (http://www.webjump.com.br)
 * @license     http://www.webjump.com.br  Copyright
 *
 * @link        http://www.webjump.com.br
 */

declare(strict_types=1);

namespace Braspag\Webkul\Model\BraspagFees;

use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Sales\Model\Order\Item as OrderItem;
use Webjump\MpAssignProduct\Model\MpAssignProduct;
use Webjump\SellerOrderApproval\Model\SellerOrderManager;

class ItemRewardPoints
{

    /**
     * @var MpAssignProduct
     */
    private $mpAssignProductHelper;

    /**
     * @var SellerOrderManager
     */
    private $sellerOrderManager;

    /**
     * Construct method
     *
     * @param SellerOrderManager $sellerOrderManager
     * @param MpAssignProduct $mpAssignProductHelper
     */
    public function __construct(
        SellerOrderManager $sellerOrderManager,
        MpAssignProduct $mpAssignProductHelper
    ) {
        $this->mpAssignProductHelper = $mpAssignProductHelper;
        $this->sellerOrderManager = $sellerOrderManager;
    }

    /**
     * Get reward amount per Item
     *
     * @param QuoteItem|OrderItem $item
     * @return int|float
     */
    public function getAmountPerItem($item, $entity = null)
    {
        $entity = $entity ?? $item->getOrder() ?? $item->getQuote();
        $rewardRate = $this->getRewardRate($item, $entity);
        $sellerId = $this->mpAssignProductHelper
            ->getSellerIdByAssignProductId((int) $item->getProductId());
        $shippingItemAmount = $this->sellerOrderManager
            ->getSellerOrderShippingDivisionAmountByItem($entity, $sellerId);

        return ($item->getRowTotal() + $shippingItemAmount - $item->getDiscountAmount()) * ($rewardRate);
    }

    /**
     * Get reward rate
     *
     * @param mixed $item
     * @param mixed $order
     * @return float|int
     */
    public function getRewardRate($item = null, $order = null)
    {
        $entity = $order ?? $item->getOrder() ?? $item->getQuote();
        $orderRewardCurrencyAmount = $entity->getRewardCurrencyAmount() ?? 0;
        return ($orderRewardCurrencyAmount) / (($entity->getGrandTotal() + $orderRewardCurrencyAmount) - $entity->getBraspagFeesAmount());
    }
}