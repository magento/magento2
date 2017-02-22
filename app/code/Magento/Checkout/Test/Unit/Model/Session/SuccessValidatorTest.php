<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\Unit\Model\Session;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class SuccessValidatorTest extends \PHPUnit_Framework_TestCase
{
    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
    }

    public function testIsValid()
    {
        $checkoutSession = $this->getMockBuilder(
            '\Magento\Checkout\Model\Session'
        )->disableOriginalConstructor()->getMock();
        $this->assertFalse($this->createSuccessValidator($checkoutSession)->isValid($checkoutSession));
    }

    public function testIsValidWithNotEmptyGetLastSuccessQuoteId()
    {
        $checkoutSession = $this->getMockBuilder(
            'Magento\Checkout\Model\Session'
        )->disableOriginalConstructor()->getMock();

        $checkoutSession->expects(
            $this->at(0)
        )->method(
            '__call'
        )->with(
            'getLastSuccessQuoteId'
        )->will(
            $this->returnValue(1)
        );

        $checkoutSession->expects($this->at(1))->method('__call')->with('getLastQuoteId')->will($this->returnValue(0));

        $this->assertFalse($this->createSuccessValidator($checkoutSession)->isValid($checkoutSession));
    }

    public function testIsValidWithEmptyQuoteAndOrder()
    {
        $checkoutSession = $this->getMockBuilder(
            'Magento\Checkout\Model\Session'
        )->disableOriginalConstructor()->getMock();
        $checkoutSession->expects(
            $this->at(0)
        )->method(
            '__call'
        )->with(
            'getLastSuccessQuoteId'
        )->will(
            $this->returnValue(1)
        );

        $checkoutSession->expects($this->at(1))->method('__call')->with('getLastQuoteId')->will($this->returnValue(1));

        $checkoutSession->expects($this->at(2))->method('__call')->with('getLastOrderId')->will($this->returnValue(0));

        $this->assertFalse($this->createSuccessValidator($checkoutSession)->isValid($checkoutSession));
    }

    public function testIsValidTrue()
    {
        $checkoutSession = $this->getMockBuilder(
            'Magento\Checkout\Model\Session'
        )->disableOriginalConstructor()->getMock();
        $checkoutSession->expects(
            $this->at(0)
        )->method(
            '__call'
        )->with(
            'getLastSuccessQuoteId'
        )->will(
            $this->returnValue(1)
        );

        $checkoutSession->expects($this->at(1))->method('__call')->with('getLastQuoteId')->will($this->returnValue(1));

        $checkoutSession->expects($this->at(2))->method('__call')->with('getLastOrderId')->will($this->returnValue(1));

        $this->assertTrue($this->createSuccessValidator($checkoutSession)->isValid($checkoutSession));
    }

    protected function createSuccessValidator(\PHPUnit_Framework_MockObject_MockObject $checkoutSession)
    {
        return $this->objectManagerHelper->getObject(
            'Magento\Checkout\Model\Session\SuccessValidator', ['checkoutSession' => $checkoutSession]
        );
    }
}
