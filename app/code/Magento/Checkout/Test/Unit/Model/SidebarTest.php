<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\Unit\Model;

use Magento\Checkout\Model\Sidebar;

class SidebarTest extends \PHPUnit_Framework_TestCase
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
        $this->cartMock = $this->getMock('Magento\Checkout\Model\Cart', [], [], '', false);
        $this->checkoutHelperMock = $this->getMock('Magento\Checkout\Helper\Data', [], [], '', false);
        $this->resolverMock = $this->getMock('Magento\Framework\Locale\ResolverInterface');

        $this->sidebar = new Sidebar(
            $this->cartMock,
            $this->checkoutHelperMock,
            $this->resolverMock
        );
    }

    /**
     * @param string $error
     * @param float $summaryQty
     * @param array $totals
     * @param array $result
     *
     * @dataProvider dataProviderGetResponseData
     */
    public function testGetResponseData($error, $summaryQty, $totals, $result)
    {
        $quoteMock = $this->getMockBuilder('Magento\Quote\Model\Quote')
            ->disableOriginalConstructor()
            ->getMock();
        $quoteMock->expects($this->any())
            ->method('getTotals')
            ->willReturn($totals);

        $this->cartMock->expects($this->any())
            ->method('getSummaryQty')
            ->willReturn($summaryQty);
        $this->cartMock->expects($this->any())
            ->method('getQuote')
            ->willReturn($quoteMock);

        $this->checkoutHelperMock->expects($this->any())
            ->method('formatPrice')
            ->willReturnArgument(0);

        $this->assertEquals($result, $this->sidebar->getResponseData($error));
    }

    public function dataProviderGetResponseData()
    {
        $totalMock = $this->getMockBuilder('Magento\Quote\Model\Quote\Address\Total')
            ->disableOriginalConstructor()
            ->setMethods(['getValue'])
            ->getMock();
        $totalMock->expects($this->any())
            ->method('getValue')
            ->willReturn(12.34);

        return [
            [
                '',
                0,
                [],
                [
                    'success' => true,
                    'data' => [
                        'summary_qty' => 0,
                        'summary_text' => __(' items'),
                        'subtotal' => 0,
                    ],
                    'cleanup' => true,
                ],
            ],
            [
                '',
                1,
                [
                    'subtotal' => $this->getMock('NonexistentClass'),
                ],
                [
                    'success' => true,
                    'data' => [
                        'summary_qty' => 1,
                        'summary_text' => __(' item'),
                        'subtotal' => 0,
                    ],
                ],
            ],
            [
                '',
                2,
                [
                    'subtotal' => $totalMock,
                ],
                [
                    'success' => true,
                    'data' => [
                        'summary_qty' => 2,
                        'summary_text' => __(' items'),
                        'subtotal' => 12.34,
                    ],
                ],
            ],
            [
                'Error',
                0,
                [],
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

        $itemMock = $this->getMockBuilder('Magento\Quote\Api\Data\CartItemInterface')
            ->getMock();

        $quoteMock = $this->getMockBuilder('Magento\Quote\Model\Quote')
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

        $quoteMock = $this->getMockBuilder('Magento\Quote\Model\Quote')
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
