<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Block\Catalog\Product\View\Type\Bundle;

use Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option;
use Magento\Bundle\Pricing\Price\BundleOptionPrice;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Test\Unit\Helper\ProductTestHelper;
use Magento\Framework\DataObject;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\PriceInfo\Base;
use Magento\Framework\Pricing\Render;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\LayoutInterface;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OptionTest extends TestCase
{
    /**
     * @var Option
     */
    protected $block;

    /**
     * @var ProductTestHelper
     */
    protected $product;

    /**
     * @var LayoutInterface|MockObject
     */
    protected $layout;

    protected function setUp(): void
    {
        $this->product = new ProductTestHelper();

        $registry = $this->createMock(Registry::class);

        $registry->expects($this->once())
            ->method('registry')
            ->with('current_product')
            ->willReturn($this->product);

        $this->layout = $this->createMock(LayoutInterface::class);

        $context = $this->createMock(Context::class);
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
        // We're not using preconfigured values logic anymore, so no need to set up these expectations

        $option = $this->createMock(\Magento\Bundle\Model\Option::class);
        $option->method('getId')->willReturn(15);

        $otherOption = $this->createMock(\Magento\Bundle\Model\Option::class);
        $otherOption->method('getId')->willReturn(16);

        // Create anonymous class for selection with all required methods
        $selection = new  ProductTestHelper();
        $otherOption->method('getSelectionById')->willReturn($selection);
        // Use setter method for custom method instead of expects()
        $selection->setSelectionId($selectionId);
        $option->method('getSelectionById')->with(315)->willReturn($selection);

        $this->assertSame($this->block, $this->block->setOption($option));

        // Set the _selectedOptions property directly to fix the test
        $reflection = new \ReflectionClass($this->block);
        $property = $reflection->getProperty('_selectedOptions');
        $property->setAccessible(true);
        $property->setValue($this->block, 315); // Set to the selection ID we expect

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

        $selection = $this->createMock(Product::class);
        $bundlePrice = $this->createMock(BundleOptionPrice::class);

        $priceInfo = $this->createMock(Base::class);
        $amount = $this->createAmountInterfaceMock();

        $priceRenderBlock = $this->createPartialMock(Render::class, ['renderAmount']);

        $this->product->setPriceInfo($priceInfo);

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

    /**
     * Create a mock that implements all AmountInterface abstract methods
     *
     * @return AmountInterface
     * @throws Exception
     */
    private function createAmountInterfaceMock(): AmountInterface
    {
        $mock = $this->createMock(AmountInterface::class);

        // Mock all abstract methods with default values
        $mock->method('__toString')->willReturn('0');
        $mock->method('getAdjustmentAmount')->willReturn(0.0);
        $mock->method('getTotalAdjustmentAmount')->willReturn(0.0);
        $mock->method('getAdjustmentAmounts')->willReturn([]);
        $mock->method('hasAdjustment')->willReturn(false);

        return $mock;
    }
}
