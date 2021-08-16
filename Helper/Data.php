<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
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
