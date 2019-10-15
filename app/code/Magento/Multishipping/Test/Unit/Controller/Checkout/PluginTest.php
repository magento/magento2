<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Multishipping\Test\Unit\Controller\Checkout;

use Magento\Multishipping\Controller\Checkout\Plugin;

class PluginTest extends \PHPUnit\Framework\TestCase
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
        $this->cartMock = $this->createMock(\Magento\Checkout\Model\Cart::class);
        $this->quoteMock = $this->createPartialMock(
            \Magento\Quote\Model\Quote::class,
            ['__wakeUp', 'setIsMultiShipping', 'getIsMultiShipping']
        );
        $this->cartMock->expects($this->once())->method('getQuote')->will($this->returnValue($this->quoteMock));
        $this->object = new \Magento\Multishipping\Controller\Checkout\Plugin($this->cartMock);
    }

    public function testExecuteTurnsOffMultishippingModeOnMultishippingQuote(): void
    {
        $subject = $this->createMock(\Magento\Checkout\Controller\Index\Index::class);
        $this->quoteMock->expects($this->once())->method('getIsMultiShipping')->willReturn(1);
        $this->quoteMock->expects($this->once())->method('setIsMultiShipping')->with(0);
        $this->cartMock->expects($this->once())->method('saveQuote');
        $this->object->beforeExecute($subject);
    }

    public function testExecuteTurnsOffMultishippingModeOnNotMultishippingQuote(): void
    {
        $subject = $this->createMock(\Magento\Checkout\Controller\Index\Index::class);
        $this->quoteMock->expects($this->once())->method('getIsMultiShipping')->willReturn(0);
        $this->quoteMock->expects($this->never())->method('setIsMultiShipping');
        $this->cartMock->expects($this->never())->method('saveQuote');
        $this->object->beforeExecute($subject);
    }
}
