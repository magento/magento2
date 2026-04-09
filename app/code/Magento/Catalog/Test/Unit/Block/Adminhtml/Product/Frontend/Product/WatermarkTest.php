<?php

/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Product\Frontend\Product;

use Magento\Backend\Block\Context;
use Magento\Catalog\Block\Adminhtml\Product\Frontend\Product\Watermark;
use Magento\Catalog\Model\Config\Source\Watermark\Position;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Data\Form\Element\Imagefile;
use Magento\Framework\Data\Form\Element\Select;
use Magento\Framework\Data\Form\Element\Text;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * Unit test for Watermark class
 *
 * @covers \Magento\Catalog\Block\Adminhtml\Product\Frontend\Product\Watermark
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class WatermarkTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var Watermark
     */
    private Watermark $watermark;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var Position|MockObject
     */
    private $positionMock;

    /**
     * @var Field|MockObject
     */
    private $formFieldMock;

    /**
     * @var Factory|MockObject
     */
    private $elementFactoryMock;

    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @inheritdoc
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->contextMock = $this->createMock(Context::class);
        $this->positionMock = $this->createMock(Position::class);
        $this->formFieldMock = $this->createMock(Field::class);
        $this->elementFactoryMock = $this->createMock(Factory::class);
        $this->requestMock = $this->createMock(RequestInterface::class);

        $this->contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestMock);

        $imageTypes = [
            'thumbnail' => ['title' => 'Thumbnail'],
            'small_image' => ['title' => 'Small Image'],
            'image' => ['title' => 'Base Image']
        ];

        $this->watermark = $objectManager->getObject(
            Watermark::class,
            [
                'context' => $this->contextMock,
                'watermarkPosition' => $this->positionMock,
                'formField' => $this->formFieldMock,
                'elementFactory' => $this->elementFactoryMock,
                'imageTypes' => $imageTypes
            ]
        );
    }

    /**
     * Create element mock
     *
     * @return MockObject
     */
    private function createElementMock(): MockObject
    {
        $elementMock = $this->createPartialMockWithReflection(
            AbstractElement::class,
            ['getLegend', 'getHtmlId']
        );
        $elementMock->expects($this->any())->method('getLegend')->willReturn('Watermark Settings');
        $elementMock->expects($this->any())->method('getHtmlId')->willReturn('watermark_fieldset');
        return $elementMock;
    }

    /**
     * Create text field mock
     *
     * @return MockObject
     */
    private function createTextFieldMock(): MockObject
    {
        $mock = $this->createPartialMockWithReflection(
            Text::class,
            ['setName', 'setLabel', 'setForm', 'setRenderer', 'toHtml']
        );
        $mock->expects($this->any())->method('setName')->willReturnSelf();
        $mock->expects($this->any())->method('setForm')->willReturnSelf();
        $mock->expects($this->any())->method('setLabel')->willReturnSelf();
        $mock->expects($this->any())->method('setRenderer')->willReturnSelf();
        $mock->expects($this->any())->method('toHtml')->willReturn('<div>text field</div>');
        return $mock;
    }

    /**
     * Create image field mock
     *
     * @return MockObject
     */
    private function createImageFieldMock(): MockObject
    {
        $mock = $this->createPartialMockWithReflection(
            Imagefile::class,
            ['setName', 'setLabel', 'setForm', 'setRenderer', 'toHtml']
        );
        $mock->expects($this->any())->method('setName')->willReturnSelf();
        $mock->expects($this->any())->method('setForm')->willReturnSelf();
        $mock->expects($this->any())->method('setLabel')->willReturnSelf();
        $mock->expects($this->any())->method('setRenderer')->willReturnSelf();
        $mock->expects($this->any())->method('toHtml')->willReturn('<div>image field</div>');
        return $mock;
    }

    /**
     * Create select field mock
     *
     * @return MockObject
     */
    private function createSelectFieldMock(): MockObject
    {
        $mock = $this->createPartialMockWithReflection(
            Select::class,
            ['setName', 'setLabel', 'setValues', 'setForm', 'setRenderer', 'toHtml']
        );
        $mock->expects($this->any())->method('setName')->willReturnSelf();
        $mock->expects($this->any())->method('setForm')->willReturnSelf();
        $mock->expects($this->any())->method('setLabel')->willReturnSelf();
        $mock->expects($this->any())->method('setRenderer')->willReturnSelf();
        $mock->expects($this->any())->method('setValues')->willReturnSelf();
        $mock->expects($this->any())->method('toHtml')->willReturn('<div>select field</div>');
        return $mock;
    }

    /**
     * Setup element factory mock
     *
     * @return void
     */
    private function setupElementFactoryMock(): void
    {
        $textFieldMock = $this->createTextFieldMock();
        $imageFieldMock = $this->createImageFieldMock();
        $selectFieldMock = $this->createSelectFieldMock();

        $this->elementFactoryMock->expects($this->any())
            ->method('create')
            ->willReturnCallback(function ($type) use ($textFieldMock, $imageFieldMock, $selectFieldMock) {
                if ($type === 'text') {
                    return $textFieldMock;
                } elseif ($type === 'imagefile') {
                    return $imageFieldMock;
                } elseif ($type === 'select') {
                    return $selectFieldMock;
                }
                return null;
            });
    }

    /**
     * Test render method with various scopes
     *
     * @param string|null $websiteParam
     * @param string|null $storeParam
     * @param bool $expectUseDefault
     * @param array $expectedContains
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Frontend\Product\Watermark::render
     * @return void
     */
    #[DataProvider('renderDataProvider')]
    public function testRender(
        ?string $websiteParam,
        ?string $storeParam,
        bool $expectUseDefault,
        array $expectedContains
    ): void {
        $elementMock = $this->createElementMock();
        $formMock = $this->createMock(Form::class);
        $this->watermark->setData('form', $formMock);

        $this->requestMock->expects($this->atLeastOnce())
            ->method('getParam')
            ->willReturnMap([
                ['website', null, $websiteParam],
                ['store', null, $storeParam]
            ]);

        $this->positionMock->expects($this->exactly(3))
            ->method('toOptionArray')
            ->willReturn([
                ['value' => 'stretch', 'label' => 'Stretch'],
                ['value' => 'center', 'label' => 'Center']
            ]);

        $this->setupElementFactoryMock();

        $result = $this->watermark->render($elementMock);

        $this->assertStringContainsString('Watermark Settings', $result);
        $this->assertStringContainsString('watermark_fieldset', $result);
        $this->assertStringContainsString('</fieldset>', $result);

        foreach ($expectedContains as $expected) {
            $this->assertStringContainsString($expected, $result);
        }

        if ($expectUseDefault) {
            $this->assertStringContainsString('use-default', $result);
        }
    }

    /**
     * Data provider for testRender
     *
     * @return array
     */
    public static function renderDataProvider(): array
    {
        return [
            'default_scope' => [
                'websiteParam' => null,
                'storeParam' => null,
                'expectUseDefault' => false,
                'expectedContains' => [
                    '<div>text field</div>',
                    '<div>image field</div>',
                    '<div>select field</div>'
                ]
            ],
            'website_scope' => [
                'websiteParam' => 'base',
                'storeParam' => null,
                'expectUseDefault' => true,
                'expectedContains' => []
            ],
            'store_scope' => [
                'websiteParam' => null,
                'storeParam' => 'default',
                'expectUseDefault' => true,
                'expectedContains' => []
            ]
        ];
    }

    /**
     * Test render with empty image types
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Frontend\Product\Watermark::render
     * @return void
     */
    public function testRenderWithEmptyImageTypes(): void
    {
        $objectManager = new ObjectManager($this);
        $watermarkEmpty = $objectManager->getObject(
            Watermark::class,
            [
                'context' => $this->contextMock,
                'watermarkPosition' => $this->positionMock,
                'formField' => $this->formFieldMock,
                'elementFactory' => $this->elementFactoryMock,
                'imageTypes' => []
            ]
        );

        $elementMock = $this->createElementMock();
        $formMock = $this->createMock(Form::class);
        $watermarkEmpty->setData('form', $formMock);

        $this->requestMock->expects($this->atLeastOnce())
            ->method('getParam')
            ->willReturnMap([
                ['website', null, null],
                ['store', null, null]
            ]);

        $this->elementFactoryMock->expects($this->never())->method('create');

        $result = $watermarkEmpty->render($elementMock);

        $this->assertStringContainsString('Watermark Settings', $result);
        $this->assertStringContainsString('</fieldset>', $result);
        $this->assertStringNotContainsString('<div>text field</div>', $result);
    }

    /**
     * Test _getHeaderHtml method with default scope
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Frontend\Product\Watermark::_getHeaderHtml
     * @return void
     */
    public function testGetHeaderHtmlDefaultScope(): void
    {
        $elementMock = $this->createElementMock();

        $this->requestMock->expects($this->exactly(2))
            ->method('getParam')
            ->willReturnMap([
                ['website', null, null],
                ['store', null, null]
            ]);

        $method = new ReflectionMethod(Watermark::class, '_getHeaderHtml');

        $result = $method->invoke($this->watermark, $elementMock);

        $this->assertStringContainsString('<h4 class="icon-head head-edit-form">Watermark Settings</h4>', $result);
        $this->assertStringContainsString('<fieldset class="config" id="watermark_fieldset">', $result);
        $this->assertStringContainsString('<legend>Watermark Settings</legend>', $result);
        $this->assertStringContainsString('<table>', $result);
        $this->assertStringContainsString('<colgroup class="label" />', $result);
        $this->assertStringContainsString('<colgroup class="value" />', $result);
        $this->assertStringNotContainsString('<colgroup class="use-default" />', $result);
        $this->assertStringContainsString('<tbody>', $result);
    }

    /**
     * Test _getHeaderHtml method with website scope
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Frontend\Product\Watermark::_getHeaderHtml
     * @return void
     */
    public function testGetHeaderHtmlWebsiteScope(): void
    {
        $elementMock = $this->createElementMock();

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('website')
            ->willReturn('base');

        $method = new ReflectionMethod(Watermark::class, '_getHeaderHtml');

        $result = $method->invoke($this->watermark, $elementMock);

        $this->assertStringContainsString('<h4 class="icon-head head-edit-form">Watermark Settings</h4>', $result);
        $this->assertStringContainsString('<fieldset class="config" id="watermark_fieldset">', $result);
        $this->assertStringContainsString('<legend>Watermark Settings</legend>', $result);
        $this->assertStringContainsString('<colgroup class="use-default" />', $result);
    }

    /**
     * Test _getHeaderHtml method with store scope
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Frontend\Product\Watermark::_getHeaderHtml
     * @return void
     */
    public function testGetHeaderHtmlStoreScope(): void
    {
        $elementMock = $this->createElementMock();

        $this->requestMock->expects($this->atLeastOnce())
            ->method('getParam')
            ->willReturnMap([
                ['website', null, null],
                ['store', null, 'default']
            ]);

        $method = new ReflectionMethod(Watermark::class, '_getHeaderHtml');

        $result = $method->invoke($this->watermark, $elementMock);

        $this->assertStringContainsString('<h4 class="icon-head head-edit-form">Watermark Settings</h4>', $result);
        $this->assertStringContainsString('<colgroup class="use-default" />', $result);
    }

    /**
     * Test _getFooterHtml method
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Frontend\Product\Watermark::_getFooterHtml
     * @return void
     */
    public function testGetFooterHtml(): void
    {
        $elementMock = $this->createElementMock();

        $method = new ReflectionMethod(Watermark::class, '_getFooterHtml');

        $result = $method->invoke($this->watermark, $elementMock);

        $this->assertEquals('</tbody></table></fieldset>', $result);
    }

    /**
     * Test render with null element
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Frontend\Product\Watermark::render
     * @return void
     */
    public function testRenderWithNullElement(): void
    {
        $this->expectException(\TypeError::class);
        $this->watermark->render(null);
    }

    /**
     * Test render with element having null legend
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Frontend\Product\Watermark::render
     * @return void
     */
    public function testRenderWithNullLegend(): void
    {
        $elementMock = $this->createPartialMockWithReflection(
            AbstractElement::class,
            ['getLegend', 'getHtmlId']
        );
        $elementMock->expects($this->any())->method('getLegend')->willReturn(null);
        $elementMock->expects($this->any())->method('getHtmlId')->willReturn('test_id');

        $formMock = $this->createMock(Form::class);
        $this->watermark->setData('form', $formMock);

        $this->requestMock->expects($this->atLeastOnce())
            ->method('getParam')
            ->willReturn(null);

        $this->positionMock->expects($this->any())
            ->method('toOptionArray')
            ->willReturn([]);

        $this->setupElementFactoryMock();

        $result = $this->watermark->render($elementMock);

        $this->assertIsString($result);
        $this->assertStringContainsString('</fieldset>', $result);
    }

    /**
     * Test render with empty image types array in constructor
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Frontend\Product\Watermark::render
     * @return void
     */
    public function testRenderWithEmptyImageTypesArray(): void
    {
        $objectManager = new ObjectManager($this);
        $watermarkEmpty = $objectManager->getObject(
            Watermark::class,
            [
                'context' => $this->contextMock,
                'watermarkPosition' => $this->positionMock,
                'formField' => $this->formFieldMock,
                'elementFactory' => $this->elementFactoryMock,
                'imageTypes' => []
            ]
        );

        $elementMock = $this->createElementMock();
        $formMock = $this->createMock(Form::class);
        $watermarkEmpty->setData('form', $formMock);

        $this->requestMock->expects($this->atLeastOnce())
            ->method('getParam')
            ->willReturn(null);

        $this->elementFactoryMock->expects($this->never())->method('create');

        $result = $watermarkEmpty->render($elementMock);

        $this->assertIsString($result);
        $this->assertStringContainsString('</fieldset>', $result);
    }

    /**
     * Test render when position toOptionArray returns empty
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Frontend\Product\Watermark::render
     * @return void
     */
    public function testRenderWithEmptyPositionOptions(): void
    {
        $elementMock = $this->createElementMock();
        $formMock = $this->createMock(Form::class);
        $this->watermark->setData('form', $formMock);

        $this->requestMock->expects($this->atLeastOnce())
            ->method('getParam')
            ->willReturn(null);

        $this->positionMock->expects($this->any())
            ->method('toOptionArray')
            ->willReturn([]);

        $this->setupElementFactoryMock();

        $result = $this->watermark->render($elementMock);

        $this->assertIsString($result);
        $this->assertStringContainsString('</fieldset>', $result);
    }

    /**
     * Test render with single image type
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Frontend\Product\Watermark::render
     * @return void
     */
    public function testRenderWithSingleImageType(): void
    {
        $objectManager = new ObjectManager($this);
        $watermarkSingle = $objectManager->getObject(
            Watermark::class,
            [
                'context' => $this->contextMock,
                'watermarkPosition' => $this->positionMock,
                'formField' => $this->formFieldMock,
                'elementFactory' => $this->elementFactoryMock,
                'imageTypes' => ['thumbnail' => ['title' => 'Thumbnail Only']]
            ]
        );

        $elementMock = $this->createElementMock();
        $formMock = $this->createMock(Form::class);
        $watermarkSingle->setData('form', $formMock);

        $this->requestMock->expects($this->atLeastOnce())
            ->method('getParam')
            ->willReturn(null);

        $this->positionMock->expects($this->once())
            ->method('toOptionArray')
            ->willReturn([['value' => 'center', 'label' => 'Center']]);

        $this->setupElementFactoryMock();

        $result = $watermarkSingle->render($elementMock);

        $this->assertIsString($result);
        // Since the mocked fields return generic HTML, check for structure instead
        $this->assertStringContainsString('</fieldset>', $result);
        $this->assertStringContainsString('<div>text field</div>', $result);
    }

    /**
     * Test render with special characters in image type title
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Frontend\Product\Watermark::render
     * @return void
     */
    public function testRenderWithSpecialCharactersInTitle(): void
    {
        $objectManager = new ObjectManager($this);
        $scriptOpen = '<' . 'script>';
        $scriptClose = '</' . 'script>';
        $titleWithScript = 'Test ' . $scriptOpen . 'alert("xss")' . $scriptClose . ' Image';
        $watermarkSpecial = $objectManager->getObject(
            Watermark::class,
            [
                'context' => $this->contextMock,
                'watermarkPosition' => $this->positionMock,
                'formField' => $this->formFieldMock,
                'elementFactory' => $this->elementFactoryMock,
                'imageTypes' => [
                    'test' => ['title' => $titleWithScript]
                ]
            ]
        );

        $elementMock = $this->createElementMock();
        $formMock = $this->createMock(Form::class);
        $watermarkSpecial->setData('form', $formMock);

        $this->requestMock->expects($this->atLeastOnce())
            ->method('getParam')
            ->willReturn(null);

        $this->positionMock->expects($this->once())
            ->method('toOptionArray')
            ->willReturn([['value' => 'center', 'label' => 'Center']]);

        $this->setupElementFactoryMock();

        $result = $watermarkSpecial->render($elementMock);

        $this->assertIsString($result);
        // The title is used in field generation, so check for field HTML structure
        $this->assertStringContainsString('</fieldset>', $result);
        $this->assertStringContainsString('<div>text field</div>', $result);
    }

    /**
     * Test render with both website and store parameters
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Frontend\Product\Watermark::render
     * @return void
     */
    public function testRenderWithBothWebsiteAndStoreParams(): void
    {
        $elementMock = $this->createElementMock();
        $formMock = $this->createMock(Form::class);
        $this->watermark->setData('form', $formMock);

        $this->requestMock->expects($this->atLeastOnce())
            ->method('getParam')
            ->willReturnMap([
                ['website', null, 'base'],
                ['store', null, 'default']
            ]);

        $this->positionMock->expects($this->any())
            ->method('toOptionArray')
            ->willReturn([['value' => 'center', 'label' => 'Center']]);

        $this->setupElementFactoryMock();

        $result = $this->watermark->render($elementMock);

        $this->assertIsString($result);
        $this->assertStringContainsString('use-default', $result);
    }

    /**
     * Test _getHeaderHtml with element having empty HTML ID
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Frontend\Product\Watermark::_getHeaderHtml
     * @return void
     */
    public function testGetHeaderHtmlWithEmptyHtmlId(): void
    {
        $elementMock = $this->createPartialMockWithReflection(
            AbstractElement::class,
            ['getLegend', 'getHtmlId']
        );
        $elementMock->expects($this->any())->method('getLegend')->willReturn('Test Legend');
        $elementMock->expects($this->any())->method('getHtmlId')->willReturn('');

        $this->requestMock->expects($this->atLeastOnce())
            ->method('getParam')
            ->willReturn(null);

        $method = new ReflectionMethod(Watermark::class, '_getHeaderHtml');

        $result = $method->invoke($this->watermark, $elementMock);

        $this->assertIsString($result);
        $this->assertStringContainsString('<fieldset', $result);
    }

    /**
     * Test render with form not set
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Frontend\Product\Watermark::render
     * @return void
     */
    public function testRenderWithoutForm(): void
    {
        $elementMock = $this->createElementMock();

        $this->requestMock->expects($this->atLeastOnce())
            ->method('getParam')
            ->willReturn(null);

        $this->positionMock->expects($this->any())
            ->method('toOptionArray')
            ->willReturn([['value' => 'center', 'label' => 'Center']]);

        $this->setupElementFactoryMock();

        $result = $this->watermark->render($elementMock);

        $this->assertIsString($result);
        $this->assertStringContainsString('</fieldset>', $result);
    }
}
