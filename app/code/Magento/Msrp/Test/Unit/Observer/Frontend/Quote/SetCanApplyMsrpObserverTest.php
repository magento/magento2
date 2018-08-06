<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Msrp\Test\Unit\Observer\Frontend\Quote;

use Magento\Quote\Model\Quote\Address;

class SetCanApplyMsrpObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Msrp\Observer\Frontend\Quote\SetCanApplyMsrpObserver
     */
    protected $observer;

    /**
     * @var \Magento\Msrp\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    protected $canApplyMsrpMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    protected $msrpMock;

    protected function setUp()
    {
        $this->configMock = $this->createMock(\Magento\Msrp\Model\Config::class);
        $this->canApplyMsrpMock = $this->createMock(\Magento\Msrp\Model\Quote\Address\CanApplyMsrp::class);
        $this->msrpMock = $this->createMock(\Magento\Msrp\Model\Quote\Msrp::class);

        $this->observer = new \Magento\Msrp\Observer\Frontend\Quote\SetCanApplyMsrpObserver(
            $this->configMock,
            $this->canApplyMsrpMock,
            $this->msrpMock
        );
    }

    public function testSetQuoteCanApplyMsrpIfMsrpCanApply()
    {
        $quoteId = 100;
        $eventMock = $this->createPartialMock(\Magento\Framework\Event::class, ['getQuote']);
        $quoteMock = $this->createPartialMock(\Magento\Quote\Model\Quote::class, ['getAllAddresses', 'getId']);
        $observerMock = $this->createMock(\Magento\Framework\Event\Observer::class);

        $observerMock->expects($this->once())->method('getEvent')->willReturn($eventMock);
        $eventMock->expects($this->once())->method('getQuote')->willReturn($quoteMock);
        $this->configMock->expects($this->once())->method('isEnabled')->willReturn(true);
        $this->msrpMock->expects($this->once())->method('setCanApplyMsrp')->with($quoteId, true);

        $addressMock = $this->createPartialMock(\Magento\Customer\Model\Address\AbstractAddress::class, ['__wakeup']);
        $this->canApplyMsrpMock->expects($this->once())->method('isCanApplyMsrp')->willReturn(true);

        $quoteMock->expects($this->once())->method('getAllAddresses')->willReturn([$addressMock]);
        $quoteMock->expects($this->once())->method('getId')->willReturn($quoteId);
        $this->observer->execute($observerMock);
    }

    public function setQuoteCanApplyMsrpDataProvider()
    {
        $quoteId = 100;
        $eventMock = $this->createPartialMock(\Magento\Framework\Event::class, ['getQuote']);
        $quoteMock = $this->createPartialMock(\Magento\Quote\Model\Quote::class, ['getAllAddresses', 'getId']);
        $observerMock = $this->createMock(\Magento\Framework\Event\Observer::class);

        $observerMock->expects($this->once())->method('getEvent')->willReturn($eventMock);
        $eventMock->expects($this->once())->method('getQuote')->willReturn($quoteMock);
        $this->configMock->expects($this->once())->method('isEnabled')->willReturn(true);
        $this->msrpMock->expects($this->once())->method('setCanApplyMsrp')->with($quoteId, false);

        $addressMock = $this->createPartialMock(\Magento\Customer\Model\Address\AbstractAddress::class, ['__wakeup']);
        $this->canApplyMsrpMock->expects($this->once())->method('isCanApplyMsrp')->willReturn(false);

        $quoteMock->expects($this->once())->method('getAllAddresses')->willReturn([$addressMock]);
        $quoteMock->expects($this->once())->method('getId')->willReturn($quoteId);
        $this->observer->execute($observerMock);
    }

    public function testSetQuoteCanApplyMsrpIfMsrpDisabled()
    {
        $quoteId = 100;
        $eventMock = $this->createPartialMock(\Magento\Framework\Event::class, ['getQuote']);
        $quoteMock = $this->createPartialMock(\Magento\Quote\Model\Quote::class, ['getAllAddresses', 'getId']);
        $observerMock = $this->createMock(\Magento\Framework\Event\Observer::class);

        $observerMock->expects($this->once())->method('getEvent')->willReturn($eventMock);
        $eventMock->expects($this->once())->method('getQuote')->willReturn($quoteMock);
        $this->configMock->expects($this->once())->method('isEnabled')->willReturn(false);
        $this->msrpMock->expects($this->once())->method('setCanApplyMsrp')->with($quoteId, false);
        $quoteMock->expects($this->once())->method('getId')->willReturn($quoteId);
        $this->observer->execute($observerMock);
    }
}
