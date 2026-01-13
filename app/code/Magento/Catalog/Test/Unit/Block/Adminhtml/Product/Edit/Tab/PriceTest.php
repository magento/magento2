<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Product\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Element\ElementCreator;
use Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Price;
use Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Price\Tier;
use Magento\Catalog\Model\Product;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Fieldset;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Filesystem\Directory\ReadInterface as DirectoryHelper;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\LayoutInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Unit test for Price tab block.
 *
 * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Price
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class PriceTest extends TestCase
{
    /**
     * @var Price
     */
    private Price $block;

    /**
     * @var FormFactory|MockObject
     */
    private FormFactory|MockObject $formFactory;

    /**
     * @var Registry|MockObject
     */
    private Registry|MockObject $registry;

    /**
     * @var LayoutInterface|MockObject
     */
    private LayoutInterface|MockObject $layout;

    /**
     * @var Product|MockObject
     */
    private Product|MockObject $product;

    /**
     * Set up test dependencies and mocks.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);
        $objectManagerHelper->prepareObjectManager();

        $this->formFactory = $this->createMock(FormFactory::class);
        $this->registry = $this->createMock(Registry::class);
        $this->layout = $this->getMockForAbstractClass(LayoutInterface::class);
        $this->product = $this->createMock(Product::class);

        $context = $this->createMock(Context::class);
        $context->method('getLayout')->willReturn($this->layout);

        $urlBuilder = $this->createMock(UrlInterface::class);
        $urlBuilder->method('getBaseUrl')->willReturn('http://example.com/');
        $context->method('getUrlBuilder')->willReturn($urlBuilder);

        $this->block = new Price($context, $this->registry, $this->formFactory);
    }

    /**
     * Test _prepareForm creates proper fieldset and fields.
     *
     * @return void
     */
    public function testPrepareFormCreatesFieldsetAndFields(): void
    {
        $form = $this->createMock(Form::class);
        $fieldset = $this->createMock(Fieldset::class);
        $defaultPriceField = $this->createMock(AbstractElement::class);
        $tierPriceField = $this->createMock(AbstractElement::class);

        $this->formFactory->method('create')->willReturn($form);

        $this->product->method('getPrice')->willReturn(99.99);
        $this->product->method('getData')->with('tier_price')->willReturn('10=5.00,20=4.00');
        $this->registry->method('registry')->with('product')->willReturn($this->product);

        $form->expects($this->once())
            ->method('addFieldset')
            ->with('tiered_price', ['legend' => 'Tier Pricing'])
            ->willReturn($fieldset);

        $fieldset->expects($this->exactly(2))
            ->method('addField')
            ->willReturnCallback(function ($id, $type, $config) use ($defaultPriceField, $tierPriceField) {
                if ($id === 'default_price') {
                    $this->assertSame('label', $type);
                    $this->assertInstanceOf(Phrase::class, $config['label']);
                    $this->assertSame('Default Price', (string)$config['label']);
                    $this->assertInstanceOf(Phrase::class, $config['title']);
                    $this->assertSame('Default Price', (string)$config['title']);
                    $this->assertSame('default_price', $config['name']);
                    $this->assertTrue($config['bold']);
                    $this->assertSame('99.99', (string)$config['value']);
                    return $defaultPriceField;
                }
                if ($id === 'tier_price') {
                    $this->assertSame('text', $type);
                    $this->assertSame('tier_price', $config['name']);
                    $this->assertSame('requried-entry', $config['class']);
                    $this->assertSame('10=5.00,20=4.00', $config['value']);
                    return $tierPriceField;
                }
                return null;
            });

        $form->method('getElement')
            ->with('tier_price')
            ->willReturn($tierPriceField);

        $tierRenderer = $this->createMock(Tier::class);
        $this->layout->method('createBlock')->with(Tier::class)->willReturn($tierRenderer);
        $tierPriceField->expects($this->once())->method('setRenderer')->with($tierRenderer);

        $reflection = new ReflectionClass(Price::class);
        $method = $reflection->getMethod('_prepareForm');
        $method->setAccessible(true);
        $method->invoke($this->block);

        $this->assertSame($form, $this->block->getForm());
    }

    /**
     * Test _prepareForm handles NULL product from registry.
     *
     * @return void
     */
    public function testPrepareFormWithNullProduct(): void
    {
        $form = $this->createMock(Form::class);
        $fieldset = $this->createMock(Fieldset::class);

        $this->formFactory->method('create')->willReturn($form);
        $this->registry->method('registry')->with('product')->willReturn(null);

        $form->method('addFieldset')->willReturn($fieldset);
        $fieldset->method('addField')->willReturn($this->createMock(AbstractElement::class));

        $this->expectException(\Error::class);
        $this->expectExceptionMessage('Call to a member function getPrice() on null');

        $reflection = new ReflectionClass(Price::class);
        $method = $reflection->getMethod('_prepareForm');
        $method->setAccessible(true);
        $method->invoke($this->block);
    }

    /**
     * Test _prepareForm handles NULL price value.
     *
     * @return void
     */
    public function testPrepareFormWithNullPrice(): void
    {
        $form = $this->createMock(Form::class);
        $fieldset = $this->createMock(Fieldset::class);
        $defaultPriceField = $this->createMock(AbstractElement::class);
        $tierPriceField = $this->createMock(AbstractElement::class);

        $this->formFactory->method('create')->willReturn($form);

        $this->product->method('getPrice')->willReturn(null);
        $this->product->method('getData')->with('tier_price')->willReturn(null);
        $this->registry->method('registry')->with('product')->willReturn($this->product);

        $form->method('addFieldset')->willReturn($fieldset);

        $fieldset->expects($this->exactly(2))
            ->method('addField')
            ->willReturnCallback(function ($id, $_type, $config) use ($defaultPriceField, $tierPriceField) {
                if ($id === 'default_price') {
                    $this->assertNull($config['value']);
                    return $defaultPriceField;
                }
                if ($id === 'tier_price') {
                    $this->assertNull($config['value']);
                    return $tierPriceField;
                }
                return null;
            });

        $form->method('getElement')->with('tier_price')->willReturn($tierPriceField);

        $tierRenderer = $this->createMock(Tier::class);
        $this->layout->method('createBlock')->with(Tier::class)->willReturn($tierRenderer);
        $tierPriceField->method('setRenderer')->with($tierRenderer);

        $reflection = new ReflectionClass(Price::class);
        $method = $reflection->getMethod('_prepareForm');
        $method->setAccessible(true);
        $method->invoke($this->block);

        $this->assertSame($form, $this->block->getForm());
    }

    /**
     * Test _prepareForm handles zero price value.
     *
     * @return void
     */
    public function testPrepareFormWithZeroPrice(): void
    {
        $form = $this->createMock(Form::class);
        $fieldset = $this->createMock(Fieldset::class);
        $defaultPriceField = $this->createMock(AbstractElement::class);
        $tierPriceField = $this->createMock(AbstractElement::class);

        $this->formFactory->method('create')->willReturn($form);

        $this->product->method('getPrice')->willReturn(0.00);
        $this->product->method('getData')->with('tier_price')->willReturn('');
        $this->registry->method('registry')->with('product')->willReturn($this->product);

        $form->method('addFieldset')->willReturn($fieldset);

        $fieldset->expects($this->exactly(2))
            ->method('addField')
            ->willReturnCallback(function ($id, $_type, $config) use ($defaultPriceField, $tierPriceField) {
                if ($id === 'default_price') {
                    $this->assertSame(0.00, $config['value']);
                    return $defaultPriceField;
                }
                if ($id === 'tier_price') {
                    $this->assertSame('', $config['value']);
                    return $tierPriceField;
                }
                return null;
            });

        $form->method('getElement')->with('tier_price')->willReturn($tierPriceField);

        $tierRenderer = $this->createMock(Tier::class);
        $this->layout->method('createBlock')->with(Tier::class)->willReturn($tierRenderer);
        $tierPriceField->method('setRenderer')->with($tierRenderer);

        $reflection = new ReflectionClass(Price::class);
        $method = $reflection->getMethod('_prepareForm');
        $method->setAccessible(true);
        $method->invoke($this->block);

        $this->assertSame($form, $this->block->getForm());
    }
}
