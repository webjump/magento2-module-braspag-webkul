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

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item;

class ItemRewardPoints
{

    /**
     * Get reward amount proportional to the item amount
     *
     * @param Order $order
     * @param Item $item
     * @return float|int
     */
    public function getAmountPerItem($order, $item)
    {
        $orderRewardCurrencyAmount = $order->getRewardCurrencyAmount() ?? 0;
        $rewardRate = ($orderRewardCurrencyAmount) / $order->getBaseSubtotal();
        return $item->getRowTotal() * ($rewardRate);
    }
}