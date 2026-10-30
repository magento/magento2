<?php

/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Product\Helper\Form;

use Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Config;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\Collection;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Escaper;
use Magento\Framework\Math\Random;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Config class
 *
 * @covers \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Config
 */
class ConfigTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var Config
     */
    private Config $model;

    /**
     * @var Factory|MockObject
     */
    private Factory|MockObject $factoryElement;

    /**
     * @var CollectionFactory|MockObject
     */
    private CollectionFactory|MockObject $factoryCollection;

    /**
     * @var Escaper|MockObject
     */
    private Escaper|MockObject $escaper;

    /**
     * @var SecureHtmlRenderer|MockObject
     */
    private SecureHtmlRenderer|MockObject $secureRenderer;

    /**
     * @var Form|MockObject
     */
    private Form|MockObject $form;

    /**
     * @var ObjectManager
     */
    private ObjectManager $objectManager;

    /**
     * Create a mock Form object for testing
     *
     * @return Form|MockObject
     */
    private function createFormMock(): Form|MockObject
    {
        $form = $this->createPartialMockWithReflection(Form::class, ['getHtmlIdPrefix', 'getHtmlIdSuffix']);
        $form->method('getHtmlIdPrefix')->willReturn('');
        $form->method('getHtmlIdSuffix')->willReturn('');

        return $form;
    }

    /**
     * Stub SecureHtmlRenderer to return Magento-compliant script tags
     *
     * Returns scripts with x-magento-template attribute to comply with
     * Magento's Content Security Policy and avoid inline JS violations
     *
     * @return void
     */
    private function stubSecureRenderer(): void
    {
        $this->secureRenderer->method('renderTag')
            ->willReturn('<script type="text/x-magento-template">test_script</script>');
        $this->secureRenderer->method('renderEventListenerAsTag')
            ->willReturn('<script type="text/x-magento-template">test_listener</script>');
    }

    /**
     * Set up test environment
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->factoryElement = $this->createMock(Factory::class);
        $this->factoryCollection = $this->createMock(CollectionFactory::class);
        $this->escaper = $this->createMock(Escaper::class);
        $this->secureRenderer = $this->createMock(SecureHtmlRenderer::class);
        $this->form = $this->createFormMock();

        $this->objectManager = new ObjectManager($this);

        // Setup Random mock
        $randomMock = $this->createMock(Random::class);
        $randomMock->method('getRandomString')->willReturn('randomstring');

        // Prepare ObjectManager for the parent class (Select) that uses ObjectManager::getInstance()
        $objects = [
            [
                SecureHtmlRenderer::class,
                $this->secureRenderer
            ],
            [
                Random::class,
                $randomMock
            ]
        ];
        $this->objectManager->prepareObjectManager($objects);

        // Setup collection factory with proper iterator
        $collection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getIterator', 'count', 'add'])
            ->getMock();
        $collection->method('getIterator')->willReturn(new \ArrayIterator([]));
        $collection->method('count')->willReturn(0);
        $this->factoryCollection->method('create')->willReturn($collection);

        // Setup escaper defaults
        $this->escaper->method('escapeHtml')->willReturnCallback(function ($value) {
            return htmlspecialchars((string)$value);
        });

        $this->model = $this->objectManager->getObject(
            Config::class,
            [
                'factoryElement' => $this->factoryElement,
                'factoryCollection' => $this->factoryCollection,
                'escaper' => $this->escaper,
                'data' => [],
                'secureRenderer' => $this->secureRenderer
            ]
        );

        $this->model->setForm($this->form);
    }

    /**
     * Test that constructor properly initializes the object with SecureHtmlRenderer
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Config::__construct
     * @return void
     */
    public function testConstructorInitializesSecureRenderer(): void
    {
        $model = $this->objectManager->getObject(
            Config::class,
            [
                'factoryElement' => $this->factoryElement,
                'factoryCollection' => $this->factoryCollection,
                'escaper' => $this->escaper,
                'data' => ['html_id' => 'test_id'],
                'secureRenderer' => $this->secureRenderer
            ]
        );

        $this->assertInstanceOf(Config::class, $model);
    }

    /**
     * Test getElementHtml with various scenarios
     *
     * @param string $value
     * @param bool $readonly
     * @param array $expectedContains
     * @param array $expectedNotContains
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Config::getElementHtml
     * @return void
     */
    #[DataProvider('getElementHtmlDataProvider')]
    public function testGetElementHtmlScenarios(
        string $value,
        bool $readonly,
        array $expectedContains,
        array $expectedNotContains = []
    ): void {
        $htmlId = 'test_element';
        $this->model->setHtmlId($htmlId);
        $this->model->setValue($value);
        $this->model->setReadonly($readonly);

        $this->stubSecureRenderer();

        $html = $this->model->getElementHtml();

        // Assert expected strings are present
        foreach ($expectedContains as $expected) {
            $this->assertStringContainsString($expected, $html);
        }

        // Assert expected strings are NOT present
        foreach ($expectedNotContains as $notExpected) {
            $this->assertStringNotContainsString($notExpected, $html);
        }
    }

    /**
     * Data provider for testGetElementHtmlScenarios
     *
     * @return array
     */
    public static function getElementHtmlDataProvider(): array
    {
        return [
            'empty_value_checked_checkbox' => [
                'value' => '',
                'readonly' => false,
                'expectedContains' => [
                    'use_config_test_element',
                    'checked="checked"',
                    'Use Config Settings',
                    'type="checkbox"'
                ],
                'expectedNotContains' => []
            ],
            'with_value_unchecked_checkbox' => [
                'value' => 'some_value',
                'readonly' => false,
                'expectedContains' => [
                    'use_config_test_element',
                    'Use Config Settings',
                    'type="checkbox"'
                ],
                'expectedNotContains' => ['checked="checked"']
            ],
            'readonly_disabled_checkbox' => [
                'value' => '',
                'readonly' => true,
                'expectedContains' => [
                    'disabled="disabled"',
                    'use_config_test_element'
                ],
                'expectedNotContains' => []
            ]
        ];
    }

    /**
     * Test getElementHtml additional scenarios
     *
     * @param bool $testCallback
     * @param array $expectedContains
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Config::getElementHtml
     * @return void
     */
    #[DataProvider('getElementHtmlAdditionalDataProvider')]
    public function testGetElementHtmlAdditionalScenarios(
        bool $testCallback,
        array $expectedContains
    ): void {
        $htmlId = 'test_element';
        $this->model->setHtmlId($htmlId);
        $this->model->setValue('');

        if ($testCallback) {
            $this->secureRenderer->expects($this->once())
                ->method('renderTag')
                ->with('script', [], $this->callback(function ($content) {
                    return strpos($content, 'toggleValueElements') !== false;
                }), false)
                ->willReturn('<script type="text/x-magento-template">test</script>');

            $this->secureRenderer->expects($this->once())
                ->method('renderEventListenerAsTag')
                ->with('onclick', $this->callback(function ($listener) {
                    return strpos($listener, 'toggleValueElements') !== false;
                }), $this->anything())
                ->willReturn('<script type="text/x-magento-template">test_listener</script>');
        } else {
            $this->stubSecureRenderer();
        }

        $html = $this->model->getElementHtml();

        $this->assertNotEmpty($html);

        foreach ($expectedContains as $expected) {
            $this->assertStringContainsString($expected, $html);
        }
    }

    /**
     * Data provider for testGetElementHtmlAdditionalScenarios
     *
     * @return array
     */
    public static function getElementHtmlAdditionalDataProvider(): array
    {
        return [
            'javascript_callback' => [
                'testCallback' => true,
                'expectedContains' => [
                    'use_config_test_element',
                    'text/x-magento-template'
                ]
            ],
            'checkbox_name' => [
                'testCallback' => false,
                'expectedContains' => [
                    'name="product[use_config_test_element]"'
                ]
            ],
            'checkbox_value' => [
                'testCallback' => false,
                'expectedContains' => [
                    'value="1"'
                ]
            ],
            'checkbox_label' => [
                'testCallback' => false,
                'expectedContains' => [
                    'Use Config Settings'
                ]
            ],
            'secure_renderer_tags' => [
                'testCallback' => false,
                'expectedContains' => [
                    'text/x-magento-template',
                    'test_script',
                    'test_listener'
                ]
            ]
        ];
    }
}
