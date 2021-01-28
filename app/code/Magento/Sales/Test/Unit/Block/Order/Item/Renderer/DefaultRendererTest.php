<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Block\Order\Item\Renderer;

class DefaultRendererTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Sales\Block\Order\Item\Renderer\DefaultRenderer
     */
    protected $block;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Backend\Block\Template
     */
    protected $priceRenderBlock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\View\Layout
     */
    protected $layoutMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Quote\Model\Quote\Item  */
    protected $itemMock;

    /**
     * Initialize required data
     */
    protected function setUp(): void
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->layoutMock = $this->getMockBuilder(\Magento\Framework\View\Layout::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBlock'])
            ->getMock();

        $this->block = $this->objectManager->getObject(
            \Magento\Sales\Block\Order\Item\Renderer\DefaultRenderer::class,
            [
                'context' => $this->objectManager->getObject(
                    \Magento\Backend\Block\Template\Context::class,
                    ['layout' => $this->layoutMock]
                )
            ]
        );

        $this->priceRenderBlock = $this->getMockBuilder(\Magento\Backend\Block\Template::class)
            ->disableOriginalConstructor()
            ->setMethods(['setItem', 'toHtml'])
            ->getMock();

        $itemMockMethods = [
            '__wakeup',
            'getRowTotal',
            'getTaxAmount',
            'getDiscountAmount',
            'getDiscountTaxCompensationAmount',
            'getWeeeTaxAppliedRowAmount',
        ];
        $this->itemMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Item::class)
            ->disableOriginalConstructor()
            ->setMethods($itemMockMethods)
            ->getMock();
    }

    public function testGetItemPriceHtml()
    {
        $html = '$34.28';

        $this->layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('item_unit_price')
            ->willReturn($this->priceRenderBlock);

        $this->priceRenderBlock->expects($this->once())
            ->method('setItem')
            ->with($this->itemMock);

        $this->priceRenderBlock->expects($this->once())
            ->method('toHtml')
            ->willReturn($html);

        $this->assertEquals($html, $this->block->getItemPriceHtml($this->itemMock));
    }

    public function testGetItemRowTotalHtml()
    {
        $html = '$34.28';

        $this->layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('item_row_total')
            ->willReturn($this->priceRenderBlock);

        $this->priceRenderBlock->expects($this->once())
            ->method('setItem')
            ->with($this->itemMock);

        $this->priceRenderBlock->expects($this->once())
            ->method('toHtml')
            ->willReturn($html);

        $this->assertEquals($html, $this->block->getItemRowTotalHtml($this->itemMock));
    }

    public function testGetItemRowTotalAfterDiscountHtml()
    {
        $html = '$34.28';

        $this->layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('item_row_total_after_discount')
            ->willReturn($this->priceRenderBlock);

        $this->priceRenderBlock->expects($this->once())
            ->method('setItem')
            ->with($this->itemMock);

        $this->priceRenderBlock->expects($this->once())
            ->method('toHtml')
            ->willReturn($html);

        $this->assertEquals($html, $this->block->getItemRowTotalAfterDiscountHtml($this->itemMock));
    }

    public function testGetTotalAmount()
    {
        $rowTotal = 100;
        $taxAmount = 10;
        $discountTaxCompensationAmount = 2;
        $discountAmount = 20;
        $weeeTaxAppliedRowAmount = 10;

        $expectedResult = $rowTotal
            + $taxAmount
            + $discountTaxCompensationAmount
            - $discountAmount
            + $weeeTaxAppliedRowAmount;
        $this->itemMock->expects($this->once())
            ->method('getRowTotal')
            ->willReturn($rowTotal);
        $this->itemMock->expects($this->once())
            ->method('getTaxAmount')
            ->willReturn($taxAmount);
        $this->itemMock->expects($this->once())
            ->method('getDiscountTaxCompensationAmount')
            ->willReturn($discountTaxCompensationAmount);
        $this->itemMock->expects($this->once())
            ->method('getDiscountAmount')
            ->willReturn($discountAmount);
        $this->itemMock->expects($this->once())
            ->method('getWeeeTaxAppliedRowAmount')
            ->willReturn($weeeTaxAppliedRowAmount);

        $this->assertEquals($expectedResult, $this->block->getTotalAmount($this->itemMock));
    }
}
