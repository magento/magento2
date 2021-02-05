<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Product\View;

use Magento\Catalog\Block\Product\View\Attributes as AttributesBlock;
use Magento\Catalog\Model\Product;
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
     * @var \Magento\Catalog\Block\Product\View\Attributes
     */
    private $attributesBlock;

    protected function setUp(): void
    {
        $this->attribute = $this
            ->getMockBuilder(AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->attribute
            ->expects($this->any())
            ->method('getIsVisibleOnFront')
            ->willReturn(true);
        $this->attribute
            ->expects($this->any())
            ->method('getAttributeCode')
            ->willReturn('phrase');
        $this->frontendAttribute = $this
            ->getMockBuilder(AbstractFrontend::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->attribute
            ->expects($this->any())
            ->method('getFrontendInput')
            ->willReturn('phrase');
        $this->attribute
            ->expects($this->any())
            ->method('getFrontend')
            ->willReturn($this->frontendAttribute);
        $this->product = $this
            ->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->product
            ->expects($this->any())
            ->method('getAttributes')
            ->willReturn([$this->attribute]);
        $this->product
            ->expects($this->any())
            ->method('hasData')
            ->willReturn(true);
        $this->context = $this
            ->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->registry = $this
            ->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->registry
            ->expects($this->any())
            ->method('registry')
            ->willReturn($this->product);
        $this->priceCurrencyInterface = $this
            ->getMockBuilder(PriceCurrencyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->attributesBlock = new AttributesBlock(
            $this->context,
            $this->registry,
            $this->priceCurrencyInterface
        );
    }

    /**
     * @return void
     */
    public function testGetAttributeNoValue()
    {
        $this->phrase = '';
        $this->frontendAttribute
            ->expects($this->any())
            ->method('getValue')
            ->willReturn($this->phrase);
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
            ->expects($this->any())
            ->method('getValue')
            ->willReturn($this->phrase);
        $attributes = $this->attributesBlock->getAdditionalData();
        $this->assertNotEmpty($attributes['phrase']);
        $this->assertNotEmpty($attributes['phrase']['value']);
        $this->assertEquals('Yes', $attributes['phrase']['value']);
    }
}
