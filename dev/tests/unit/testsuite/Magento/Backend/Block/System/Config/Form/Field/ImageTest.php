<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Tests for \Magento\Framework\Data\Form\Element\Image
 */
namespace Magento\Backend\Block\System\Config\Form\Field;

class ImageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManagerMock;

    /**
     * @var \Magento\Framework\Url|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuilderMock;

    /**
     * @var \Magento\Backend\Block\System\Config\Form\Field\Image
     */
    protected $_image;

    protected function setUp()
    {
        $factoryMock = $this->getMock('Magento\Framework\Data\Form\Element\Factory', [], [], '', false);
        $collectionFactoryMock = $this->getMock(
            'Magento\Framework\Data\Form\Element\CollectionFactory',
            [],
            [],
            '',
            false
        );
        $escaperMock = $this->getMock('Magento\Framework\Escaper', [], [], '', false);
        $this->urlBuilderMock = $this->getMock('Magento\Framework\Url', [], [], '', false);
        $this->_image = new \Magento\Backend\Block\System\Config\Form\Field\Image(
            $factoryMock,
            $collectionFactoryMock,
            $escaperMock,
            $this->urlBuilderMock
        );
        $formMock = new \Magento\Framework\Object();
        $formMock->getHtmlIdPrefix('id_prefix');
        $formMock->getHtmlIdPrefix('id_suffix');
        $this->_image->setForm($formMock);
    }

    /**
     * @covers \Magento\Backend\Block\System\Config\Form\Field\Image::_getUrl
     */
    public function testGetElementHtmlWithValue()
    {
        $type = 'media';
        $url = 'http://test.example.com/media/';
        $this->urlBuilderMock->expects($this->once())->method('getBaseUrl')
            ->with(['_type' => $type])->will($this->returnValue($url));

        $this->_image->setValue('test_value');
        $this->_image->setFieldConfig(
            [
                'id' => 'placeholder',
                'type' => 'image',
                'sortOrder' => '1',
                'showInDefault' => '1',
                'showInWebsite' => '1',
                'showInStore' => '1',
                'label' => null,
                'backend_model' => 'Magento\\Backend\\Model\\Config\\Backend\\Image',
                'upload_dir' => [
                    'config' => 'system/filesystem/media',
                    'scope_info' => '1',
                    'value' => 'catalog/product/placeholder',
                ],
                'base_url' => [
                    'type' => $type,
                    'scope_info' => '1',
                    'value' => 'catalog/product/placeholder',
                ],
                '_elementType' => 'field',
                'path' => 'catalog/placeholder',
            ]);

        $html = $this->_image->getElementHtml();
        $this->assertContains('class="input-file"', $html);
        $this->assertContains('<input', $html);
        $this->assertContains('type="file"', $html);
        $this->assertContains('value="test_value"', $html);
        $this->assertContains(
            '<a href="' . $url
                . 'catalog/product/placeholder/test_value" onclick="imagePreview(\'_image\'); return false;"',
            $html
        );
        $this->assertContains('<input type="checkbox"', $html);
    }
}
