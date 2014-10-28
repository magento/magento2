<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        $factoryMock = $this->getMock('Magento\Framework\Data\Form\Element\Factory', array(), array(), '', false);
        $collectionFactoryMock = $this->getMock(
            'Magento\Framework\Data\Form\Element\CollectionFactory',
            array(),
            array(),
            '',
            false
        );
        $escaperMock = $this->getMock('Magento\Framework\Escaper', array(), array(), '', false);
        $this->urlBuilderMock = $this->getMock('Magento\Framework\Url', array(), array(), '', false);
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
            array(
                'id' => 'placeholder',
                'type' => 'image',
                'sortOrder' => '1',
                'showInDefault' => '1',
                'showInWebsite' => '1',
                'showInStore' => '1',
                'label' => null,
                'backend_model' => 'Magento\\Backend\\Model\\Config\\Backend\\Image',
                'upload_dir' => array(
                    'config' => 'system/filesystem/media',
                    'scope_info' => '1',
                    'value' => 'catalog/product/placeholder',
                ),
                'base_url' => array(
                    'type' => $type,
                    'scope_info' => '1',
                    'value' => 'catalog/product/placeholder',
                ),
                '_elementType' => 'field',
                'path' => 'catalog/placeholder',
            ));

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
