<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Block;

use Magento\Checkout\Block\Cart;
use Magento\Checkout\Model\Session;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\LayoutInterface;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CartTest extends TestCase
{

    /**
     * @var Cart
     */
    private $cartBlock;

    /**
     * @var Escaper|MockObject
     */
    private $escaper;

    /** @var Cart|MockObject */
    private $context;

    /** @var LayoutInterface|MockObject */
    private $layoutMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->context = $this->createPartialMock(Context::class, ['getEscaper', 'getLayout']);
        $quoteMock = $this->createMock(Quote::class);
        $checkoutSession = $this->createMock(Session::class);
        $this->layoutMock = $this->createMock(LayoutInterface::class);
        $this->escaper = $objectManager->getObject(Escaper::class);
        $quoteMock->expects($this->once())->method('getAllVisibleItems')->willReturn([]);
        $checkoutSession->expects($this->any())->method('getQuote')->willReturn($quoteMock);
        $this->context->expects($this->once())->method('getEscaper')->willReturn($this->escaper);
        $this->context->expects($this->once())->method('getLayout')->willReturn($this->layoutMock);

        /** @var $cartBlock Cart */
        $this->cartBlock = $objectManager->getObject(
            Cart::class,
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
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage(
            (string)__('Invalid method: %1', $name)
        );
        $this->cartBlock->getMethodHtml($name);
    }
}
