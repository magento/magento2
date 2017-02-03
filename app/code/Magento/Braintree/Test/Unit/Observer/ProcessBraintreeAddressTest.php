<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Braintree\Test\Unit\Observer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class ProcessBraintreeAddressTest
 */
class ProcessBraintreeAddressTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Braintree\Observer\ProcessBraintreeAddress
     */
    protected $processBraintreeAddressObserver;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->processBraintreeAddressObserver = $this->objectManagerHelper->getObject(
            'Magento\Braintree\Observer\ProcessBraintreeAddress',
            []
        );
    }

    public function testProcessBraintreeAddressIfPaymentIsBraintree()
    {
        $billingAddressMock = $this->getMock(
            'Magento\Quote\Model\Quote\Address',
            ['setShouldIgnoreValidation'],
            [],
            '',
            false
        );
        $eventMock = $this->getMock('Magento\Framework\Event', ['getQuote'], [], '', false);
        $observerMock = $this->getMock('Magento\Framework\Event\Observer', [], [], '', false);
        $observerMock->expects($this->once())->method('getEvent')->willReturn($eventMock);
        $quoteMock = $this->getMock('Magento\Quote\Model\Quote', [], [], '', false);
        $eventMock->expects($this->once())->method('getQuote')->willReturn($quoteMock);
        $paymentMock = $this->getMock('Magento\Quote\Model\Quote\Payment', [], [], '', false);
        $quoteMock->expects($this->once())->method('getPayment')->willReturn($paymentMock);
        $paymentMock
            ->expects($this->once())
            ->method('getMethod')
            ->willReturn(\Magento\Braintree\Model\PaymentMethod\PayPal:: METHOD_CODE);
        $quoteMock->expects($this->once())->method('getBillingAddress')->willReturn($billingAddressMock);
        $billingAddressMock->expects($this->once())->method('setShouldIgnoreValidation')->with(true);
        $this->processBraintreeAddressObserver->execute($observerMock);
    }

    public function testProcessBraintreeAddressIfPaymentIsNotBraintree()
    {
        $eventMock = $this->getMock('Magento\Framework\Event', ['getQuote'], [], '', false);
        $observerMock = $this->getMock('Magento\Framework\Event\Observer', [], [], '', false);
        $observerMock->expects($this->once())->method('getEvent')->willReturn($eventMock);
        $quoteMock = $this->getMock('Magento\Quote\Model\Quote', [], [], '', false);
        $eventMock->expects($this->once())->method('getQuote')->willReturn($quoteMock);
        $paymentMock = $this->getMock('Magento\Quote\Model\Quote\Payment', [], [], '', false);
        $quoteMock->expects($this->once())->method('getPayment')->willReturn($paymentMock);
        $paymentMock
            ->expects($this->once())
            ->method('getMethod')
            ->willReturn('checkmo');
        $quoteMock->expects($this->never())->method('getBillingAddress');
        $this->processBraintreeAddressObserver->execute($observerMock);
    }
}
