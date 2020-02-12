<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Tests for \Magento\Framework\Data\Form\Element\Image.
 */
namespace Magento\Framework\Data\Test\Unit\Form\Element;

use Magento\Framework\UrlInterface;

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

    /**
     * @var \Magento\Framework\Escaper|\PHPUnit\Framework\MockObject\MockObject
     */
    private $escaperMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $factoryMock = $this->createMock(\Magento\Framework\Data\Form\Element\Factory::class);
        $collectionFactoryMock = $this->createMock(\Magento\Framework\Data\Form\Element\CollectionFactory::class);
        $this->escaperMock = $this->createMock(\Magento\Framework\Escaper::class);
        $this->urlBuilder = $this->createMock(\Magento\Framework\Url::class);
        $this->_image = new \Magento\Framework\Data\Form\Element\Image(
            $factoryMock,
            $collectionFactoryMock,
            $this->escaperMock,
            $this->urlBuilder
        );
        $formMock = new \Magento\Framework\DataObject();
        $formMock->getHtmlIdPrefix('id_prefix');
        $formMock->getHtmlIdPrefix('id_suffix');
        $this->_image->setForm($formMock);
    }

    /**
     * Check that getType return correct value.
     *
     * @covers \Magento\Framework\Data\Form\Element\Image::__construct
     */
    public function testConstruct()
    {
        $this->assertEquals('file', $this->_image->getType());
    }

    /**
     * Get name and check data.
     *
     * @covers \Magento\Framework\Data\Form\Element\Image::getName
     */
    public function testGetName()
    {
        $this->_image->setName('image_name');

        $this->assertEquals('image_name', $this->_image->getName());
    }

    /**
     * Get element without value and check data.
     *
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
     * Get element with value and check data.
     *
     * @covers \Magento\Framework\Data\Form\Element\Image::getElementHtml
     */
    public function testGetElementHtmlWithValue()
    {
        $data = 'test_value';
        $baseUrl = 'http://localhost/media/';
        $this->_image->setValue($data);
        $this->urlBuilder->expects($this->once())
            ->method('getBaseUrl')
            ->with(['_type' => UrlInterface::URL_TYPE_MEDIA])
            ->willReturn($baseUrl);
        $this->escaperMock->expects($this->once())
            ->method('escapeUrl')
            ->with($baseUrl . $data)
            ->willReturn($baseUrl . $data);
        $this->escaperMock->expects($this->exactly(3))->method('escapeHtmlAttr')->with($data)->willReturn($data);
        $html = $this->_image->getElementHtml();

        $this->assertContains('class="input-file"', $html);
        $this->assertContains('<input', $html);
        $this->assertContains('type="file"', $html);
        $this->assertContains('value="test_value"', $html);
        $this->assertContains(
            '<a href="http://localhost/media/test_value" onclick="imagePreview(\'_image\'); return false;"',
            $html
        );
        $this->assertContains('<input type="checkbox"', $html);
    }
}
