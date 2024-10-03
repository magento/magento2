<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Block\Catalog\Product\View\Type\Bundle;

use Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option;
use Magento\Bundle\Pricing\Price\BundleOptionPrice;
use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\PriceInfo\Base;
use Magento\Framework\Pricing\Render;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\LayoutInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class OptionTest extends TestCase
{
    /**
     * @var Option
     */
    protected $block;

    /**
     * @var Product|MockObject
     */
    protected $product;

    /**
     * @var LayoutInterface|MockObject
     */
    protected $layout;

    protected function setUp(): void
    {
        $this->product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->addMethods(['hasPreconfiguredValues'])
            ->onlyMethods(['getPriceInfo', 'getPreconfiguredValues', '__wakeup'])
            ->getMock();

        $registry = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $registry->expects($this->once())
            ->method('registry')
            ->with('current_product')
            ->willReturn($this->product);

        $this->layout = $this->getMockForAbstractClass(LayoutInterface::class);

        $context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $context->expects($this->atLeastOnce())
            ->method('getLayout')
            ->willReturn($this->layout);

        $objectManagerHelper = new ObjectManager($this);
        $this->block = $objectManagerHelper->getObject(
            Option::class,
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
                new DataObject(['bundle_option' => [15 => 315, 16 => 316]])
            );

        $option = $this->createMock(\Magento\Bundle\Model\Option::class);
        $option->expects($this->any())->method('getId')->willReturn(15);

        $otherOption = $this->getMockBuilder(\Magento\Bundle\Model\Option::class)
            ->disableOriginalConstructor()
            ->getMock();
        $otherOption->expects($this->any())->method('getId')->willReturn(16);

        $selection = $this->getMockBuilder(Product::class)
            ->addMethods(['getSelectionId'])
            ->onlyMethods(['__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $otherSelection = $this->getMockBuilder(Product::class)
            ->addMethods(['getSelectionId'])
            ->onlyMethods(['__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
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

        $selection = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $bundlePrice = $this->getMockBuilder(BundleOptionPrice::class)
            ->disableOriginalConstructor()
            ->getMock();

        $priceInfo = $this->createMock(Base::class);
        $amount = $this->getMockForAbstractClass(AmountInterface::class);

        $priceRenderBlock = $this->getMockBuilder(Render::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['renderAmount'])
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
