<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

/**
 * Tests for \Magento\Framework\Data\Form\Field\Image
 */
namespace Magento\Config\Test\Unit\Block\System\Config\Form\Field;

class ImageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Url|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuilderMock;

    /**
     * @var \Magento\Config\Block\System\Config\Form\Field\Image
     */
    protected $image;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->urlBuilderMock = $this->getMock('Magento\Framework\Url', [], [], '', false);
        $this->image = $objectManager->getObject(
            'Magento\Config\Block\System\Config\Form\Field\Image',
            [
                'urlBuilder' => $this->urlBuilderMock,
            ]
        );

        $formMock = new \Magento\Framework\Object();
        $formMock->getHtmlIdPrefix('id_prefix');
        $formMock->getHtmlIdPrefix('id_suffix');
        $this->image->setForm($formMock);
    }

    /**
     * @covers \Magento\Config\Block\System\Config\Form\Field\Image::_getUrl
     */
    public function testGetElementHtmlWithValue()
    {
        $type = 'media';
        $url = 'http://test.example.com/media/';
        $this->urlBuilderMock->expects($this->once())->method('getBaseUrl')
            ->with(['_type' => $type])->will($this->returnValue($url));

        $this->image->setValue('test_value');
        $this->image->setFieldConfig(
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

        $html = $this->image->getElementHtml();
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
