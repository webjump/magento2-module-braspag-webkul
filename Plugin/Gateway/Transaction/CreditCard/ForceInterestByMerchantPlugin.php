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

namespace Braspag\Webkul\Plugin\Gateway\Transaction\CreditCard;

use Braspag\Webkul\Gateway\Transaction\Base\Config\ConfigInterface;
use Webjump\BraspagPagador\Gateway\Transaction\CreditCard\Resource\Authorize\Request as CreditCardRequest;

class ForceInterestByMerchantPlugin
{
    /** @var string */
    const INTEREST_FORCED = 'ByMerchant';

    /** @var ConfigInterface */
    protected $creditCardConfig;

    /**
     * Construct method
     *
     * @param ConfigInterface $creditCardConfig
     */
    public function __construct(ConfigInterface $creditCardConfig)
    {
        $this->creditCardConfig = $creditCardConfig;
    }

    /**
     * When force flag is enable, forces to ByMerchant
     *
     * @param CreditCardRequest $subject
     * @param string $interest
     * @return void
     */
    public function afterGetPaymentInterest(
        CreditCardRequest $subject,
        $interest
    ) {
        if ((bool) $this->creditCardConfig
            ->getForceInterestByMerchantBaseCreditCard()
        ) {
            $interest = self::INTEREST_FORCED;
        }

        return $interest;
    }
}
