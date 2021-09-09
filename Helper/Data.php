<?php
/**
 * @author      Webjump Core Team <dev@webjump.com.br>
 * @copyright   2021 Webjump (http://www.webjump.com.br)
 * @license     http://www.webjump.com.br  Copyright
 *
 * @link        http://www.webjump.com.br
 */

namespace Braspag\Webkul\Helper;

use Magento\Framework\App\ObjectManager;

/**
 * Webkul Marketplace Helper Data.
 */
class Data extends \Webkul\Marketplace\Helper\Data
{
    /**
     * @param int $mpassignproductId
     * @param int $proid
     * @return int
     */
    public function getSellerId($mpassignproductId, $proid)
    {
        $sellerId = 0;
        if ($mpassignproductId) {
            $this->assignItemsFactory = ObjectManager::getInstance()->create(
                \Webkul\MpAssignProduct\Model\ItemsFactory::class
            );
            $mpassignModel = $this->assignItemsFactory->create()->load($mpassignproductId);
            $sellerId = $mpassignModel->getSellerId();
        } else {

            $collection = $this->_mpProductCollectionFactory->create()
                                ->addFieldToFilter('mage_pro_row_id', ['eq' => $proid]);
            foreach ($collection as $temp) {
                $sellerId = $temp->getSellerId();
            }
        }

        return $sellerId;
    }
}
