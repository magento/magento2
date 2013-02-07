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
 * @category    Magento
 * @package     Mage_Adminhtml
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Adminhtml_Block_Catalog_Product_Helper_Form_BaseImageTest extends PHPUnit_Framework_TestCase
{
    /**
     * Object under test
     *
     * @var Mage_Adminhtml_Block_Catalog_Product_Helper_Form_BaseImage
     */
    protected $_block;

    /**
     * @var Mage_Backend_Model_Url|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_url;

    /**
     * @var Mage_Core_Helper_Data|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_coreHelper;

    /**
     * @var Mage_Catalog_Helper_Data|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_catalogHelperData;

    protected function setUp()
    {
        $mediaUploader = $this->getMockBuilder('Mage_Adminhtml_Block_Media_Uploader')->disableOriginalConstructor()
            ->setMethods(array('getDataMaxSizeInBytes'))->getMock();
        $mediaUploader->expects($this->once())->method('getDataMaxSizeInBytes')->will($this->returnValue('999'));
        $this->_url = $this->getMock('Mage_Backend_Model_Url', array('getUrl'), array(), '', false);
        $this->_url->expects($this->once())->method('getUrl')
            ->will($this->returnValue('http://example.com/pub/images/catalog_product_gallery/upload/'));

        $jsonEncode = function ($value) {
            return json_encode($value);
        };

        $this->_coreHelper = $this->getMockBuilder('Mage_Core_Helper_Data')->disableOriginalConstructor()
            ->setMethods(array('escapeHtml', 'jsonEncode'))->getMock();
        $this->_coreHelper->expects($this->any())->method('jsonEncode')->will($this->returnCallback($jsonEncode));
        $this->_catalogHelperData = $this->getMockBuilder('Mage_Catalog_Helper_Data')->disableOriginalConstructor()
            ->setMethods(array('__'))->getMock();
        $this->_catalogHelperData->expects($this->any())->method('__')->will($this->returnCallback('json_encode'));
        $form = $this->getMockBuilder('Varien_Data_Form')->disableOriginalConstructor()
            ->setMethods(null)->getMock();
        $product = $this->getMockBuilder('Mage_Catalog_Model_Product')->disableOriginalConstructor()
            ->setMethods(array('getMediaGalleryImages'))->getMock();
        $form->setDataObject($product);

        $this->_block = new Mage_Adminhtml_Block_Catalog_Product_Helper_Form_BaseImage(array(
            'name' => 'image',
            'label' => 'Base Image',
            'mediaUploader' => $mediaUploader,
            'url' => $this->_url,
            'coreHelper' => $this->_coreHelper,
            'catalogHelperData' => $this->_catalogHelperData,
        ));
        $this->_block->setForm($form);
        $this->_block->setHtmlId('image');
    }

    /**
     * Test to get valid html code for 'image' attribute
     *
     * @param mixed $imageValue
     * @param string $urlPath
     * @dataProvider validateImageUrlDataProvider
     */
    public function testGetElementHtml($imageValue, $urlPath)
    {
        $this->markTestIncomplete('Test should be rewritten as part of MAGETWO-4611');
        $this->_block->setValue($imageValue);
        $this->_coreHelper->expects($this->any())->method('escapeHtml')->will($this->returnArgument(0));
        $html = $this->_createHtmlCode($imageValue, $urlPath);

        $this->assertXmlStringEqualsXmlString(
            str_replace('&times;', '&amp;times;', "<test>{$html}</test>"),
            str_replace('&times;', '&amp;times;', "<test>{$this->_block->getElementHtml()}</test>"),
            'Another BaseImage html code is expected'
        );
    }

    /**
     * @return array
     */
    public function validateImageUrlDataProvider()
    {
        return array(
            array(
                '/f/i/file_666.png',
                'http://example.com/pub/media/tmp/catalog/product/f/i/file_78.png'
            ),
            array(
                '/f/i/file_666.png.tmp',
                'http://example.com/pub/images/image-placeholder.png'
            )
        );
    }

    /**
     * Test to get valid html code for 'image' with placeholder
     */
    public function testImagePlaceholder()
    {
        $this->markTestIncomplete('Test should be rewritten as part of MAGETWO-4611');
        $urlPath = 'http://example.com/pub/images/image-placeholder.png';
        $this->_block->setValue(null);
        $this->_coreHelper->expects($this->any())->method('escapeHtml')->will($this->returnArgument(0));
        $html = $this->_createHtmlCode('', $urlPath);
        $this->assertXmlStringEqualsXmlString(
            str_replace('&times;', '&amp;times;', "<test>{$html}</test>"),
            str_replace('&times;', '&amp;times;', "<test>{$this->_block->getElementHtml()}</test>"),
            'Another BaseImage html code is expected'
        );
    }

    /**
     * Create html code for expected result
     *
     * @param string $imageValue
     * @param string $urlPath
     *
     * @return string
     */
    protected function _createHtmlCode($imageValue, $urlPath)
    {
        $uploadImage = 'http://example.com/pub/images/catalog_product_gallery/upload/';
        return str_replace(
            array('%htmlId%', '%imageValue%', '%uploadImage%', '%imageUrl%'),
            array($this->_block->getHtmlId(), $imageValue, $uploadImage, $urlPath),
            file_get_contents(__DIR__ . '/_files/BaseImageHtml.txt')
        );
    }
}
