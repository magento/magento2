<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Multishipping\Test\Unit\Controller\Checkout;

use Magento\Multishipping\Controller\Checkout\Plugin;

class PluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $cartMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteMock;

    /**
     * @var Plugin
     */
    protected $object;

    protected function setUp()
    {
        $this->cartMock = $this->getMock(\Magento\Checkout\Model\Cart::class, [], [], '', false);
        $this->quoteMock = $this->getMock(
            \Magento\Quote\Model\Quote::class,
            ['__wakeUp', 'setIsMultiShipping'],
            [],
            '',
            false
        );
        $this->cartMock->expects($this->once())->method('getQuote')->will($this->returnValue($this->quoteMock));
        $this->object = new \Magento\Multishipping\Controller\Checkout\Plugin($this->cartMock);
    }

    public function testExecuteTurnsOffMultishippingModeOnQuote()
    {
        $subject = $this->getMock(\Magento\Checkout\Controller\Index\Index::class, [], [], '', false);
        $this->quoteMock->expects($this->once())->method('setIsMultiShipping')->with(0);
        $this->object->beforeExecute($subject);
    }
}
