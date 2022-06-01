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

namespace Brapag\Webkul\Test\Unit\Model\BraspagFees;

use Braspag\Webkul\Model\BraspagFees\PaymentValidator as Model;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Webjump\BraspagPagador\Gateway\Transaction\Base\Config\InstallmentsConfigInterface;

class PaymentValidatorTest extends TestCase
{

    /** @var MockObject | InstallmentsConfigInterface */
    private $installmentsConfigMock;

    /** @var Observer */
    private $instance;

    /**
     * Setup test
     */
    protected function setUp(): void
    {
        $this->getMocks();
        $this->setInstance();
    }

    /**
     * @return void
     */
    public function testShouldReturnTrue(): void
    {
        $this->installmentsConfigMock
            ->expects($this->exactly(1))
            ->method('isActive')
            ->willReturn(true);
        $this->installmentsConfigMock
            ->expects($this->exactly(1))
            ->method('isInterestByIssuer')
            ->willReturn(true);
        $this->installmentsConfigMock
            ->expects($this->exactly(1))
            ->method('getInstallmentsMaxWithoutInterest')
            ->willReturn(1);
        $this->installmentsConfigMock
            ->expects($this->exactly(1))
            ->method('getInterestRate')
            ->willReturn(2.24);

        $this->assertTrue($this->instance->isValid('braspag_pagador_creditcard','3'));
    }

    /**
     * @dataProvider isNotValidDataProvider
     * @return void
     */
    public function testShouldReturnFalse(
        $isActive,
        $isInterestByIssuer,
        $installmentsMaxWithoutInterest,
        $interestRate
    ): void {
        $this->installmentsConfigMock
            ->expects($this->exactly(1))
            ->method('isActive')
            ->willReturn($isActive);
        $this->installmentsConfigMock
            ->expects($this->any())
            ->method('isInterestByIssuer')
            ->willReturn($isInterestByIssuer);
        $this->installmentsConfigMock
            ->expects($this->any())
            ->method('getInstallmentsMaxWithoutInterest')
            ->willReturn($installmentsMaxWithoutInterest);
        $this->installmentsConfigMock
            ->expects($this->any())
            ->method('getInterestRate')
            ->willReturn($interestRate);

        $this->assertFalse($this->instance->isValid('braspag_pagador_creditcard','3'));
    }

    /**
     * @return array
     */
    public function isNotValidDataProvider(): array
    {   
        return [
            'Is not active' => [
                'isActive' => false,
                'isInterestByIssuer' => true,
                'installmentsMaxWithoutInterest' => 1,
                'interestRate' => 3
            ],
            'Is not interest by issuer' => [
                'isActive' => true,
                'isInterestByIssuer' => false,
                'installmentsMaxWithoutInterest' => 1,
                'interestRate' => 3
            ],
            'Installments is not greater than max' => [
                'isActive' => false,
                'isInterestByIssuer' => true,
                'installmentsMaxWithoutInterest' => 5,
                'interestRate' => 2
            ],
            'Interest rate is null' => [
                'isActive' => true,
                'isInterestByIssuer' => true,
                'installmentsMaxWithoutInterest' => 1,
                'interestRate' => null
            ]
        ];
    }

    /**
     * @return void
     */
    private function getMocks(): void
    {
        $this->installmentsConfigMock = $this
            ->createMock(InstallmentsConfigInterface::class);
    }

    /**
     * @return void
     */
    private function setInstance(): void
    {
        $objectManager = new ObjectManager($this);
        $this->instance = $objectManager
            ->getObject(
                Model::class,
                [
                    'installmentsConfig' => $this->installmentsConfigMock
                ]
            );
    }
}
