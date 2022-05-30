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

use Webjump\BraspagPagador\Gateway\Transaction\Base\Config\InstallmentsConfigInterface;

class PaymentValidator
{

    /** @var string */
    const CC_PAYMENT_METHOD = 'braspag_pagador_creditcard';

    /** @var InstallmentsConfigInterface */
    private $installmentsConfig;

    /**
     * Construct method
     *
     * @param InstallmentsConfigInterface $installmentsConfig
     */
    public function __construct(
        InstallmentsConfigInterface $installmentsConfig
    ) {
        $this->installmentsConfig = $installmentsConfig;
    }

    /**
     * Validates if could use braspag fees
     *
     * @param string $method
     * @param int | string $ccInstallments
     * @return boolean
     */
    public function isValid($method, $ccInstallments)
    {
        if ($this->installmentsConfig->isActive()
            && $this->installmentsConfig->isInterestByIssuer()
            && $method == self::CC_PAYMENT_METHOD
            && $ccInstallments > (int) $this->installmentsConfig->getInstallmentsMaxWithoutInterest()
            && (bool) $this->installmentsConfig->getInterestRate()
        ) {
            return true;
        }

        return false;
    }
}
