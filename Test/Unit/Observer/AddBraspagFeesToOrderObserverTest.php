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
use Magento\Sales\Model\Order\Item;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Braspag\Webkul\Model\BraspagFees\PaymentValidator;
use Braspag\Webkul\Model\BraspagFees\TaxCalculator;

class AddBraspagFeesToOrderObserverTest extends TestCase
{

    /** @var MockObject | ObserverEvent */
    private $observerEventMock;

    /** @var MockObject | PaymentValidator */
    private $paymentValidatorMock;

    /** @var MockObject | TaxCalculator */
    private $taxCalculatorMock;

    /** @var MockObject | Order */
    private $orderMock;

    /** @var MockObject | Quote */
    private $quoteMock;

    /** @var MockObject | Payment */
    private $paymentMock;

    /** @var MockObject | Item */
    private $orderItemMock;

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
                ['method'],
                ['additional_information']
            )
            ->willReturnOnConsecutiveCalls(
                'braspag_pagador_creditcard',
                ['cc_installments' => 4]
            );

        $this->quoteMock
            ->expects($this->exactly(1))
            ->method('getId')
            ->willReturn(1011);
        
        $this->paymentValidatorMock
            ->expects($this->exactly(1))
            ->method('isValid')
            ->willReturn(true);

        $this->applyGetQuoteValues();

        $this->applySetOrderValues();
        
        $this->orderMock
            ->expects($this->exactly(1))
            ->method('getBraspagFees')
            ->willReturn(20);
        $this->orderMock
            ->expects($this->exactly(1))
            ->method('getItems')
            ->willReturn([$this->orderItemMock]);
        $this->applyOrderItemsMocks();

        $this->assertNull($this->instance->execute($this->observerEventMock));
    }

    /**
     * @return void
     */
    private function applyGetQuoteValues()
    {
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
            ->method('getBraspagFees')
            ->willReturn(26.2232);
        $this->quoteMock
            ->expects($this->exactly(1))
            ->method('getBraspagFeesAmount')
            ->willReturn(26.2232);
    }

    /**
     * @return void
     */
    private function applySetOrderValues()
    {
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
            ->method('setBraspagFees')
            ->willReturnSelf();
        $this->orderMock
            ->expects($this->exactly(1))
            ->method('setBraspagFeesAmount')
            ->willReturnSelf();
    }

    /**
     * @return void
     */
    private function applyOrderItemsMocks(): void
    {
        $this->taxCalculatorMock
            ->expects($this->exactly(1))
            ->method('getItemPricesInclBraspagFees')
            ->willReturn([
                66.6,
                66.6,
                66.6,
                66.6
            ]);
        $this->orderItemMock
            ->expects($this->exactly(5))
            ->method('setData')
            ->willReturnSelf();
    }

    /**
     * @return void
     */
    private function getMocks(): void
    {
        $this->observerEventMock = $this
            ->createMock(ObserverEvent::class);
        $this->paymentValidatorMock = $this
            ->createMock(PaymentValidator::class);
        $this->taxCalculatorMock = $this
            ->createMock(TaxCalculator::class);
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
                    'getBaseGrandTotal',
                    'getGrandTotal',
                    'getBraspagFees',
                    'getBraspagFeesAmount'
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
                    'getItems'
                ]
            )
            ->addMethods(
                [
                    'setBraspagFees',
                    'setBraspagFeesAmount',
                    'getBraspagFees'
                ]
            )
            ->getMock();
        $this->orderItemMock = $this
                ->getMockBuilder(Item::class)
                ->disableOriginalConstructor()
                ->onlyMethods(
                    [
                        'setData'
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
                    'paymentValidator' => $this->paymentValidatorMock,
                    'taxCalculator' => $this->taxCalculatorMock
                ]
            );
    }
}
