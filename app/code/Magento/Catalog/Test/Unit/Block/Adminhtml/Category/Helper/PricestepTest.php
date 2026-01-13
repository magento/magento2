<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Category\Helper;

use Magento\Catalog\Block\Adminhtml\Category\Helper\Pricestep;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Magento\Framework\Math\Random;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Pricestep helper
 *
 * @covers \Magento\Catalog\Block\Adminhtml\Category\Helper\Pricestep
 */
class PricestepTest extends TestCase
{
    /**
     * @var Pricestep
     */
    private Pricestep $model;

    /**
     * @var Factory|MockObject
     */
    private $factoryElementMock;

    /**
     * @var CollectionFactory|MockObject
     */
    private $factoryCollectionMock;

    /**
     * @var Escaper|MockObject
     */
    private $escaperMock;

    /**
     * @var SecureHtmlRenderer|MockObject
     */
    private $secureRendererMock;

    /**
     * @var Random|MockObject
     */
    private $randomMock;

    /**
     * @var Form|MockObject
     */
    private $formMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->factoryElementMock = $this->createMock(Factory::class);
        $this->factoryCollectionMock = $this->createMock(CollectionFactory::class);
        $this->escaperMock = $this->createMock(Escaper::class);
        $this->escaperMock->method('escapeHtml')->willReturnArgument(0);
        
        $this->secureRendererMock = $this->createMock(SecureHtmlRenderer::class);
        $this->secureRendererMock->method('renderTag')
            ->willReturnCallback(
                function (string $tag, array $attributes, ?string $content): string {
                    $attributes = new DataObject($attributes);
                    return "<$tag {$attributes->serialize()}>$content</$tag>";
                }
            );
        $this->secureRendererMock->method('renderEventListenerAsTag')
            ->willReturnCallback(
                function (string $event, string $listener, string $selector): string {
                    return "<script type=\"text/x-magento-template\">"
                        . "document.querySelector('{$selector}').{$event} = () => { {$listener} };"
                        . "</script>";
                }
            );

        $this->randomMock = $this->createMock(Random::class);
        $this->randomMock->method('getRandomString')->willReturn('test123456');

        $this->formMock = $this->getMockBuilder(Form::class)
            ->disableOriginalConstructor()
            ->addMethods(['getHtmlIdPrefix', 'getFieldNameSuffix', 'getHtmlIdSuffix'])
            ->onlyMethods(['addSuffixToName'])
            ->getMock();

        $this->formMock->method('getHtmlIdPrefix')->willReturn('');
        $this->formMock->method('getFieldNameSuffix')->willReturn('');
        $this->formMock->method('getHtmlIdSuffix')->willReturn('');
        $this->formMock->method('addSuffixToName')->willReturnArgument(0);

        // Using getMockBuilder to avoid parent constructor ObjectManager::getInstance() calls
        $this->model = $this->getMockBuilder(Pricestep::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        // Inject dependencies using reflection to avoid parent constructor issues
        // Access AbstractElement class directly
        $abstractElementReflection = new \ReflectionClass(\Magento\Framework\Data\Form\Element\AbstractElement::class);
        
        // Inject escaper (from AbstractElement)
        $escaperProperty = $abstractElementReflection->getProperty('_escaper');
        $escaperProperty->setAccessible(true);
        $escaperProperty->setValue($this->model, $this->escaperMock);
        
        // Inject random (from AbstractElement)
        $randomProperty = $abstractElementReflection->getProperty('random');
        $randomProperty->setAccessible(true);
        $randomProperty->setValue($this->model, $this->randomMock);
        
        // Inject secureRenderer (from Pricestep) - use the real class for reflection
        $pricestepReflection = new \ReflectionClass(Pricestep::class);
        $secureRendererProperty = $pricestepReflection->getProperty('secureRenderer');
        $secureRendererProperty->setAccessible(true);
        $secureRendererProperty->setValue($this->model, $this->secureRendererMock);

        $this->model->setForm($this->formMock);
    }

    /**
     * Test getToggleCode method returns correct JavaScript code
     *
     * @dataProvider toggleCodeDataProvider
     * @param string $htmlId
     * @param string $expectedId
     * @return void
     */
    public function testGetToggleCode(string $htmlId, string $expectedId): void
    {
        $this->model->setData('html_id', $htmlId);
        $result = $this->model->getToggleCode();

        $this->assertIsString($result);
        $this->assertStringContainsString('toggleValueElements', $result);
        $this->assertStringContainsString($expectedId, $result);
        $this->assertStringContainsString('parentNode.parentNode', $result);
        $this->assertStringContainsString('this.checked', $result);
        $this->assertStringContainsString('if (!this.checked)', $result);
    }

