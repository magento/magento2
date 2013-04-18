<?php
/**
 * Test API work with product images
 *
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
 * @copyright Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @magentoDbIsolation enabled
 */
class Mage_Catalog_Model_Product_Api_ImageTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Catalog_Model_Product
     */
    protected $_product;

    protected $_requestData;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $productFixture = require dirname(__FILE__) . '/_files/ProductData.php';
        $product = Mage::getModel('Mage_Catalog_Model_Product');

        $product->setData($productFixture['create_full_fledged']);
        $product->save();

        $this->_product = $product;
        $this->_requestData = array(
            'label' => 'My Product Image',
            'position' => 2,
            'types' => array('small_image', 'image', 'thumbnail'),
            'exclude' => 1,
            'remove' => 0,
            'file' => array(
                'name' => 'my_image_file',
                'content' => null,
                'mime' => 'image/jpeg'
            )
        );

        parent::setUp();
    }

    /**
     * Tests valid image for product creation
     *
     * @dataProvider validImageProvider
     * @param string $validImgPath Absolute path to valid image file
     */
    public function testCreateValidImage($validImgPath)
    {
        $product = $this->_product;
        $requestData = $this->_requestData;

        // valid JPG image file
        $requestData['file']['content'] = base64_encode(file_get_contents($validImgPath));

        $imagePath = Magento_Test_Helper_Api::call(
            $this,
            'catalogProductAttributeMediaCreate',
            array('productId' => $product->getSku(), 'data' => $requestData)
        );
        $this->assertInternalType('string', $imagePath, 'String type of response expected but not received');

        // reload product to reflect changes done by API request
        $product->load($product->getId());

        // retrieve saved image
        $attributes = $product->getTypeInstance()->getSetAttributes($product);
        $imageParams = $attributes['media_gallery']->getBackend()->getImage($product, $imagePath);

        $this->assertInternalType('array', $imageParams, 'Image not found');
        $this->assertEquals($requestData['label'], $imageParams['label'], 'Label does not match');
        $this->assertEquals($requestData['position'], $imageParams['position'], 'Position does not match');
        $this->assertEquals($requestData['exclude'], $imageParams['disabled'], 'Disabled does not match');
    }

    /**
     * Tests not an image for product creation
     */
    public function testCreateNotAnImage()
    {
        $product = $this->_product;
        $requestData = $this->_requestData;

        // TXT file
        $requestData['file']['content'] = base64_encode(
            file_get_contents(dirname(__FILE__) . '/_files/_data/files/test.txt')
        );

        try {
            Magento_Test_Helper_Api::call(
                $this,
                'catalogProductAttributeMediaCreate',
                array('productId' => $product->getSku(), 'data' => $requestData)
            );
        } catch (Exception $e) {
            $this->assertEquals('Unsupported image format.', $e->getMessage(), 'Invalid exception message');
        }
        // reload product to reflect changes done by API request
        $product->load($product->getId());

        $mediaData = $product->getData('media_gallery');

        $this->assertCount(0, $mediaData['images'], 'Invalid image file has been saved');
    }

    /**
     * Tests an invalid image for product creation
     *
     * @dataProvider invalidImageProvider
     * @param strign $invalidImgPath Absolute path to invalid image file
     */
    public function testCreateInvalidImage($invalidImgPath)
    {
        $product = $this->_product;
        $requestData = $this->_requestData;

        // Not an image file with JPG extension
        $requestData['file']['content'] = base64_encode(file_get_contents($invalidImgPath));

        try {
            Magento_Test_Helper_Api::call(
                $this,
                'catalogProductAttributeMediaCreate',
                array('productId' => $product->getSku(), 'data' => $requestData)
            );
        } catch (Exception $e) {
            $this->assertEquals('Unsupported image format.', $e->getMessage(), 'Invalid exception message');
        }
        // reload product to reflect changes done by API request
        $product->load($product->getId());

        $mediaData = $product->getData('media_gallery');

        $this->assertCount(0, $mediaData['images'], 'Invalid image file has been saved');
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function invalidImageProvider()
    {
        return array(
            array(dirname(__FILE__) . '/_files/_data/files/images/test.bmp.jpg'),
            array(dirname(__FILE__) . '/_files/_data/files/images/test.php.jpg')
        );
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function validImageProvider()
    {
        return array(
            array(dirname(__FILE__) . '/_files/_data/files/images/test.jpg.jpg'),
            array(dirname(__FILE__) . '/_files/_data/files/images/test.png.jpg')
        );
    }
}
