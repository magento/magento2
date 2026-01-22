<?php

/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Product\Helper\Form;

use Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Image;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Escaper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Image class
 *
 * @covers \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Image
 */
class ImageTest extends TestCase
{
    /**
     * Base URL for media files
     */
    private const BASE_URL = 'http://example.com/pub/media/';

    /**
     * Test image path
     */
    private const TEST_IMAGE = 'test/image.jpg';

    /**
     * Short test filename
     */
    private const TEST_FILENAME = 'test.jpg';

    /**
     * HTML ID for test elements
     */
    private const TEST_HTML_ID = 'test_image';

    /**
     * @var Image
     */
    private Image $model;

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
     * @var UrlInterface|MockObject
     */
    private UrlInterface|MockObject $urlBuilder;

    /**
     * @var SecureHtmlRenderer|MockObject
     */
    private SecureHtmlRenderer|MockObject $secureRenderer;

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
        $form = $this->getMockBuilder(Form::class)
            ->disableOriginalConstructor()
            ->addMethods(['getHtmlIdPrefix', 'getHtmlIdSuffix'])
            ->getMock();
        $form->method('getHtmlIdPrefix')->willReturn('');
        $form->method('getHtmlIdSuffix')->willReturn('');

        return $form;
    }

    /**
     * Invoke a protected method for testing purposes
     *
     * @param string $methodName
     * @return mixed
     */
    private function invokeProtected(string $methodName)
    {
        $reflection = new \ReflectionClass($this->model);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invoke($this->model);
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
        $this->urlBuilder = $this->getMockForAbstractClass(UrlInterface::class);
        $this->secureRenderer = $this->createMock(SecureHtmlRenderer::class);

        $this->objectManager = new ObjectManager($this);

        // Prepare ObjectManager for the parent class (Image) that uses ObjectManager::getInstance()
        $objects = [
            [
                SecureHtmlRenderer::class,
                $this->secureRenderer
            ]
        ];
        $this->objectManager->prepareObjectManager($objects);

        $this->model = $this->objectManager->getObject(
            Image::class,
            [
                'factoryElement' => $this->factoryElement,
                'factoryCollection' => $this->factoryCollection,
                'escaper' => $this->escaper,
                'urlBuilder' => $this->urlBuilder,
                'data' => [],
                'secureRenderer' => $this->secureRenderer
            ]
        );
    }

    /**
     * Test _getUrl method with various scenarios
     *
     * @param mixed $value
     * @param bool $shouldCallGetBaseUrl
     * @param mixed $expectedResult
     * @dataProvider getUrlDataProvider
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Image::_getUrl
     * @return void
     */
    public function testGetUrl($value, bool $shouldCallGetBaseUrl, $expectedResult): void
    {
        $this->model->setValue($value);

        if ($shouldCallGetBaseUrl) {
            $this->urlBuilder->expects($this->once())
                ->method('getBaseUrl')
                ->with(['_type' => UrlInterface::URL_TYPE_MEDIA])
                ->willReturn(self::BASE_URL);

            $expectedUrl = self::BASE_URL . 'catalog/product/' . $value;
            $result = $this->invokeProtected('_getUrl');
            $this->assertSame($expectedUrl, $result);
        } else {
            $this->urlBuilder->expects($this->never())
                ->method('getBaseUrl');

            $result = $this->invokeProtected('_getUrl');
            $this->assertSame($expectedResult, $result);
        }
    }

    /**
     * Data provider for testGetUrl
     *
     * @return array
     */
    public static function getUrlDataProvider(): array
    {
        return [
            'with_value' => [
                'value' => self::TEST_IMAGE,
                'shouldCallGetBaseUrl' => true,
                'expectedResult' => null // result will be calculated in test
            ],
            'without_value' => [
                'value' => null,
                'shouldCallGetBaseUrl' => false,
                'expectedResult' => false
            ],
            'empty_value' => [
                'value' => '',
                'shouldCallGetBaseUrl' => false,
                'expectedResult' => false
            ],
            'nested_path' => [
                'value' => 'a/b/image.jpg',
                'shouldCallGetBaseUrl' => true,
                'expectedResult' => null // result will be calculated in test
            ]
        ];
    }

    /**
     * Test _getDeleteCheckbox method with various scenarios
     *
     * @param bool|null $isRequired
     * @param string|null $htmlId
     * @param string|null $imageValue
     * @param bool $expectsHiddenField
     * @param array $expectedContains
     * @dataProvider getDeleteCheckboxDataProvider
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Image::_getDeleteCheckbox
     * @return void
     */
    public function testGetDeleteCheckbox(
        ?bool $isRequired,
        ?string $htmlId,
        ?string $imageValue,
        bool $expectsHiddenField,
        array $expectedContains
    ): void {
        if ($isRequired !== null) {
            $attribute = $this->createMock(Attribute::class);
            $attribute->expects($this->once())
                ->method('getIsRequired')
                ->willReturn($isRequired);
            $this->model->setEntityAttribute($attribute);
        } else {
            $this->model->setEntityAttribute(null);
        }

        if ($htmlId !== null) {
            $this->model->setForm($this->createFormMock());
            $this->model->setId($htmlId);
        }

        if ($imageValue !== null) {
            $this->model->setValue($imageValue);
        }

        if ($expectsHiddenField) {
            $this->secureRenderer->expects($this->once())
                ->method('renderTag')
                ->with('script', [], $this->anything(), false)
                ->willReturn('<script type="text/x-magento-template">test_script</script>');
        }

        $result = $this->invokeProtected('_getDeleteCheckbox');

        $this->assertIsString($result);

        foreach ($expectedContains as $expected) {
            $this->assertStringContainsString($expected, $result);
        }
    }

    /**
     * Data provider for testGetDeleteCheckbox
     *
     * @return array
     */
    public static function getDeleteCheckboxDataProvider(): array
    {
        return [
            'non_required_attribute' => [
                'isRequired' => false,
                'htmlId' => null,
                'imageValue' => null,
                'expectsHiddenField' => false,
                'expectedContains' => []
            ],
            'no_attribute' => [
                'isRequired' => null,
                'htmlId' => null,
                'imageValue' => null,
                'expectsHiddenField' => false,
                'expectedContains' => []
            ],
            'required_with_value' => [
                'isRequired' => true,
                'htmlId' => self::TEST_HTML_ID,
                'imageValue' => self::TEST_IMAGE,
                'expectsHiddenField' => true,
                'expectedContains' => [
                    'type="hidden"',
                    'class="required-entry"',
                    '_hidden',
                    self::TEST_IMAGE,
                    'text/x-magento-template'
                ]
            ],
            'required_with_empty_value' => [
                'isRequired' => true,
                'htmlId' => self::TEST_HTML_ID,
                'imageValue' => '',
                'expectsHiddenField' => true,
                'expectedContains' => [
                    'type="hidden"',
                    '_hidden',
                    'text/x-magento-template'
                ]
            ],
            'required_different_html_id' => [
                'isRequired' => true,
                'htmlId' => 'product_image_field',
                'imageValue' => self::TEST_FILENAME,
                'expectsHiddenField' => true,
                'expectedContains' => [
                    '_hidden"',
                    'type="hidden"',
                    'text/x-magento-template'
                ]
            ]
        ];
    }

    /**
     * Test _getDeleteCheckbox includes syncOnchangeValue JavaScript for required attribute
     *
     * Uses pattern-based assertions to verify JavaScript functionality
     * without triggering static analysis inline JS warnings
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Image::_getDeleteCheckbox
     * @return void
     */
    public function testGetDeleteCheckboxIncludesJavaScriptForRequiredAttribute(): void
    {
        $htmlId = 'test_image';

        $attribute = $this->createMock(Attribute::class);
        $attribute->expects($this->once())
            ->method('getIsRequired')
            ->willReturn(true);

        $this->model->setForm($this->createFormMock());
        $this->model->setEntityAttribute($attribute);
        $this->model->setId($htmlId);
        $this->model->setValue('test.jpg');

        $this->secureRenderer->expects($this->once())
            ->method('renderTag')
            ->with('script', [], $this->callback(function ($content) {
                // Check for pattern without creating inline JS string
                return strpos($content, 'syncOnchangeValue') !== false;
            }), false)
            ->willReturn('<script type="text/x-magento-template">test</script>');

        $result = $this->invokeProtected('_getDeleteCheckbox');

        $this->assertNotEmpty($result);
        $this->assertStringContainsString('type="hidden"', $result);
        $this->assertStringContainsString('text/x-magento-template', $result);
    }

    /**
     * Test that constructor properly initializes the object
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Image::__construct
     * @return void
     */
    public function testConstructorInitializesObject(): void
    {
        $model = $this->objectManager->getObject(
            Image::class,
            [
                'factoryElement' => $this->factoryElement,
                'factoryCollection' => $this->factoryCollection,
                'escaper' => $this->escaper,
                'urlBuilder' => $this->urlBuilder,
                'data' => ['html_id' => 'test_id'],
                'secureRenderer' => $this->secureRenderer
            ]
        );

        $this->assertInstanceOf(Image::class, $model);
    }
}
