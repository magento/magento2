<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Msrp\Test\Unit\Model\Observer\Frontend\Quote;

use Magento\Quote\Model\Quote\Address;

/**
 * Tests Magento\Msrp\Model\Observer\Frontend\Quote\SetCanApplyMsrp
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SetCanApplyMsrpTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Msrp\Model\Observer\Frontend\Quote\SetCanApplyMsrp
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
        $this->configMock = $this->getMock('\Magento\Msrp\Model\Config', [], [], '', false);
        $this->canApplyMsrpMock = $this->getMock('\Magento\Msrp\Model\Quote\Address\CanApplyMsrp', [], [], '', false);
        $this->msrpMock = $this->getMock('\Magento\Msrp\Model\Quote\Msrp', [], [], '', false);

        $this->observer = new \Magento\Msrp\Model\Observer\Frontend\Quote\SetCanApplyMsrp(
            $this->configMock,
            $this->canApplyMsrpMock,
            $this->msrpMock
        );
    }

    public function testSetQuoteCanApplyMsrpIfMsrpCanApply()
    {
        $quoteId = 100;
        $eventMock = $this->getMock('\Magento\Framework\Event', ['getQuote'], [], '', false);
        $quoteMock = $this->getMock('\Magento\Quote\Model\Quote', ['getAllAddresses', 'getId'], [], '', false);
        $observerMock = $this->getMock('\Magento\Framework\Event\Observer', [], [], '', false);

        $observerMock->expects($this->once())->method('getEvent')->willReturn($eventMock);
        $eventMock->expects($this->once())->method('getQuote')->willReturn($quoteMock);
        $this->configMock->expects($this->once())->method('isEnabled')->willReturn(true);
        $this->msrpMock->expects($this->once())->method('setCanApplyMsrp')->with($quoteId, true);

        $addressMock = $this->getMock('\Magento\Customer\Model\Address\AbstractAddress', ['__wakeup'], [], '', false);
        $this->canApplyMsrpMock->expects($this->once())->method('isCanApplyMsrp')->willReturn(true);

        $quoteMock->expects($this->once())->method('getAllAddresses')->willReturn([$addressMock]);
        $quoteMock->expects($this->once())->method('getId')->willReturn($quoteId);
        $this->observer->execute($observerMock);
    }

    public function testSetQuoteCanApplyMsrpIfMsrpCanNotApply()
    {
        $quoteId = 100;
        $eventMock = $this->getMock('\Magento\Framework\Event', ['getQuote'], [], '', false);
        $quoteMock = $this->getMock('\Magento\Quote\Model\Quote', ['getAllAddresses', 'getId'], [], '', false);
        $observerMock = $this->getMock('\Magento\Framework\Event\Observer', [], [], '', false);

        $observerMock->expects($this->once())->method('getEvent')->willReturn($eventMock);
        $eventMock->expects($this->once())->method('getQuote')->willReturn($quoteMock);
        $this->configMock->expects($this->once())->method('isEnabled')->willReturn(true);
        $this->msrpMock->expects($this->once())->method('setCanApplyMsrp')->with($quoteId, false);

        $addressMock = $this->getMock('\Magento\Customer\Model\Address\AbstractAddress', ['__wakeup'], [], '', false);
        $this->canApplyMsrpMock->expects($this->once())->method('isCanApplyMsrp')->willReturn(false);

        $quoteMock->expects($this->once())->method('getAllAddresses')->willReturn([$addressMock]);
        $quoteMock->expects($this->once())->method('getId')->willReturn($quoteId);
        $this->observer->execute($observerMock);
    }

    public function testSetQuoteCanApplyMsrpIfMsrpDisabled()
    {
        $quoteId = 100;
        $eventMock = $this->getMock('\Magento\Framework\Event', ['getQuote'], [], '', false);
        $quoteMock = $this->getMock('\Magento\Quote\Model\Quote', ['getAllAddresses', 'getId'], [], '', false);
        $observerMock = $this->getMock('\Magento\Framework\Event\Observer', [], [], '', false);

        $observerMock->expects($this->once())->method('getEvent')->willReturn($eventMock);
        $eventMock->expects($this->once())->method('getQuote')->willReturn($quoteMock);
        $this->configMock->expects($this->once())->method('isEnabled')->willReturn(false);
        $this->msrpMock->expects($this->once())->method('setCanApplyMsrp')->with($quoteId, false);
        $quoteMock->expects($this->once())->method('getId')->willReturn($quoteId);
        $this->observer->execute($observerMock);
    }
}
