<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Product\Edit;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Element\ElementCreator;
use Magento\Catalog\Block\Adminhtml\Product\Edit\AttributeSet;
use Magento\Catalog\Model\Product;
use Magento\Framework\Escaper;
use Magento\Framework\Filesystem\Directory\ReadInterface as DirectoryHelper;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for AttributeSet block.
 *
 * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\AttributeSet
 */
class AttributeSetTest extends TestCase
{
    /**
     * @var AttributeSet
     */
    private AttributeSet $block;

    /**
     * @var Registry|MockObject
     */
    private Registry|MockObject $registry;

    /**
     * @var Product|MockObject
     */
    private Product|MockObject $product;

    /**
     * @var UrlInterface|MockObject
     */
    private UrlInterface|MockObject $urlBuilder;

    /**
     * @var Escaper|MockObject
     */
    private Escaper|MockObject $escaper;

    /**
     * @var JsonHelper|MockObject
     */
    private JsonHelper|MockObject $jsonHelper;

    /**
     * Set up test dependencies and mocks.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);
        $objectManagerHelper->prepareObjectManager();

        $this->registry = $this->createMock(Registry::class);
        $this->product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAttributeSetId'])
            ->getMock();
        $this->urlBuilder = $this->getMockForAbstractClass(UrlInterface::class);
        $this->escaper = $this->createMock(Escaper::class);
        $this->jsonHelper = $this->createMock(JsonHelper::class);

        $context = $this->createMock(Context::class);
        $context->method('getUrlBuilder')->willReturn($this->urlBuilder);
        $context->method('getEscaper')->willReturn($this->escaper);

        $this->block = new AttributeSet(
            $context,
            $this->registry,
            ['jsonHelper' => $this->jsonHelper]
        );

        $this->registry->method('registry')->with('product')->willReturn($this->product);
    }

    /**
     * Test constructor accepts JsonHelper as fourth parameter.
     *
     * @return void
     */
    public function testConstructorWithJsonHelper(): void
    {
        $context = $this->createMock(Context::class);
        $registry = $this->createMock(Registry::class);
        $jsonHelper = $this->createMock(JsonHelper::class);

        $block = new AttributeSet($context, $registry, [], $jsonHelper);

        $this->assertInstanceOf(AttributeSet::class, $block);
    }

    /**
     * Test constructor creates JsonHelper via ObjectManager when not provided (null).
     *
     * @return void
     */
    public function testConstructorWithoutJsonHelperUsesObjectManager(): void
    {
        $objectManagerHelper = new ObjectManager($this);
        $objectManagerHelper->prepareObjectManager();

        $context = $this->createMock(Context::class);
        $registry = $this->createMock(Registry::class);

        $block = new AttributeSet($context, $registry, [], null);

        $this->assertInstanceOf(AttributeSet::class, $block);
    }

    /**
     * Test getSelectorOptions returns correct structure and values.
     *
     * @return void
     */
    public function testGetSelectorOptions(): void
    {
        $this->product->method('getAttributeSetId')->willReturn(123);

        $this->urlBuilder->method('getUrl')
            ->with('catalog/product/suggestAttributeSets')
            ->willReturn('http://example.com/admin/catalog/product/suggestAttributeSets');

        $this->escaper->method('escapeUrl')
            ->willReturnCallback(function ($url) {
                return 'escaped_' . $url;
            });
        $this->escaper->method('escapeHtml')
            ->willReturnCallback(function ($value) {
                return 'escaped_' . $value;
            });

        $options = $this->block->getSelectorOptions();

        $this->assertIsArray($options);
        $this->assertArrayHasKey('source', $options);
        $this->assertArrayHasKey('className', $options);
        $this->assertArrayHasKey('showRecent', $options);
        $this->assertArrayHasKey('storageKey', $options);
        $this->assertArrayHasKey('minLength', $options);
        $this->assertArrayHasKey('currentlySelected', $options);

        $this->assertStringContainsString('escaped_', $options['source']);
        $this->assertStringContainsString('catalog/product/suggestAttributeSets', $options['source']);
        $this->assertSame('category-select', $options['className']);
        $this->assertTrue($options['showRecent']);
        $this->assertSame('product-template-key', $options['storageKey']);
        $this->assertSame(0, $options['minLength']);
        $this->assertSame('escaped_123', $options['currentlySelected']);
    }

    /**
     * Test getSelectorOptions uses correct attribute set ID.
     *
     * @return void
     */
    public function testGetSelectorOptionsWithDifferentAttributeSetId(): void
    {
        $this->product->method('getAttributeSetId')->willReturn(456);

        $this->urlBuilder->method('getUrl')->willReturn('http://example.com/admin/url');
        $this->escaper->method('escapeUrl')->willReturnArgument(0);
        $this->escaper->method('escapeHtml')->willReturnArgument(0);

        $options = $this->block->getSelectorOptions();

        $this->assertSame(456, $options['currentlySelected']);
    }

    /**
     * Test getSelectorOptions properly escapes URL to prevent XSS.
     *
     * @return void
     */
    public function testGetSelectorOptionsEscapesUrl(): void
    {
        $this->product->method('getAttributeSetId')->willReturn(1);

        $rawUrl = 'http://example.com/admin/catalog?test=1&special=<div onclick="alert(1)">';
        $escapedUrl = 'http://example.com/admin/catalog?test=1&amp;special='
            . '&lt;div onclick=&quot;alert(1)&quot;&gt;';

        $this->urlBuilder->method('getUrl')->willReturn($rawUrl);
        $this->escaper->method('escapeUrl')->with($rawUrl)->willReturn($escapedUrl);
        $this->escaper->method('escapeHtml')->willReturnArgument(0);

        $options = $this->block->getSelectorOptions();

        $this->assertSame($escapedUrl, $options['source']);
    }

