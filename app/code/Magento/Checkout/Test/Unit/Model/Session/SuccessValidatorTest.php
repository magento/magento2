<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\Unit\Model\Session;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class SuccessValidatorTest extends \PHPUnit\Framework\TestCase
{
    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
    }

    public function testIsValid()
    {
        $checkoutSession = $this->getMockBuilder(
            \Magento\Checkout\Model\Session::class
        )->disableOriginalConstructor()->getMock();
        $this->assertFalse($this->createSuccessValidator($checkoutSession)->isValid($checkoutSession));
    }

    public function testIsValidWithNotEmptyGetLastSuccessQuoteId()
    {
        $checkoutSession = $this->getMockBuilder(
            \Magento\Checkout\Model\Session::class
        )->disableOriginalConstructor()->getMock();

        $checkoutSession->expects(
            $this->at(0)
        )->method(
            '__call'
        )->with(
            'getLastSuccessQuoteId'
        )->willReturn(
            1
        );

        $checkoutSession->expects($this->at(1))->method('__call')->with('getLastQuoteId')->willReturn(0);

        $this->assertFalse($this->createSuccessValidator($checkoutSession)->isValid($checkoutSession));
    }

    public function testIsValidWithEmptyQuoteAndOrder()
    {
        $checkoutSession = $this->getMockBuilder(
            \Magento\Checkout\Model\Session::class
        )->disableOriginalConstructor()->getMock();
        $checkoutSession->expects(
            $this->at(0)
        )->method(
            '__call'
        )->with(
            'getLastSuccessQuoteId'
        )->willReturn(
            1
        );

        $checkoutSession->expects($this->at(1))->method('__call')->with('getLastQuoteId')->willReturn(1);

        $checkoutSession->expects($this->at(2))->method('__call')->with('getLastOrderId')->willReturn(0);

        $this->assertFalse($this->createSuccessValidator($checkoutSession)->isValid($checkoutSession));
    }

    public function testIsValidTrue()
    {
        $checkoutSession = $this->getMockBuilder(
            \Magento\Checkout\Model\Session::class
        )->disableOriginalConstructor()->getMock();
        $checkoutSession->expects(
            $this->at(0)
        )->method(
            '__call'
        )->with(
            'getLastSuccessQuoteId'
        )->willReturn(
            1
        );

        $checkoutSession->expects($this->at(1))->method('__call')->with('getLastQuoteId')->willReturn(1);

        $checkoutSession->expects($this->at(2))->method('__call')->with('getLastOrderId')->willReturn(1);

        $this->assertTrue($this->createSuccessValidator($checkoutSession)->isValid($checkoutSession));
    }

    /**
     * @param \PHPUnit\Framework\MockObject\MockObject $checkoutSession
     * @return object
     */
    protected function createSuccessValidator(\PHPUnit\Framework\MockObject\MockObject $checkoutSession)
    {
        return $this->objectManagerHelper->getObject(
            \Magento\Checkout\Model\Session\SuccessValidator::class,
            ['checkoutSession' => $checkoutSession]
        );
    }
}
