<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Test\Unit\Block\Catalog\Product\View\Type\Bundle;

class OptionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option
     */
    protected $block;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $product;

    /**
     * @var \Magento\Framework\View\LayoutInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $layout;

    protected function setUp(): void
    {
        $this->product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPriceInfo', 'hasPreconfiguredValues', 'getPreconfiguredValues', '__wakeup'])
            ->getMock();

        $registry = $this->getMockBuilder(\Magento\Framework\Registry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $registry->expects($this->once())
            ->method('registry')
            ->with('current_product')
            ->willReturn($this->product);

        $this->layout = $this->createMock(\Magento\Framework\View\LayoutInterface::class);

        $context = $this->getMockBuilder(\Magento\Framework\View\Element\Template\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $context->expects($this->atLeastOnce())
            ->method('getLayout')
            ->willReturn($this->layout);

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->block = $objectManagerHelper->getObject(
            \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option::class,
            ['registry' => $registry, 'context' => $context]
        );
    }

    public function testSetOption()
    {
        $selectionId = 315;
        $this->product->expects($this->atLeastOnce())
            ->method('hasPreconfiguredValues')
            ->willReturn(true);
        $this->product->expects($this->atLeastOnce())
            ->method('getPreconfiguredValues')
            ->willReturn(
                new \Magento\Framework\DataObject(['bundle_option' => [15 => 315, 16 => 316]])
            );

        $option = $this->createMock(\Magento\Bundle\Model\Option::class);
        $option->expects($this->any())->method('getId')->willReturn(15);

        $otherOption = $this->getMockBuilder(\Magento\Bundle\Model\Option::class)
            ->disableOriginalConstructor()
            ->getMock();
        $otherOption->expects($this->any())->method('getId')->willReturn(16);

        $selection = $this->createPartialMock(
            \Magento\Catalog\Model\Product::class,
            ['getSelectionId', '__wakeup']
        );
        $otherSelection = $this->createPartialMock(
            \Magento\Catalog\Model\Product::class,
            ['getSelectionId', '__wakeup']
        );
        $otherOption->expects($this->any())->method('getSelectionById')->willReturn($selection);
        $selection->expects($this->atLeastOnce())->method('getSelectionId')->willReturn($selectionId);
        $option->expects($this->once())->method('getSelectionById')->with(315)->willReturn($otherSelection);

        $this->assertSame($this->block, $this->block->setOption($option));
        $this->assertTrue($this->block->isSelected($selection));

        $this->block->setOption($otherOption);
        $this->assertFalse(
            $this->block->isSelected($selection),
            'Selected value should change after new option is set'
        );
    }

    public function testRenderPriceString()
    {
        $includeContainer = false;
        $priceHtml = 'price-html';

        $selection = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $bundlePrice = $this->getMockBuilder(\Magento\Bundle\Pricing\Price\BundleOptionPrice::class)
            ->disableOriginalConstructor()
            ->getMock();

        $priceInfo = $this->createMock(\Magento\Framework\Pricing\PriceInfo\Base::class);
        $amount = $this->createMock(\Magento\Framework\Pricing\Amount\AmountInterface::class);

        $priceRenderBlock = $this->getMockBuilder(\Magento\Framework\Pricing\Render::class)
            ->disableOriginalConstructor()
            ->setMethods(['renderAmount'])
            ->getMock();

        $this->product->expects($this->atLeastOnce())
            ->method('getPriceInfo')
            ->willReturn($priceInfo);

        $priceInfo->expects($this->atLeastOnce())
            ->method('getPrice')
            ->with('bundle_option')
            ->willReturn($bundlePrice);

        $bundlePrice->expects($this->atLeastOnce())
            ->method('getOptionSelectionAmount')
            ->with($selection)
            ->willReturn($amount);

        $this->layout->expects($this->atLeastOnce())
            ->method('getBlock')
            ->with('product.price.render.default')
            ->willReturn($priceRenderBlock);

        $priceRenderBlock->expects($this->atLeastOnce())
            ->method('renderAmount')
            ->with($amount, $bundlePrice, $selection, ['include_container' => $includeContainer])
            ->willReturn($priceHtml);

        $this->assertEquals($priceHtml, $this->block->renderPriceString($selection, $includeContainer));
    }
}