    /**
     * Test getSelectorOptions properly escapes attribute set ID to prevent XSS.
     *
     * @return void
     */
    public function testGetSelectorOptionsEscapesAttributeSetId(): void
    {
        $attributeSetId = '<img src="x" onerror="alert(\'xss\')">';
        $escapedAttributeSetId = '&lt;img src=&quot;x&quot; onerror=&quot;alert(&#039;xss&#039;)&quot;&gt;';

        $this->product->method('getAttributeSetId')->willReturn($attributeSetId);
        $this->urlBuilder->method('getUrl')->willReturn('http://example.com/admin/url');
        $this->escaper->method('escapeUrl')->willReturnArgument(0);
        $this->escaper->method('escapeHtml')->with($attributeSetId)->willReturn($escapedAttributeSetId);

        $options = $this->block->getSelectorOptions();

        $this->assertSame($escapedAttributeSetId, $options['currentlySelected']);
    }

    /**
     * Test getSelectorOptions returns complete structure with correct types.
     *
     * @return void
     */
    public function testGetSelectorOptionsStructure(): void
    {
        $this->product->method('getAttributeSetId')->willReturn(10);
        $this->urlBuilder->method('getUrl')->willReturn('http://test.com');
        $this->escaper->method('escapeUrl')->willReturnArgument(0);
        $this->escaper->method('escapeHtml')->willReturnArgument(0);

        $options = $this->block->getSelectorOptions();

        $expectedKeys = ['source', 'className', 'showRecent', 'storageKey', 'minLength', 'currentlySelected'];
        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $options, "Key '$key' should exist in selector options");
        }

        $this->assertIsString($options['source']);
        $this->assertIsString($options['className']);
        $this->assertIsBool($options['showRecent']);
        $this->assertIsString($options['storageKey']);
        $this->assertIsInt($options['minLength']);
    }

    /**
     * Test getSelectorOptions throws Error when product is null.
     *
     * @return void
     */
    public function testGetSelectorOptionsThrowsErrorWhenProductNull(): void
    {
        $registry = $this->createMock(Registry::class);
        $registry->method('registry')->with('product')->willReturn(null);

        $context = $this->createMock(Context::class);
        $context->method('getUrlBuilder')->willReturn($this->urlBuilder);
        $context->method('getEscaper')->willReturn($this->escaper);

        $block = new AttributeSet($context, $registry, ['jsonHelper' => $this->jsonHelper]);

        $this->urlBuilder->method('getUrl')->willReturn('http://test.com');
        $this->escaper->method('escapeUrl')->willReturnArgument(0);

        $this->expectException(\Error::class);
        $this->expectExceptionMessage('Call to a member function getAttributeSetId() on null');
        $block->getSelectorOptions();
    }

    /**
     * Test getSelectorOptions handles NULL attribute set ID.
     *
     * @return void
     */
    public function testGetSelectorOptionsWithNullAttributeSetId(): void
    {
        $this->product->method('getAttributeSetId')->willReturn(null);
        $this->urlBuilder->method('getUrl')->willReturn('http://test.com');
        $this->escaper->method('escapeUrl')->willReturnArgument(0);
        $this->escaper->method('escapeHtml')->willReturnCallback(function ($value) {
            return $value === null ? '' : (string)$value;
        });

        $options = $this->block->getSelectorOptions();

        $this->assertArrayHasKey('currentlySelected', $options);
        $this->assertSame('', $options['currentlySelected']);
    }

    /**
     * Test getSelectorOptions handles zero attribute set ID.
     *
     * @return void
     */
    public function testGetSelectorOptionsWithZeroAttributeSetId(): void
    {
        $this->product->method('getAttributeSetId')->willReturn(0);
        $this->urlBuilder->method('getUrl')->willReturn('http://test.com');
        $this->escaper->method('escapeUrl')->willReturnArgument(0);
        $this->escaper->method('escapeHtml')->willReturnCallback(function ($value) {
            return (string)$value;
        });

        $options = $this->block->getSelectorOptions();

        $this->assertArrayHasKey('currentlySelected', $options);
        $this->assertSame('0', $options['currentlySelected']);
    }

    /**
     * Test getSelectorOptions XSS escaping verifies htmlspecialchars behavior.
     *
     * @return void
     */
    public function testGetSelectorOptionsXssEscapingWithHtmlspecialchars(): void
    {
        $maliciousInput = '<img src="x" onerror="alert(\'xss\')">';

        $this->product->method('getAttributeSetId')->willReturn($maliciousInput);
        $this->urlBuilder->method('getUrl')->willReturn('http://test.com');
        $this->escaper->method('escapeUrl')->willReturnArgument(0);
        $this->escaper->method('escapeHtml')->willReturnCallback(function ($value) {
            return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
        });

        $options = $this->block->getSelectorOptions();

        $escapedValue = $options['currentlySelected'];

        $this->assertStringNotContainsString('<img', $escapedValue);
        $this->assertStringNotContainsString('>', $escapedValue);
        $this->assertStringContainsString('&lt;', $escapedValue);
        $this->assertStringContainsString('&gt;', $escapedValue);
        $this->assertSame(
            htmlspecialchars($maliciousInput, ENT_QUOTES, 'UTF-8'),
            $escapedValue
        );
    }
}
