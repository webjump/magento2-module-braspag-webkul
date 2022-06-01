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
        $collection
            ->columns(
                [
                    'SUM(main_table.braspag_fees) AS braspag_fees',
                    'SUM(main_table.braspag_fees_amount) AS braspag_fees_amount',
                ]
            );

        return $collection;
    }
}
