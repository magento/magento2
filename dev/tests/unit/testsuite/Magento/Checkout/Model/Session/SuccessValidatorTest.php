<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
