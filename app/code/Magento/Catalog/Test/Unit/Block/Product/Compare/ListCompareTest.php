<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Block\Product\Compare;

use \Magento\Catalog\Block\Product\Compare\ListCompare;

/**
 * Class ListCompareTest
 */
class ListCompareTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ListCompare
     */
    protected $block;

    /**
     * @var \Magento\Framework\View\LayoutInterface | \PHPUnit\Framework\MockObject\MockObject
     */
    protected $layout;

    protected function setUp(): void
    {
        $this->layout = $this->createPartialMock(\Magento\Framework\View\Layout::class, ['getBlock']);

        $context = $this->createPartialMock(\Magento\Catalog\Block\Product\Context::class, ['getLayout']);
        $context->expects($this->any())
            ->method('getLayout')
            ->willReturn($this->layout);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->block = $objectManager->getObject(
            \Magento\Catalog\Block\Product\Compare\ListCompare::class,
            ['context' => $context]
        );
    }

    protected function tearDown(): void
    {
        $this->block = null;
    }

    public function testGetProductPrice()
    {
        //Data
        $expectedResult = 'html';
        $blockName = 'product.price.render.default';
        $productId = 1;

        //Verification
        $product = $this->createPartialMock(\Magento\Catalog\Model\Product::class, ['getId', '__wakeup']);
        $product->expects($this->once())
            ->method('getId')
            ->willReturn($productId);

        $blockMock = $this->createPartialMock(\Magento\Framework\Pricing\Render::class, ['render']);
        $blockMock->expects($this->once())
            ->method('render')
            ->with(
                'final_price',
                $product,
                [
                    'price_id' => 'product-price-' . $productId . '-compare-list-top',
                    'display_minimal_price' => true,
                    'zone' => \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST
                ]
            )
            ->willReturn($expectedResult);

        $this->layout->expects($this->once())
            ->method('getBlock')
            ->with($blockName)
            ->willReturn($blockMock);

        $this->assertEquals($expectedResult, $this->block->getProductPrice($product, '-compare-list-top'));
    }
}
