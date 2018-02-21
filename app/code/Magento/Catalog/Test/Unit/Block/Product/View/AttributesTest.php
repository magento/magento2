<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Block\Product\View;

use \PHPUnit\Framework\TestCase;
use \Magento\Framework\Phrase;
use \Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use \Magento\Eav\Model\Entity\Attribute\Frontend\AbstractFrontend;
use \Magento\Catalog\Model\Product;
use \Magento\Framework\View\Element\Template\Context;
use \Magento\Framework\Registry;
use \Magento\Framework\Pricing\PriceCurrencyInterface;
use \Magento\Catalog\Block\Product\View\Attributes as AttributesBlock;

/**
 * Test class for \Magento\Catalog\Block\Product\View\Attributes
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AttributesTest extends TestCase
{
    /**
     * @var \Magento\Framework\Phrase
     */
    private $phrase;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Eav\Model\Entity\Attribute\AbstractAttribute
     */
    private $attribute;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Eav\Model\Entity\Attribute\Frontend\AbstractFrontend
     */
    private $frontendAttribute;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Product
     */
    private $product;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\View\Element\Template\Context
     */
    private $context;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $priceCurrencyInterface;

    /**
     * @var \Magento\Catalog\Block\Product\View\Attributes
     */
    private $attributesBlock;

    protected function setUp()
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
            ->getMock();
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
        $this->assertTrue(empty($attributes['phrase']));
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
        $this->assertNotTrue(empty($attributes['phrase']));
        $this->assertNotTrue(empty($attributes['phrase']['value']));
        $this->assertEquals('Yes', $attributes['phrase']['value']);
    }
}
