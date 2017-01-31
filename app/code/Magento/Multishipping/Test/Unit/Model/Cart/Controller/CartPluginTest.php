<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Multishipping\Test\Unit\Model\Cart\Controller;

class CartPluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Multishipping\Model\Cart\Controller\CartPlugin
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $cartRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $checkoutSessionMock;

    protected function setUp()
    {
        $this->cartRepositoryMock = $this->getMock('\Magento\Quote\Api\CartRepositoryInterface');
        $this->checkoutSessionMock = $this->getMock('\Magento\Checkout\Model\Session', [], [], '', false);
        $this->model = new \Magento\Multishipping\Model\Cart\Controller\CartPlugin(
            $this->cartRepositoryMock,
            $this->checkoutSessionMock
        );
    }

    public function testBeforeDispatch()
    {
        $addressId = 100;
        $quoteMock = $this->getMock(
            '\Magento\Quote\Model\Quote',
            [
                'isMultipleShippingAddresses',
                'getAllShippingAddresses',
                'removeAddress',
                'getShippingAddress',
                'setIsMultiShipping',
                'collectTotals'
            ],
            [],
            '',
            false
        );
        $this->checkoutSessionMock->expects($this->once())->method('getQuote')->willReturn($quoteMock);

        $addressMock = $this->getMock('\Magento\Quote\Model\Quote\Address', [], [], '', false);
        $addressMock->expects($this->once())->method('getId')->willReturn($addressId);

        $quoteMock->expects($this->once())->method('isMultipleShippingAddresses')->willReturn(true);
        $quoteMock->expects($this->once())->method('getAllShippingAddresses')->willReturn([$addressMock]);
        $quoteMock->expects($this->once())->method('removeAddress')->with($addressId)->willReturnSelf();
        $quoteMock->expects($this->once())->method('getShippingAddress');
        $quoteMock->expects($this->once())->method('setIsMultiShipping')->with(false)->willReturnSelf();
        $quoteMock->expects($this->once())->method('collectTotals')->willReturnSelf();

        $this->cartRepositoryMock->expects($this->once())->method('save')->with($quoteMock);

        $this->model->beforeDispatch(
            $this->getMock('\Magento\Checkout\Controller\Cart', [], [], '', false),
            $this->getMock('\Magento\Framework\App\RequestInterface')
        );
    }
}
