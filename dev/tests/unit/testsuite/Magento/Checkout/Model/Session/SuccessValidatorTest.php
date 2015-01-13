<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Model\Session;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class SuccessValidatorTest extends \PHPUnit_Framework_TestCase
{
    /** @var SuccessValidator|\PHPUnit_Framework_MockObject_MockObject */
    protected $object;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->object = $this->objectManagerHelper->getObject('Magento\Checkout\Model\Session\SuccessValidator');
    }

    public function testIsValid()
    {
        $checkoutSession = $this->getMockBuilder(
            '\Magento\Checkout\Model\Session'
        )->disableOriginalConstructor()->getMock();
        $this->assertFalse($this->object->isValid($checkoutSession));
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

        $this->assertFalse($this->object->isValid($checkoutSession));
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

        $this->assertFalse($this->object->isValid($checkoutSession));
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

        $this->assertTrue($this->object->isValid($checkoutSession));
    }
}
