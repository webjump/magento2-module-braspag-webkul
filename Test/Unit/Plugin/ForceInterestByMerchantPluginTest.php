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

namespace Brapag\Webkul\Test\Unit\Plugin;

use Webjump\BraspagPagador\Gateway\Transaction\CreditCard\Resource\Authorize\Request as CreditCardRequest;
use Braspag\Webkul\Gateway\Transaction\Base\Config\ConfigInterface;
use Braspag\Webkul\Plugin\Gateway\Transaction\CreditCard\ForceInterestByMerchantPlugin as Plugin;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ForceInterestByMerchantPluginTest extends TestCase
{

    /** @var MockObject | CreditCardRequest */
    private $creditCardRequestMock;

    /** @var MockObject | ConfigInterface */
    private $configMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->getMocks();
        $this->setInstance();
    }

    /**
     * @dataProvider provider
     * @return void
     */
    public function testShouldReturnByMerchantWhenForceFlagIsEnable($interest, $forceByMerchant, $expectedValue)
    {
        $this->configMock
            ->expects($this->exactly(1))
            ->method('getForceInterestByMerchantBaseCreditCard')
            ->willReturn($forceByMerchant);

        $interestResult = $this->instance
            ->afterGetPaymentInterest($this->creditCardRequestMock, $interest);

        $this->assertEquals($expectedValue, $interestResult);
    }
    
    /**
     * @return void
     */
    public function provider()
    {
        return [
            'Interest is ByIssuer and forceByMerchant is enable' => [
                'interest' => 'ByIssuer',
                'forceByMerchant' => true,
                'expectedValue' => 'ByMerchant'
            ],
            'Interest is ByIssuer and forceByMerchant is disable' => [
                'interest' => 'ByIssuer',
                'forceByMerchant' => false,
                'expectedValue' => 'ByIssuer'
            ],
            'Interest is ByMerchant and forceByMerchant is enable' => [
                'interest' => 'ByMerchant',
                'forceByMerchant' => true,
                'expectedValue' => 'ByMerchant'
            ],

            'Interest is ByMerchant and forceByMerchant is disable' => [
                'interest' => 'ByMerchant',
                'forceByMerchant' => false,
                'expectedValue' => 'ByMerchant'
            ],
        ];
    }

    /**
     * @return void
     */
    private function getMocks()
    {
        $this->creditCardRequestMock = $this->createMock(CreditCardRequest::class);
        $this->configMock = $this->createMock(ConfigInterface::class);
    }

    /**
     * @return void
     */
    private function setInstance()
    {
        $objectManager = new ObjectManager($this);
        $this->instance = $objectManager
            ->getObject(
                Plugin::class,
                [
                    'creditCardConfig' => $this->configMock
                ]
            );
    }
}
