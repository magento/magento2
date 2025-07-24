<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Tests for \Magento\Framework\Data\Form\Element\Image
 */
namespace Magento\Framework\Data\Test\Unit\Form\Element;

use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Data\Form\Element\Image;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Magento\Framework\Math\Random;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Url;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\UrlInterface;

/**
 * Test for the widget.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class ImageTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $_objectManagerMock;

    /**
     * @var MockObject
     */
    protected $urlBuilder;

    /**
     * @var Image
     */
    protected $_image;

    /**
     * @var array
     */
    protected $testData;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $factoryMock = $this->createMock(Factory::class);
        $collectionFactoryMock = $this->createMock(CollectionFactory::class);
        $this->urlBuilder = $this->createMock(Url::class);
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
        $this->_image = $objectManager->getObject(
            Image::class,
            [
                'factoryMock'=>$factoryMock,
                'collectionFactoryMock'=>$collectionFactoryMock,
                'urlBuilder' => $this->urlBuilder,
                '_escaper' => $objectManager->getObject(Escaper::class),
                'random' => $randomMock,
                'secureRenderer' => $secureRendererMock,
            ]
        );
        $this->testData = [
            'html_id_prefix' => 'test_id_prefix_',
            'html_id' => 'test_id',
            'html_id_suffix' => '_test_id_suffix',
            'path' => 'catalog/product/placeholder',
            'value' => 'test_value',
        ];

        $formMock = new DataObject();
        $formMock->getHtmlIdPrefix($this->testData['html_id_prefix']);
        $formMock->getHtmlIdPrefix($this->testData['html_id_suffix']);
        $this->_image->setForm($formMock);
    }

    /**
     * @covers \Magento\Framework\Data\Form\Element\Image::__construct
     */
    public function testConstruct()
    {
        $this->assertEquals('file', $this->_image->getType());
    }

    /**
     * @covers \Magento\Framework\Data\Form\Element\Image::getName
     */
    public function testGetName()
    {
        $this->_image->setName('image_name');
        $this->assertEquals('image_name', $this->_image->getName());
    }

    /**
     * @covers \Magento\Framework\Data\Form\Element\Image::getElementHtml
     */
    public function testGetElementHtmlWithoutValue()
    {
        $html = $this->_image->getElementHtml();
        $this->assertStringContainsString('class="input-file"', $html);
        $this->assertStringContainsString('<input', $html);
        $this->assertStringContainsString('type="file"', $html);
        $this->assertStringContainsString('value=""', $html);
        $this->assertStringNotContainsString('</a>', $html);
    }

    /**
     * @covers \Magento\Framework\Data\Form\Element\Image::getElementHtml
     */
    public function testGetElementHtmlWithValue()
    {
        $url = 'http://test.example.com/media/';

        $this->_image->setValue($this->testData['value']);
        $this->_image->setHtmlId($this->testData['html_id']);

        $this->urlBuilder->expects($this->once())->method('getBaseUrl')
            ->with(['_type' => UrlInterface::URL_TYPE_MEDIA])->willReturn($url);

        $expectedHtmlId = $this->testData['html_id'];

        $html = $this->_image->getElementHtml();

        $this->assertStringContainsString('class="input-file"', $html);
        $this->assertStringContainsString('<input', $html);
        $this->assertStringContainsString('type="file"', $html);
        $this->assertStringContainsString('value="test_value"', $html);

        $this->assertStringContainsString(
            '<a previewlinkid="linkIdsome-rando-string" href="'
            . $url
            . $this->testData['value']
            . '"',
            $html
        );

        $this->assertStringContainsString("imagePreview('{$expectedHtmlId}_image');\nreturn false;", $html);
        $this->assertStringContainsString('<input type="checkbox"', $html);
    }
}
