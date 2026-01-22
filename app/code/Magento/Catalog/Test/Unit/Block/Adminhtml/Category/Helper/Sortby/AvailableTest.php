<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Category\Helper\Sortby;

use Magento\Catalog\Block\Adminhtml\Category\Helper\Sortby\Available;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Magento\Framework\Math\Random;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Available sortby helper
 *
 * @covers \Magento\Catalog\Block\Adminhtml\Category\Helper\Sortby\Available
 */
class AvailableTest extends TestCase
{
    /**
     * @var Available
     */
    private Available $model;

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
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
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

        $this->model = new Available(
            $this->factoryElementMock,
            $this->factoryCollectionMock,
            $this->escaperMock,
            [],
            $this->secureRendererMock,
            $this->randomMock
        );

        $this->model->setForm($this->formMock);
    }

    /**
     * Test getElementHtml method with value and not disabled
     *
     * @return void
     */
    public function testGetElementHtmlWithValue(): void
    {
        $this->model->setData('html_id', 'test_element');
        $this->model->setData('id', 'test_id');
        $this->model->setData('name', 'test_name');
        $this->model->setData('value', ['option1', 'option2']);

        $html = $this->model->getElementHtml();

        $this->assertStringContainsString('use_config_test_element', $html);
        $this->assertStringContainsString('Use All Available Attributes', $html);
        $this->assertStringNotContainsString('checked="checked"', $html);
    }

    /**
     * Test getElementHtml method without value
     *
     * @return void
     */
    public function testGetElementHtmlWithoutValue(): void
    {
        $this->model->setData('html_id', 'test_element');
        $this->model->setData('id', 'test_id');
        $this->model->setData('name', 'test_name');
        $this->model->setValue(null);

        $html = $this->model->getElementHtml();

        $this->assertStringContainsString('use_config_test_element', $html);
        $this->assertStringContainsString('checked="checked"', $html);
    }

    /**
     * Test getElementHtml method with disabled element
     *
     * @return void
     */
    public function testGetElementHtmlWithDisabledElement(): void
    {
        $this->model->setData('html_id', 'test_element');
        $this->model->setData('id', 'test_id');
        $this->model->setData('name', 'test_name');
        $this->model->setData('disabled', 'disabled');
        $this->model->setData('value', ['option1']);

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
        $this->model->setData('html_id', 'test_element');
        $this->model->setData('id', 'test_id');
        $this->model->setData('name', 'test_name');
        $this->model->setData('readonly', true);
        $this->model->setValue(null);

        $html = $this->model->getElementHtml();

        $this->assertStringContainsString('disabled="disabled"', $html);
    }

    /**
     * Test that the model is properly instantiated
     *
     * @return void
     */
    public function testModelInstantiation(): void
    {
        $this->assertInstanceOf(Available::class, $this->model);
    }

    /**
     * Test getToggleCode method returns correct JavaScript code
     *
     * @return void
     */
    public function testGetToggleCode(): void
    {
        $this->model->setData('html_id', 'default_test_id');
        $result = $this->model->getToggleCode();

        $this->assertIsString($result);
        $this->assertStringContainsString('toggleValueElements', $result);
        $this->assertStringContainsString('use_config_default_test_id', $result);
        $this->assertStringContainsString('parentNode.parentNode', $result);
        $this->assertStringContainsString('this.checked', $result);
        $this->assertStringContainsString('if (!this.checked)', $result);
    }

    /**
     * Test getToggleCode with different HTML IDs
     *
     * @param string $htmlId
     * @param string $expectedId
     * @return void
     * @dataProvider toggleCodeDataProvider
     */
    public function testGetToggleCodeWithDifferentIds(string $htmlId, string $expectedId): void
    {
        // Create new instance with specific html_id
        $model = new Available(
            $this->factoryElementMock,
            $this->factoryCollectionMock,
            $this->escaperMock,
            ['html_id' => $htmlId],
            $this->secureRendererMock,
            $this->randomMock
        );
        $model->setForm($this->formMock);
        
        $result = $model->getToggleCode();

        $this->assertIsString($result);
        $this->assertStringContainsString('toggleValueElements', $result);
        $this->assertStringContainsString($expectedId, $result);
        $this->assertStringContainsString('parentNode.parentNode', $result);
    }

    /**
     * Data provider for testGetToggleCodeWithDifferentIds
     *
     * @return array
     */
    public static function toggleCodeDataProvider(): array
    {
        return [
            'simple_id' => ['test_element', 'use_config_test_element'],
            'with_underscore' => ['category_sortby', 'use_config_category_sortby'],
            'with_numbers' => ['sortby_123', 'use_config_sortby_123'],
            'complex_id' => ['default_category_sortby_config', 'use_config_default_category_sortby_config']
        ];
    }
}
