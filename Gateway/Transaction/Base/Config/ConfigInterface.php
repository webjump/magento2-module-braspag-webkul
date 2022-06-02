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

interface ConfigInterface
{
    const CONFIG_XML_BRASPAG_BASE_CREDITCARD_INSTALLMETNS_FORCE_INTEREST_BYMERCHANT = 'payment/braspag_pagador_creditcard/installments_force_interest_by_merchant';

    public function getForceInterestByMerchantBaseCreditCard();
}
