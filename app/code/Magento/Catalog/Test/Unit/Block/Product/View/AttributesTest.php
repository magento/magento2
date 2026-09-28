<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Product\View;

use Magento\Catalog\Block\Product\View\Attributes as AttributesBlock;
use Magento\Catalog\Model\Product;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Attribute\Frontend\AbstractFrontend;
use Magento\Framework\Phrase;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Catalog\Block\Product\View\Attributes
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AttributesTest extends TestCase
{
    /**
     * @var Phrase
     */
    private $phrase;

    /**
     * @var MockObject|AbstractAttribute
     */
    private $attribute;

    /**
     * @var MockObject|AbstractFrontend
     */
    private $frontendAttribute;

    /**
     * @var MockObject|Product
     */
    private $product;

    /**
     * @var MockObject|Context
     */
    private $context;

    /**
     * @var MockObject|Registry
     */
    private $registry;

    /**
     * @var MockObject|PriceCurrencyInterface
     */
    private $priceCurrencyInterface;

    /**
     * @var MockObject|DirectoryHelper
     */
    private $directoryHelper;

    /**
     * @var \Magento\Catalog\Block\Product\View\Attributes
     */
    private $attributesBlock;

    protected function setUp(): void
    {
        $this->attribute = $this->createMock(AbstractAttribute::class);
        $this->attribute
            ->method('getIsVisibleOnFront')->willReturn(true);
        $this->attribute
            ->method('getAttributeCode')->willReturn('phrase');
        $this->frontendAttribute = $this->createMock(AbstractFrontend::class);
        $this->attribute
            ->method('getFrontendInput')->willReturn('phrase');
        $this->attribute
            ->method('getFrontend')->willReturn($this->frontendAttribute);
        $this->product = $this->createMock(Product::class);
        $this->product
            ->method('getAttributes')->willReturn([$this->attribute]);
        $this->product
            ->method('hasData')->willReturn(true);
        $this->context = $this->createMock(Context::class);
        $this->registry = $this->createMock(Registry::class);
        $this->registry
            ->method('registry')->willReturn($this->product);
        $this->priceCurrencyInterface = $this->createMock(PriceCurrencyInterface::class);
        $this->directoryHelper = $this->createMock(DirectoryHelper::class);
        $this->attributesBlock = new AttributesBlock(
            $this->context,
            $this->registry,
            $this->priceCurrencyInterface,
            [],
            $this->directoryHelper
        );
    }

    /**
     * @return void
     */
    public function testGetAttributeNoValue()
    {
        $this->phrase = '';
        $this->frontendAttribute
            ->method('getValue')->willReturn($this->phrase);
        $attributes = $this->attributesBlock->getAdditionalData();
        $this->assertEmpty($attributes);
    }

    /**
     * @return void
     */
    public function testGetAttributeHasValue()
    {
        $this->phrase = __('Yes');
        $this->frontendAttribute
            ->method('getValue')->willReturn($this->phrase);
        $attributes = $this->attributesBlock->getAdditionalData();
        $this->assertNotEmpty($attributes['phrase']);
        $this->assertNotEmpty($attributes['phrase']['value']);
        $this->assertEquals('Yes', $attributes['phrase']['value']);
    }

    /**
     * @return void
     */
    public function testGetWeightAttributeAppendsUnit()
    {
        $weightAttribute = $this->createMock(AbstractAttribute::class);
        $weightAttribute->method('getIsVisibleOnFront')->willReturn(true);
        $weightAttribute->method('getAttributeCode')->willReturn('weight');
        $weightAttribute->method('getFrontendInput')->willReturn('text');
        $weightFrontend = $this->createMock(AbstractFrontend::class);
        $weightFrontend->method('getValue')->willReturn('10.000000');
        $weightAttribute->method('getFrontend')->willReturn($weightFrontend);

        $product = $this->createMock(Product::class);
        $product->method('getAttributes')->willReturn([$weightAttribute]);
        $product->method('hasData')->willReturn(true);

        $registry = $this->createMock(Registry::class);
        $registry->method('registry')->willReturn($product);

        $directoryHelper = $this->createMock(DirectoryHelper::class);
        $directoryHelper->method('getWeightUnit')->willReturn('lbs');

        $block = new AttributesBlock(
            $this->context,
            $registry,
            $this->priceCurrencyInterface,
            [],
            $directoryHelper
        );

        $attributes = $block->getAdditionalData();
        $this->assertNotEmpty($attributes['weight']);
        $this->assertEquals('10.000000 lbs', $attributes['weight']['value']);
    }
}
