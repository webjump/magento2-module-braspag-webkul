<?php
namespace Braspag\Webkul\Plugin\Marketplace\Model;

use Webkul\Marketplace\Model\ResourceModel\Saleslist\Collection as SalesListCollection;

class BraspagFeesToSalesListCollection
{

    /**
     * Added Braspag fees information to query
     *
     * @param SalesListCollection $subject
     * @param mixed $collection
     * @return void
     */
    public function afterGetSellerOrderTotalsQuery(
        SalesListCollection $subject,
        $collection
    ) {
        $collection = $collection->getSelect()
            ->reset(\Zend_Db_Select::COLUMNS)
            ->columns(
                [
                    'main_table.currency_rate AS currency_rate',
                    'main_table.order_id AS order_id',
                    'SUM(main_table.total_amount) AS total_amount',
                    'SUM(main_table.actual_seller_amount) AS total',
                    'SUM(main_table.actual_seller_amount) AS actual_seller_amount',
                    'SUM(main_table.total_commission) AS total_commission',
                    'SUM(main_table.applied_coupon_amount) AS applied_coupon_amount',
                    'SUM(main_table.total_tax) AS total_tax',
                    'SUM(main_table.braspag_fees) AS braspag_fees',
                    'SUM(main_table.braspag_fees_amount) AS braspag_fees_amount',
                ]
            );

        return $collection;
    }
}
