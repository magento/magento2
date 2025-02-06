<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Model;

use Magento\Checkout\Helper\Data;
use Magento\Checkout\Model\Cart;
use Magento\Checkout\Model\Sidebar;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SidebarTest extends TestCase
{
    /** @var Sidebar */
    protected $sidebar;

    /** @var Cart|MockObject */
    protected $cartMock;

    /** @var Data|MockObject */
    protected $checkoutHelperMock;

    /** @var ResolverInterface|MockObject */
    protected $resolverMock;

    protected function setUp(): void
    {
        $this->cartMock = $this->createMock(Cart::class);
        $this->checkoutHelperMock = $this->createMock(Data::class);
        $this->resolverMock = $this->getMockForAbstractClass(ResolverInterface::class);

        $this->sidebar = new Sidebar(
            $this->cartMock,
            $this->checkoutHelperMock,
            $this->resolverMock
        );
    }

    /**
     * @param string $error
     * @param array $result
     *
     * @dataProvider dataProviderGetResponseData
     */
    public function testGetResponseData($error, $result)
    {
        $this->assertEquals($result, $this->sidebar->getResponseData($error));
    }

    /**
     * @return array
     */
    public static function dataProviderGetResponseData()
    {
        return [
            [
                '',
                ['success' => true],
            ],
            [
                '',
                ['success' => true],
            ],
            [
                '',
                ['success' => true],
            ],
            [
                'Error',
                [
                    'success' => false,
                    'error_message' => 'Error',
                ],
            ],
        ];
    }

    public function testCheckQuoteItem()
    {
        $itemId = 1;

        $itemMock = $this->getMockBuilder(CartItemInterface::class)
            ->getMock();

        $quoteMock = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $quoteMock->expects($this->once())
            ->method('getItemById')
            ->with($itemId)
            ->willReturn($itemMock);

        $this->cartMock->expects($this->any())
            ->method('getQuote')
            ->willReturn($quoteMock);

        $this->assertEquals($this->sidebar, $this->sidebar->checkQuoteItem($itemId));
    }

    public function testCheckQuoteItemWithException()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('The quote item isn\'t found. Verify the item and try again.');
        $itemId = 2;

        $quoteMock = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $quoteMock->expects($this->once())
            ->method('getItemById')
            ->with($itemId)
            ->willReturn(null);

        $this->cartMock->expects($this->any())
            ->method('getQuote')
            ->willReturn($quoteMock);

        $this->sidebar->checkQuoteItem($itemId);
    }

    public function testRemoveQuoteItem()
    {
        $itemId = 1;

        $this->cartMock->expects($this->once())
            ->method('removeItem')
            ->with($itemId)
            ->willReturnSelf();
        $this->cartMock->expects($this->once())
            ->method('save')
            ->willReturnSelf();

        $this->assertEquals($this->sidebar, $this->sidebar->removeQuoteItem($itemId));
    }

    public function testUpdateQuoteItem()
    {
        $itemId = 1;
        $itemQty = 2;

        $this->resolverMock->expects($this->once())
            ->method('getLocale')
            ->willReturn('en');

        $this->cartMock->expects($this->once())
            ->method('updateItems')
            ->with([$itemId => ['qty' => $itemQty]])
            ->willReturnSelf();
        $this->cartMock->expects($this->once())
            ->method('save')
            ->willReturnSelf();

        $this->assertEquals($this->sidebar, $this->sidebar->updateQuoteItem($itemId, $itemQty));
    }

    public function testUpdateQuoteItemWithZeroQty()
    {
        $itemId = 1;
        $itemQty = 0;

        $this->resolverMock->expects($this->never())
            ->method('getLocale');

        $this->cartMock->expects($this->once())
            ->method('updateItems')
            ->with([$itemId => ['qty' => $itemQty]])
            ->willReturnSelf();
        $this->cartMock->expects($this->once())
            ->method('save')
            ->willReturnSelf();

        $this->assertEquals($this->sidebar, $this->sidebar->updateQuoteItem($itemId, $itemQty));
    }
}
