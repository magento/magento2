<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\Unit\Block;

use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote;
use Magento\Checkout\Model\Session;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\Escaper;

class CartTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var \Magento\Checkout\Block\Cart
     */
    private $cartBlock;

    /**
     * @var \Magento\Framework\Escaper|\PHPUnit\Framework\MockObject\MockObject
     */
    private $escaper;

    /** @var \Magento\Checkout\Block\Cart|\PHPUnit\Framework\MockObject\MockObject */
    private $context;

    /** @var \Magento\Framework\View\LayoutInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $layoutMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->context = $this->createPartialMock(Context::class, ['getEscaper', 'getLayout']);
        $quoteMock = $this->createMock(Quote::class);
        $checkoutSession = $this->createMock(Session::class);
        $this->layoutMock = $this->getMockForAbstractClass(LayoutInterface::class);
        $this->escaper = $objectManager->getObject(Escaper::class);
        $quoteMock->expects($this->once())->method('getAllVisibleItems')->willReturn([]);
        $checkoutSession->expects($this->any())->method('getQuote')->willReturn($quoteMock);
        $this->context->expects($this->once())->method('getEscaper')->willReturn($this->escaper);
        $this->context->expects($this->once())->method('getLayout')->willReturn($this->layoutMock);

        /** @var $cartBlock CartBlock */
        $this->cartBlock = $objectManager->getObject(
            \Magento\Checkout\Block\Cart::class,
            [
                'context'=> $this->context,
                'checkoutSession'=>$checkoutSession,

            ]
        );
    }

    public function testGetMethodHtmlWithException()
    {
        $this->layoutMock->expects($this->any())->method('getBlock')->willReturn(false);
        $name='blockMethod';
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage(
            (string)__('Invalid method: %1', $name)
        );
        $this->cartBlock->getMethodHtml($name);
    }
}
