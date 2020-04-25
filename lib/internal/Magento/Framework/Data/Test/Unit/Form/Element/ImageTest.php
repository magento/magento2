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
use Magento\Framework\Url;
use Magento\Framework\UrlInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

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

    protected function setUp(): void
    {
        $factoryMock = $this->createMock(Factory::class);
        $collectionFactoryMock = $this->createMock(CollectionFactory::class);
        $escaperMock = $this->createMock(Escaper::class);
        $this->urlBuilder = $this->createMock(Url::class);
        $this->_image = new Image(
            $factoryMock,
            $collectionFactoryMock,
            $escaperMock,
            $this->urlBuilder
        );
        $formMock = new DataObject();
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
        $this->_image->setValue('test_value');
        $this->urlBuilder->expects($this->once())
            ->method('getBaseUrl')
            ->with(['_type' => UrlInterface::URL_TYPE_MEDIA])
            ->willReturn('http://localhost/media/');
        $html = $this->_image->getElementHtml();
        $this->assertStringContainsString('class="input-file"', $html);
        $this->assertStringContainsString('<input', $html);
        $this->assertStringContainsString('type="file"', $html);
        $this->assertStringContainsString('value="test_value"', $html);
        $this->assertStringContainsString(
            '<a href="http://localhost/media/test_value" onclick="imagePreview(\'_image\'); return false;"',
            $html
        );
        $this->assertStringContainsString('<input type="checkbox"', $html);
    }
}
