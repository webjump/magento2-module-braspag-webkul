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

use Braspag\Webkul\Observer\AddBraspagFeesToQuoteObserver as Observer;
use Magento\Framework\Event\Observer as ObserverEvent;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Braspag\Webkul\Model\Observer\BraspagFeesToQuote as BraspagFeesHelper;
use Braspag\Webkul\Model\BraspagFees\PaymentValidator;

class AddBraspagFeesToQuoteObserverTest extends TestCase
{

    /** @var MockObject | ObserverEvent */
    private $observerEventMock;

    /** @var MockObject | BraspagFeesHelper */
    private $braspagFeesHelperMock;
    
    /** @var MockObject | PaymentValidator */
    private $paymentValidatorMock;

    /** @var MockObject | CartRepositoryInterface */
    private $quoteRepositoryMock;

    /** @var MockObject | Quote */
    private $quoteMock;

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
        $this->braspagFeesHelperMock
            ->expects($this->exactly(1))
            ->method('getPaymentInformation')
            ->willReturn(
                [
                    $this->quoteMock,
                    'braspag_pagador_creditcard',
                    3
                ]
            );
        $this->paymentValidatorMock
            ->expects($this->exactly(1))
            ->method('isValid')
            ->willReturn(true);
        
        $this->braspagFeesHelperMock
            ->expects($this->exactly(1))
            ->method('getNewQuoteInformation')
            ->willReturn(
                [
                    '120',
                    '120',
                    '20',
                    '20'
                ]
            );
        $this->quoteMock
            ->expects($this->exactly(1))
            ->method('setBaseGrandTotal')
            ->willReturnSelf();
        $this->quoteMock
            ->expects($this->exactly(1))
            ->method('setGrandTotal')
            ->willReturnSelf();
        $this->quoteMock
            ->expects($this->exactly(1))
            ->method('setBraspagFees')
            ->willReturnSelf();
        $this->quoteMock
            ->expects($this->exactly(1))
            ->method('setBraspagFeesAmount')
            ->willReturnSelf();
        $this->quoteMock
            ->expects($this->exactly(1))
            ->method('collectTotals')
            ->willReturnSelf();
        $this->quoteRepositoryMock
            ->expects($this->exactly(1))
            ->method('save')
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
        $this->paymentValidatorMock = $this->createMock(PaymentValidator::class);
        $this->braspagFeesHelperMock = $this->createMock(BraspagFeesHelper::class);
        $this->quoteRepositoryMock = $this->createMock(CartRepositoryInterface::class);
        $this->quoteMock = $this
            ->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                [
                    'collectTotals'
                ]
            )
            ->addMethods(
                [
                    'setBaseGrandTotal',
                    'setGrandTotal',
                    'setBraspagFees',
                    'setBraspagFeesAmount'
                ]
            )
            ->getMock();
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
                    'helper' => $this->braspagFeesHelperMock,
                    'paymentValidator' => $this->paymentValidatorMock,
                    'quoteRepository' => $this->quoteRepositoryMock
                ]
            );
    }
}
