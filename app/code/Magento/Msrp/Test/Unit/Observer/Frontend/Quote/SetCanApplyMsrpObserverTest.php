<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Msrp\Test\Unit\Observer\Frontend\Quote;

use Magento\Customer\Model\Address\AbstractAddress;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Msrp\Model\Config;
use Magento\Msrp\Model\Quote\Address\CanApplyMsrp;
use Magento\Msrp\Model\Quote\Msrp;
use Magento\Msrp\Observer\Frontend\Quote\SetCanApplyMsrpObserver;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SetCanApplyMsrpObserverTest extends TestCase
{
    /**
     * @var SetCanApplyMsrpObserver
     */
    protected $observer;

    /**
     * @var Config|MockObject
     */
    protected $configMock;

    /** @var  MockObject */
    protected $canApplyMsrpMock;

    /** @var  MockObject */
    protected $msrpMock;

    protected function setUp(): void
    {
        $this->configMock = $this->createMock(Config::class);
        $this->canApplyMsrpMock = $this->createMock(CanApplyMsrp::class);
        $this->msrpMock = $this->createMock(Msrp::class);

        $this->observer = new SetCanApplyMsrpObserver(
            $this->configMock,
            $this->canApplyMsrpMock,
            $this->msrpMock
        );
    }

    public function testSetQuoteCanApplyMsrpIfMsrpCanApply()
    {
        $quoteId = 100;
        $eventMock = $this->getMockBuilder(Event::class)
            ->addMethods(['getQuote'])
            ->disableOriginalConstructor()
            ->getMock();
        $quoteMock = $this->createPartialMock(Quote::class, ['getAllAddresses', 'getId']);
        $observerMock = $this->createMock(Observer::class);

        $observerMock->expects($this->once())->method('getEvent')->willReturn($eventMock);
        $eventMock->expects($this->once())->method('getQuote')->willReturn($quoteMock);
        $this->configMock->expects($this->once())->method('isEnabled')->willReturn(true);
        $this->msrpMock->expects($this->once())->method('setCanApplyMsrp')->with($quoteId, true);

        $addressMock = $this->createPartialMock(AbstractAddress::class, ['__wakeup']);
        $this->canApplyMsrpMock->expects($this->once())->method('isCanApplyMsrp')->willReturn(true);

        $quoteMock->expects($this->once())->method('getAllAddresses')->willReturn([$addressMock]);
        $quoteMock->expects($this->once())->method('getId')->willReturn($quoteId);
        $this->observer->execute($observerMock);
    }

    public function setQuoteCanApplyMsrpDataProvider()
    {
        $quoteId = 100;
        $eventMock = $this->getMockBuilder(Event::class)
            ->addMethods(['getQuote'])
            ->disableOriginalConstructor()
            ->getMock();
        $quoteMock = $this->createPartialMock(Quote::class, ['getAllAddresses', 'getId']);
        $observerMock = $this->createMock(Observer::class);

        $observerMock->expects($this->once())->method('getEvent')->willReturn($eventMock);
        $eventMock->expects($this->once())->method('getQuote')->willReturn($quoteMock);
        $this->configMock->expects($this->once())->method('isEnabled')->willReturn(true);
        $this->msrpMock->expects($this->once())->method('setCanApplyMsrp')->with($quoteId, false);

        $addressMock = $this->createPartialMock(AbstractAddress::class, ['__wakeup']);
        $this->canApplyMsrpMock->expects($this->once())->method('isCanApplyMsrp')->willReturn(false);

        $quoteMock->expects($this->once())->method('getAllAddresses')->willReturn([$addressMock]);
        $quoteMock->expects($this->once())->method('getId')->willReturn($quoteId);
        $this->observer->execute($observerMock);
    }

    public function testSetQuoteCanApplyMsrpIfMsrpDisabled()
    {
        $quoteId = 100;
        $eventMock = $this->getMockBuilder(Event::class)
            ->addMethods(['getQuote'])
            ->disableOriginalConstructor()
            ->getMock();
        $quoteMock = $this->createPartialMock(Quote::class, ['getAllAddresses', 'getId']);
        $observerMock = $this->createMock(Observer::class);

        $observerMock->expects($this->once())->method('getEvent')->willReturn($eventMock);
        $eventMock->expects($this->once())->method('getQuote')->willReturn($quoteMock);
        $this->configMock->expects($this->once())->method('isEnabled')->willReturn(false);
        $this->msrpMock->expects($this->once())->method('setCanApplyMsrp')->with($quoteId, false);
        $quoteMock->expects($this->once())->method('getId')->willReturn($quoteId);
        $this->observer->execute($observerMock);
    }
}
