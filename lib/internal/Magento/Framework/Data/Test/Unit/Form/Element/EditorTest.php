<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Tests for \Magento\Framework\Data\Form\Element\Editor
 */
namespace Magento\Framework\Data\Test\Unit\Form\Element;

use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Editor;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Math\Random;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

class EditorTest extends TestCase
{
    /**
     * @var Editor
     */
    protected $model;

    /**
     * @var Factory|MockObject
     */
    protected $factoryMock;

    /**
     * @var CollectionFactory|MockObject
     */
    protected $collectionFactoryMock;

    /**
     * @var Escaper|MockObject
     */
    protected $escaperMock;

    /**
     * @var DataObject|MockObject
     */
    protected $formMock;

    /**
     * @var DataObject|MockObject
     */
    protected $configMock;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var MockObject
     */
    private $serializer;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->factoryMock = $this->createMock(Factory::class);
        $this->collectionFactoryMock = $this->createMock(CollectionFactory::class);
        $this->escaperMock = $this->createMock(Escaper::class);
        $this->configMock = $this->createPartialMock(DataObject::class, ['getData']);
        $randomMock = $this->createMock(Random::class);
        $randomMock->method('getRandomString')->willReturn('some-rando-string');
        $secureRendererMock = $this->createMock(SecureHtmlRenderer::class);
        $secureRendererMock->method('renderEventListenerAsTag')
            ->willReturnCallback(
                function (string $event, string $listener, string $selector): string {
                    return "<script>document.querySelector('{$selector}').{$event} = () => { {$listener} };</script>";
                }
            );
        $secureRendererMock->method('renderTag')
            ->willReturnCallback(
                function (string $tag, array $attrs, ?string $content): string {
                    $attrs = new DataObject($attrs);

                    return "<$tag {$attrs->serialize()}>$content</$tag>";
                }
            );

        $this->serializer = $this->createMock(Json::class);

        $this->model = $this->objectManager->getObject(
            Editor::class,
            [
                'factoryElement' => $this->factoryMock,
                'factoryCollection' => $this->collectionFactoryMock,
                'escaper' => $this->escaperMock,
                'data' => ['config' => $this->configMock],
                'serializer' => $this->serializer,
                'random' => $randomMock,
                'secureRenderer' => $secureRendererMock
            ]
        );

        $this->formMock =
            $this->getMockBuilder(Form::class)
                ->addMethods(['getHtmlIdPrefix', 'getHtmlIdSuffix'])
                ->disableOriginalConstructor()
                ->getMock();
        $this->model->setForm($this->formMock);
    }

    public function testConstruct()
    {
        $this->assertEquals('textarea', $this->model->getType());
        $this->assertEquals('textarea', $this->model->getExtType());
        $this->assertEquals(Editor::DEFAULT_ROWS, $this->model->getRows());
        $this->assertEquals(Editor::DEFAULT_COLS, $this->model->getCols());

        $this->configMock->expects($this->once())->method('getData')->with('enabled')->willReturn(true);

        $model = $this->objectManager->getObject(
            Editor::class,
            [
                'factoryElement' => $this->factoryMock,
                'factoryCollection' => $this->collectionFactoryMock,
                'escaper' => $this->escaperMock,
                'data' => ['config' => $this->configMock]
            ]
        );

        $this->assertEquals('wysiwyg', $model->getType());
        $this->assertEquals('wysiwyg', $model->getExtType());
    }

    public function testGetElementHtml()
    {
        $html = $this->model->getElementHtml();
        $this->assertStringContainsString('</textarea>', $html);
        $this->assertStringContainsString('rows="2"', $html);
        $this->assertStringContainsString('cols="15"', $html);
        $this->assertMatchesRegularExpression('/class=\".*textarea.*\"/i', $html);
        $this->assertDoesNotMatchRegularExpression('/.*mage\/adminhtml\/wysiwyg\/widget.*/i', $html);

        $this->configMock->expects($this->any())->method('getData')
            ->willReturnMap(
                [
                    ['enabled', null, true],
                    ['hidden', null, null]
                ]
            );
        $html = $this->model->getElementHtml();
        $this->assertMatchesRegularExpression('/.*mage\/adminhtml\/wysiwyg\/widget.*/i', $html);

        $this->configMock->expects($this->any())->method('getData')
            ->willReturnMap(
                [
                    ['enabled', null, null],
                    ['widget_window_url', null, 'localhost'],
                    ['add_widgets', null, true],
                    ['hidden', null, null]
                ]
            );
        $html = $this->model->getElementHtml();
        $this->assertMatchesRegularExpression('/.*mage\/adminhtml\/wysiwyg\/widget.*/i', $html);
    }

    /**
     * @param bool $expected
     * @param bool $globalFlag
     * @param bool $attributeFlag
     * @dataProvider isEnabledDataProvider
     * @return void
     */
    public function testIsEnabled($expected, $globalFlag, $attributeFlag = null)
    {
        $this->configMock
            ->expects($this->once())
            ->method('getData')
            ->with('enabled')
            ->willReturn($globalFlag);

        if ($attributeFlag !== null) {
            $this->model->setData('wysiwyg', $attributeFlag);
        }
        $this->assertEquals($expected, $this->model->isEnabled());
    }

    /**
     * @return array
     */
    public static function isEnabledDataProvider()
    {
        return [
            'Global disabled, attribute isnt set' => [false, false],
            'Global disabled, attribute disabled' => [false, false, false],
            'Global disabled, attribute enabled' => [false, false, true],

            'Global enabled, attribute isnt set' => [true, true],
            'Global enabled, attribute disabled' => [false, true, false],
            'Global enabled, attribute enabled' => [true, true, true]
        ];
    }

    public function testIsHidden()
    {
        $this->assertEmpty($this->model->isHidden());

        $this->configMock->expects($this->once())->method('getData')->with('hidden')->willReturn(true);
        $this->assertTrue($this->model->isHidden());
    }

    public function testTranslate()
    {
        $this->assertEquals('Insert Image...', $this->model->translate('Insert Image...'));
    }

    public function testGetConfig()
    {
        $config = $this->createPartialMock(DataObject::class, ['getData']);
        $this->assertEquals($config, $this->model->getConfig());

        $this->configMock->expects($this->once())->method('getData')->with('test')->willReturn('test');
        $this->assertEquals('test', $this->model->getConfig('test'));
    }

    /**
     * Test protected `getTranslatedString` method via public `getElementHtml` method
     */
    public function testGetTranslatedString()
    {
        $callback = function ($params) {
            return json_encode($params);
        };

        $this->configMock->expects($this->any())->method('getData')
            ->willReturnCallback(
                function ($arg1) {
                    if ($arg1 == 'enabled') {
                        return true;
                    }
                }
            );
        $this->serializer->expects($this->any())
            ->method('serialize')
            ->willReturnCallback($callback);

        $html = $this->model->getElementHtml();

        $this->assertMatchesRegularExpression('/.*"Insert Image...":"Insert Image...".*/i', $html);
    }
}
