<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Block\Order\Item\Renderer;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Layout;
use Magento\Quote\Model\Quote\Item;
use Magento\Sales\Block\Order\Item\Renderer\DefaultRenderer;
use Magento\Sales\Model\Order\Item as OrderItem;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DefaultRendererTest extends TestCase
{
    /**
     * @var MockObject|DefaultRenderer
     */
    protected $block;

    /**
     * @var MockObject|Template
     */
    protected $priceRenderBlock;

    /**
     * @var MockObject|Layout
     */
    protected $layoutMock;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /** @var MockObject|Item  */
    protected $itemMock;

    /**
     * Initialize required data
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->layoutMock = $this->getMockBuilder(Layout::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getBlock'])
            ->getMock();

        $this->block = $this->objectManager->getObject(
            DefaultRenderer::class,
            [
                'context' => $this->objectManager->getObject(
                    Context::class,
                    ['layout' => $this->layoutMock]
                )
            ]
        );

        $this->priceRenderBlock = $this->getMockBuilder(Template::class)
            ->disableOriginalConstructor()
            ->addMethods(['setItem'])
            ->onlyMethods(['toHtml'])
            ->getMock();

        $itemMockMethods = [
            'getRowTotal',
            'getTaxAmount',
            'getDiscountAmount',
            'getDiscountTaxCompensationAmount',
            'getWeeeTaxAppliedRowAmount',
        ];
        $this->itemMock = $this->getMockBuilder(OrderItem::class)
            ->disableOriginalConstructor()
            ->onlyMethods($itemMockMethods)
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
