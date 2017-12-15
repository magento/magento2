<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Bundle\Test\Unit\Block\Catalog\Product\View\Type\Bundle;

class OptionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option
     */
    protected $block;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $product;

    /**
     * @var \Magento\Framework\View\LayoutInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $layout;

    protected function setUp()
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
            ->will($this->returnValue($this->product));

        $this->layout = $this->createMock(\Magento\Framework\View\LayoutInterface::class);

        $context = $this->getMockBuilder(\Magento\Framework\View\Element\Template\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $context->expects($this->atLeastOnce())
            ->method('getLayout')
            ->will($this->returnValue($this->layout));

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
            ->will($this->returnValue(true));
        $this->product->expects($this->atLeastOnce())
            ->method('getPreconfiguredValues')
            ->will($this->returnValue(
                new \Magento\Framework\DataObject(['bundle_option' => [15 => 315, 16 => 316]]))
            );

        $option = $this->createMock(\Magento\Bundle\Model\Option::class);
        $option->expects($this->any())->method('getId')->will($this->returnValue(15));

        $otherOption = $this->getMockBuilder(\Magento\Bundle\Model\Option::class)
            ->disableOriginalConstructor()
            ->getMock();
        $otherOption->expects($this->any())->method('getId')->will($this->returnValue(16));

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
            ->will($this->returnValue($priceInfo));

        $priceInfo->expects($this->atLeastOnce())
            ->method('getPrice')
            ->with('bundle_option')
            ->will($this->returnValue($bundlePrice));

        $bundlePrice->expects($this->atLeastOnce())
            ->method('getOptionSelectionAmount')
            ->with($selection)
            ->will($this->returnValue($amount));

        $this->layout->expects($this->atLeastOnce())
            ->method('getBlock')
            ->with('product.price.render.default')
            ->will($this->returnValue($priceRenderBlock));

        $priceRenderBlock->expects($this->atLeastOnce())
            ->method('renderAmount')
            ->with($amount, $bundlePrice, $selection, ['include_container' => $includeContainer])
            ->will($this->returnValue($priceHtml));

        $this->assertEquals($priceHtml, $this->block->renderPriceString($selection, $includeContainer));
    }
}
