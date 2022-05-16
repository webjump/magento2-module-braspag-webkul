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

namespace Brapag\Webkul\Test\Unit\Observer;

use Braspag\Webkul\Observer\AddBraspagFeesToOrderObserver as Observer;
use Magento\Framework\Event\Observer as ObserverEvent;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Payment;
use Magento\Sales\Model\Order;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Webjump\BraspagPagador\Gateway\Transaction\Base\Config\InstallmentsConfigInterface;

class AddBraspagFeesToOrderObserverTest extends TestCase
{

    /** @var MockObject | ObserverEvent */
    private $observerEventMock;

    /** @var MockObject | InstallmentsConfigInterface */
    private $installmentsConfigMock;

    /** @var MockObject | Order */
    private $orderMock;

    /** @var MockObject | Quote */
    private $quoteMock;

    /** @var MockObject | Payment */
    private $paymentMock;

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
    public function testObserverShouldPerformWithoutError(): void
    {
        $this->observerEventMock
            ->expects($this->exactly(2))
            ->method('getData')
            ->withConsecutive(
                ['order'],
                ['quote']
            )
            ->willReturnOnConsecutiveCalls(
                $this->orderMock,
                $this->quoteMock
            );
        $this->quoteMock
            ->expects($this->exactly(1))
            ->method('getPayment')
            ->willReturn($this->paymentMock);

        $this->paymentMock
            ->expects($this->exactly(2))
            ->method('getData')
            ->withConsecutive(
                ['additional_information'],
                ['method']
            )
            ->willReturnOnConsecutiveCalls(
                ['cc_installments' => 4],
                'braspag_pagador_creditcard'
            );

        $this->quoteMock
            ->expects($this->exactly(1))
            ->method('getId')
            ->willReturn(1011);

        $this->installmentsConfigMock
            ->expects($this->exactly(1))
            ->method('isActive')
            ->willReturn(true);

        $this->installmentsConfigMock
            ->expects($this->any())
            ->method('getInstallmentsMaxWithoutInterest')
            ->willReturn(1);

        $this->installmentsConfigMock
            ->expects($this->exactly(1))
            ->method('getInterestRate')
            ->willReturn(2.25);

        $this->quoteMock
            ->expects($this->exactly(1))
            ->method('getBaseGrandTotal')
            ->willReturn(3015);
        $this->quoteMock
            ->expects($this->exactly(1))
            ->method('getGrandTotal')
            ->willReturn(3015);
        $this->quoteMock
            ->expects($this->exactly(1))
            ->method('getBaseSubtotal')
            ->willReturn(3000);
        $this->quoteMock
            ->expects($this->exactly(1))
            ->method('getSubtotal')
            ->willReturn(3000);
        $this->quoteMock
            ->expects($this->exactly(1))
            ->method('getBraspagFees')
            ->willReturn(26.2232);

        $this->orderMock
            ->expects($this->exactly(1))
            ->method('setBaseGrandTotal')
            ->willReturnSelf();
        $this->orderMock
            ->expects($this->exactly(1))
            ->method('setGrandTotal')
            ->willReturnSelf();
        $this->orderMock
            ->expects($this->exactly(1))
            ->method('setBaseSubtotal')
            ->willReturnSelf();
        $this->orderMock
            ->expects($this->exactly(1))
            ->method('setSubtotal')
            ->willReturnSelf();
        $this->orderMock
            ->expects($this->exactly(1))
            ->method('setBraspagFees')
            ->willReturnSelf();

        $this->assertNull($this->instance->execute($this->observerEventMock));
    }

    /**
     * @return void
     */
    private function getMocks(): void
    {
        $this->observerEventMock = $this
            ->createMock(ObserverEvent::class);
        $this->installmentsConfigMock = $this
            ->createMock(InstallmentsConfigInterface::class);
        $this->quoteMock = $this
            ->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                [
                    'getId',
                    'getPayment'
                ]
            )
            ->addMethods(
                [
                    'getBraspagFees',
                    'getBaseGrandTotal',
                    'getGrandTotal',
                    'getBaseSubtotal',
                    'getSubtotal'
                ]
            )
            ->getMock();
        $this->orderMock = $this
            ->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                [
                    'setBaseGrandTotal',
                    'setGrandTotal',
                    'setBaseSubtotal',
                    'setSubtotal'
                ]
            )
            ->addMethods(
                [
                    'setBraspagFees'
                ]
            )
            ->getMock();
        $this->paymentMock = $this->createMock(Payment::class);
    }

    /**
     * @return void
     */
    private function setInstance(): void
    {
        $objectManager = new ObjectManager($this);
        $this->instance = $objectManager
            ->getObject(
                Observer::class,
                [
                    'installmentsConfig' => $this->installmentsConfigMock
                ]
            );
    }
}
