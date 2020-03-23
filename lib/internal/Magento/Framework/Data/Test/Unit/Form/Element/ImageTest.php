<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Tests for \Magento\Framework\Data\Form\Element\Image
 */
namespace Magento\Framework\Data\Test\Unit\Form\Element;

use Magento\Framework\Math\Random;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

/**
 * Test for the widget.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class ImageTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\Data\Form\Element\Image
     */
    protected $_image;

    protected function setUp()
    {
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
                    return "<$tag>$content</$tag>";
                }
            );
        $factoryMock = $this->createMock(\Magento\Framework\Data\Form\Element\Factory::class);
        $collectionFactoryMock = $this->createMock(\Magento\Framework\Data\Form\Element\CollectionFactory::class);
        $escaperMock = $this->createMock(\Magento\Framework\Escaper::class);
        $this->urlBuilder = $this->createMock(\Magento\Framework\Url::class);
        $this->_image = new \Magento\Framework\Data\Form\Element\Image(
            $factoryMock,
            $collectionFactoryMock,
            $escaperMock,
            $this->urlBuilder,
            [],
            $secureRendererMock,
            $randomMock
        );
        $formMock = new \Magento\Framework\DataObject();
        $formMock->getHtmlIdPrefix('id_prefix');
        $formMock->getHtmlIdPrefix('id_suffix');
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
        $this->assertContains('class="input-file"', $html);
        $this->assertContains('<input', $html);
        $this->assertContains('type="file"', $html);
        $this->assertContains('value=""', $html);
        $this->assertNotContains('</a>', $html);
    }

    /**
     * @covers \Magento\Framework\Data\Form\Element\Image::getElementHtml
     */
    public function testGetElementHtmlWithValue()
    {
        $this->_image->setValue('test_value');
        $this->urlBuilder->expects($this->once())
            ->method('getBaseUrl')
            ->with(['_type' => UrlInterface::URL_TYPE_MEDIA])
            ->willReturn('http://localhost/media/');
        $html = $this->_image->getElementHtml();
        $this->assertContains('class="input-file"', $html);
        $this->assertContains('<input', $html);
        $this->assertContains('type="file"', $html);
        $this->assertContains('value="test_value"', $html);
        $this->assertContains(
            '<a previewlinkid="linkIdsome-rando-string" href="http://localhost/media/test_value"',
            $html
        );
        $this->assertContains("imagePreview('_image');\nreturn false;", $html);
        $this->assertContains('<input type="checkbox"', $html);
    }
}
