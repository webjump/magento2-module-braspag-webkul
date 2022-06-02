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

namespace Braspag\Webkul\Gateway\Transaction\Base\Config;

use Webjump\BraspagPagador\Gateway\Transaction\Base\Config\Config as BaseConfig;

class Config extends BaseConfig implements ConfigInterface
{
    /**
     * @return bool
     */
    public function getForceInterestByMerchantBaseCreditCard()
    {
        return $this->_getConfig(self::CONFIG_XML_BRASPAG_BASE_CREDITCARD_INSTALLMETNS_FORCE_INTEREST_BYMERCHANT);
    }
}
