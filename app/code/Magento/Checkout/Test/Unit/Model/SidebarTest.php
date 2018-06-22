<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\Unit\Model;

use Magento\Checkout\Model\Sidebar;

class SidebarTest extends \PHPUnit\Framework\TestCase
{
    /** @var Sidebar */
    protected $sidebar;

    /** @var \Magento\Checkout\Model\Cart|\PHPUnit_Framework_MockObject_MockObject */
    protected $cartMock;

    /** @var \Magento\Checkout\Helper\Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $checkoutHelperMock;

    /** @var \Magento\Framework\Locale\ResolverInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $resolverMock;

    protected function setUp()
    {
        $this->cartMock = $this->createMock(\Magento\Checkout\Model\Cart::class);
        $this->checkoutHelperMock = $this->createMock(\Magento\Checkout\Helper\Data::class);
        $this->resolverMock = $this->createMock(\Magento\Framework\Locale\ResolverInterface::class);

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
    public function dataProviderGetResponseData()
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

        $itemMock = $this->getMockBuilder(\Magento\Quote\Api\Data\CartItemInterface::class)
            ->getMock();

        $quoteMock = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
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

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @exceptedExceptionMessage We can't find the quote item.
     */
    public function testCheckQuoteItemWithException()
    {
        $itemId = 2;

        $quoteMock = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
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
