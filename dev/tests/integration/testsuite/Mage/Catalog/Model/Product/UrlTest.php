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
 * @package     Magento_Catalog
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for Mage_Catalog_Model_Product_Url.
 *
 * @magentoDataFixture Mage/Catalog/_files/url_rewrites.php
 */
class Mage_Catalog_Model_Product_UrlTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Catalog_Model_Product_Url
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new Mage_Catalog_Model_Product_Url;
    }

    public function testGetUrlInstance()
    {
        $instance = $this->_model->getUrlInstance();
        $this->assertInstanceOf('Mage_Core_Model_Url', $instance);
        $this->assertSame($instance, $this->_model->getUrlInstance());
    }

    public function testGetUrlRewrite()
    {
        $instance = $this->_model->getUrlRewrite();
        $this->assertInstanceOf('Mage_Core_Model_Url_Rewrite', $instance);
        $this->assertSame($instance, $this->_model->getUrlRewrite());
    }

    public function testGetUrlInStore()
    {
        $product = new Mage_Catalog_Model_Product();
        $product->load(1);
        $this->assertStringEndsWith('simple-product.html', $this->_model->getUrlInStore($product));
    }

    public function testGetProductUrl()
    {
        $product = new  Mage_Catalog_Model_Product();
        $product->load(1);
        $this->assertStringEndsWith('simple-product.html', $this->_model->getProductUrl($product));
    }

    public function testFormatUrlKey()
    {
        $this->assertEquals('abc-test', $this->_model->formatUrlKey('AbC#-$^test'));
    }

    public function testGetUrlPath()
    {
        $product = new Mage_Catalog_Model_Product();
        $product->setUrlPath('product.html');

        $category = new Mage_Catalog_Model_Category();
        $category->setUrlPath('category.html');
        $this->assertEquals('product.html', $this->_model->getUrlPath($product));
        $this->assertEquals('category/product.html', $this->_model->getUrlPath($product, $category));
    }

    public function testGetUrl()
    {
        $product = new  Mage_Catalog_Model_Product();
        $product->load(1);
        $this->assertStringEndsWith('simple-product.html', $this->_model->getUrl($product));

        $product = new  Mage_Catalog_Model_Product();
        $product->setId(100);
        $this->assertStringEndsWith('catalog/product/view/id/100/', $this->_model->getUrl($product));
    }
}