    /**
     * Test getElementHtml method with value
     *
     * @return void
     */
    public function testGetElementHtmlWithValue(): void
    {
        $this->model->setData('html_id', 'price_step');
        $this->model->setData('id', 'step_id');
        $this->model->setData('name', 'step_name');
        $this->model->setValue('10.50');

        $html = $this->model->getElementHtml();

        $this->assertStringContainsString('price_step', $html);
        $this->assertStringContainsString('use_config_price_step', $html);
        $this->assertStringContainsString('Use Config Settings', $html);
        $this->assertStringContainsString('validate-number', $html);
        $this->assertStringContainsString('validate-number-range', $html);
        $this->assertStringContainsString('number-range-0.01-9999999999999999', $html);
        $this->assertStringContainsString('<br/>', $html);
        $this->assertStringContainsString('type="checkbox"', $html);
        $this->assertStringContainsString('class="checkbox"', $html);
        $this->assertStringNotContainsString('checked="checked"', $html);
    }

    /**
     * Test getElementHtml method without value (checkbox should be checked)
     *
     * @return void
     */
    public function testGetElementHtmlWithoutValue(): void
    {
        $this->model->setData('html_id', 'price_step');
        $this->model->setData('id', 'step_id');
        $this->model->setData('name', 'step_name');
        $this->model->setValue(null);

        $html = $this->model->getElementHtml();

        $this->assertStringContainsString('use_config_price_step', $html);
        $this->assertStringContainsString('checked="checked"', $html);
        $this->assertStringContainsString('disabled="disabled"', $html);
    }

    /**
     * Test getElementHtml method with empty string value (checkbox should be checked)
     *
     * @return void
     */
    public function testGetElementHtmlWithEmptyStringValue(): void
    {
        $this->model->setData('html_id', 'price_step');
        $this->model->setData('id', 'step_id');
        $this->model->setData('name', 'step_name');
        $this->model->setValue('');

        $html = $this->model->getElementHtml();

        $this->assertStringContainsString('checked="checked"', $html);
    }

    /**
     * Test getElementHtml method with disabled element
     *
     * @return void
     */
    public function testGetElementHtmlWithDisabledElement(): void
    {
        $this->model->setData('html_id', 'price_step');
        $this->model->setData('id', 'step_id');
        $this->model->setData('name', 'step_name');
        $this->model->setData('disabled', 'disabled');
        $this->model->setValue('5.00');

        $html = $this->model->getElementHtml();

        $this->assertStringContainsString('disabled="disabled"', $html);
    }

    /**
     * Test getElementHtml method with readonly element
     *
     * @return void
     */
    public function testGetElementHtmlWithReadonlyElement(): void
    {
        $this->model->setData('html_id', 'price_step');
        $this->model->setData('id', 'step_id');
        $this->model->setData('name', 'step_name');
        $this->model->setData('readonly', true);
        $this->model->setValue(null);

        $html = $this->model->getElementHtml();

        $this->assertStringContainsString('disabled="disabled"', $html);
    }

    /**
     * Test that validation classes are added to the element
     *
     * @return void
     */
    public function testValidationClassesInOutput(): void
    {
        $this->model->setData('html_id', 'price_step');
        $this->model->setData('id', 'step_id');
        $this->model->setData('name', 'step_name');
        $this->model->setValue('15.99');

        $html = $this->model->getElementHtml();

        // Verify validation classes are in the actual output HTML
        $this->assertStringContainsString('validate-number', $html);
        $this->assertStringContainsString('validate-number-range', $html);
        $this->assertStringContainsString('number-range-0.01-9999999999999999', $html);
    }

    /**
     * Test that getElementHtml contains br tag before checkbox
     *
     * @return void
     */
    public function testGetElementHtmlContainsBrTag(): void
    {
        $this->model->setData('html_id', 'price_step');
        $this->model->setData('id', 'step_id');
        $this->model->setData('name', 'step_name');
        $this->model->setValue('1.00');

        $html = $this->model->getElementHtml();

        $this->assertStringContainsString('<br/>', $html);
    }

    /**
     * Test that getElementHtml contains checkbox and label
     *
     * @return void
     */
    public function testGetElementHtmlContainsCheckboxAndLabel(): void
    {
        $this->model->setData('html_id', 'price_step');
        $this->model->setData('id', 'step_id');
        $this->model->setData('name', 'step_name');
        $this->model->setValue('2.50');

        $html = $this->model->getElementHtml();

        $this->assertStringContainsString('type="checkbox"', $html);
        $this->assertStringContainsString('class="checkbox"', $html);
        $this->assertStringContainsString('<label for="use_config_price_step"', $html);
        $this->assertStringContainsString('Use Config Settings', $html);
    }

    /**
     * Test that model can be instantiated
     *
     * @return void
     */
    public function testModelInstantiation(): void
    {
        $this->assertInstanceOf(Pricestep::class, $this->model);
    }

    /**
     * Data provider for testGetToggleCode
     *
     * @return array
     */
    public static function toggleCodeDataProvider(): array
    {
        return [
            'simple_id' => ['price_step', 'use_config_price_step'],
            'with_underscore' => ['category_price', 'use_config_category_price'],
            'with_numbers' => ['price_123', 'use_config_price_123'],
            'complex_id' => ['default_price_step_config', 'use_config_default_price_step_config']
        ];
    }
}
