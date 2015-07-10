<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Unit\CustomerData;

class CartTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Checkout\CustomerData\Cart
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $checkoutSessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $catalogUrlMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $checkoutCartMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $checkoutHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $itemPoolInterfaceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $layoutMock;

    public function setUp()
    {
        $this->checkoutSessionMock = $this->getMock('\Magento\Checkout\Model\Session', [], [], '', false);
        $this->catalogUrlMock = $this->getMock('\Magento\Catalog\Model\Resource\Url', [], [], '', false);
        $this->checkoutCartMock = $this->getMock('\Magento\Checkout\Model\Cart', [], [], '', false);
        $this->checkoutHelperMock = $this->getMock('\Magento\Checkout\Helper\Data', [], [], '', false);
        $this->layoutMock = $this->getMock('\Magento\Framework\View\LayoutInterface', [], [], '', false);
        $this->itemPoolInterfaceMock = $this->getMock(
            '\Magento\Checkout\CustomerData\ItemPoolInterface',
            [],
            [],
            '',
            false
        );

        $this->model = new \Magento\Checkout\CustomerData\Cart(
            $this->checkoutSessionMock,
            $this->catalogUrlMock,
            $this->checkoutCartMock,
            $this->checkoutHelperMock,
            $this->itemPoolInterfaceMock,
            $this->layoutMock
        );
    }

    public function testIsGuestCheckoutAllowed()
    {
        $quoteMock = $this->getMock('\Magento\Quote\Model\Quote', [], [], '', false);
        $this->checkoutSessionMock->expects($this->once())->method('getQuote')->willReturn($quoteMock);
        $this->checkoutHelperMock->expects($this->once())->method('isAllowedGuestCheckout')->with($quoteMock)
            ->willReturn(true);

        $this->assertTrue($this->model->isGuestCheckoutAllowed());
    }
}
