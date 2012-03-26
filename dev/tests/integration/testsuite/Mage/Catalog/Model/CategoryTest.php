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
 * Test class for Mage_Catalog_Model_Category.
 * - general behaviour is tested
 *
 * @group module:Mage_Catalog
 * @see Mage_Catalog_Model_CategoryTreeTest
 * @magentoDataFixture Mage/Catalog/_files/categories.php
 */
class Mage_Catalog_Model_CategoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Catalog_Model_Category
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new Mage_Catalog_Model_Category();
    }

    public function testGetUrlInstance()
    {
        $instance = $this->_model->getUrlInstance();
        $this->assertInstanceOf('Mage_Core_Model_Url', $instance);
        $this->assertSame($instance, $this->_model->getUrlInstance());
    }

    public function testGetUrlRewrite()
    {
        $rewrite = $this->_model->getUrlRewrite();
        $this->assertInstanceOf('Mage_Core_Model_Url_Rewrite', $rewrite);
        $this->assertSame($rewrite, $this->_model->getUrlRewrite());
    }

    public function testGetTreeModel()
    {
        $model = $this->_model->getTreeModel();
        $this->assertInstanceOf('Mage_Catalog_Model_Resource_Category_Tree', $model);
        $this->assertNotSame($model, $this->_model->getTreeModel());
    }

    public function testGetTreeModelInstance()
    {
        $model = $this->_model->getTreeModelInstance();
        $this->assertInstanceOf('Mage_Catalog_Model_Resource_Category_Tree', $model);
        $this->assertSame($model, $this->_model->getTreeModelInstance());
    }

    public function testGetDefaultAttributeSetId()
    {
        /* based on value installed in DB */
        $this->assertEquals(3, $this->_model->getDefaultAttributeSetId());
    }

    public function testGetProductCollection()
    {
        $collection = $this->_model->getProductCollection();
        $this->assertInstanceOf('Mage_Catalog_Model_Resource_Product_Collection', $collection);
        $this->assertEquals($this->_model->getStoreId(), $collection->getStoreId());
    }

    public function testGetAttributes()
    {
        $attributes = $this->_model->getAttributes();
        $this->assertArrayHasKey('name', $attributes);
        $this->assertArrayHasKey('custom_design', $attributes);

        $attributes = $this->_model->getAttributes(true);
        $this->assertArrayHasKey('name', $attributes);
        $this->assertArrayNotHasKey('custom_design', $attributes);
    }

    public function testGetProductsPosition()
    {
        $this->assertEquals(array(), $this->_model->getProductsPosition());
        $this->_model->unsetData();
        $this->_model->load(6);
        $this->assertEquals(array(), $this->_model->getProductsPosition());

        $this->_model->unsetData();
        $this->_model->load(4);
        $this->assertContains(1, $this->_model->getProductsPosition());
    }

    public function testGetStoreIds()
    {
        $this->_model->load(3); /* id from fixture */
        $this->assertContains(Mage::app()->getStore()->getId(), $this->_model->getStoreIds());
    }

    public function testSetGetStoreId()
    {
        $this->assertEquals(Mage::app()->getStore()->getId(), $this->_model->getStoreId());
        $this->_model->setStoreId(1000);
        $this->assertEquals(1000, $this->_model->getStoreId());
    }

    public function testGetUrl()
    {
        $this->assertStringEndsWith('catalog/category/view/', $this->_model->getUrl());

        $this->_model->setUrl('test_url');
        $this->assertEquals('test_url', $this->_model->getUrl());

        $this->_model->setUrl(null);
        $this->_model->setRequestPath('test_path');
        $this->assertStringEndsWith('test_path', $this->_model->getUrl());

        $this->_model->setUrl(null);
        $this->_model->setRequestPath(null);
        $this->_model->setId(1000);
        $this->assertStringEndsWith('catalog/category/view/id/1000/', $this->_model->getUrl());
    }

    public function testGetCategoryIdUrl()
    {
        $this->assertStringEndsWith('catalog/category/view/', $this->_model->getCategoryIdUrl());
        $this->_model->setUrlKey('test_key');
        $this->assertStringEndsWith('catalog/category/view/s/test_key/', $this->_model->getCategoryIdUrl());
    }

    public function testFormatUrlKey()
    {
        $this->assertEquals('test', $this->_model->formatUrlKey('test'));
        $this->assertEquals('test-some-chars-5', $this->_model->formatUrlKey('test-some#-chars^5'));
        $this->assertEquals('test', $this->_model->formatUrlKey('test-????????'));
    }

    public function testGetImageUrl()
    {
        $this->assertFalse($this->_model->getImageUrl());
        $this->_model->setImage('test.gif');
        $this->assertStringEndsWith('media/catalog/category/test.gif', $this->_model->getImageUrl());
    }

    public function testGetCustomDesignDate()
    {
        $dates = $this->_model->getCustomDesignDate();
        $this->assertArrayHasKey('from', $dates);
        $this->assertArrayHasKey('to', $dates);
    }

    public function testGetDesignAttributes()
    {
        $attributes = $this->_model->getDesignAttributes();
        $this->assertContains('custom_design_from', array_keys($attributes));
        $this->assertContains('custom_design_to', array_keys($attributes));
    }

    public function testCheckId()
    {
        $this->assertEquals(4, $this->_model->checkId(4));
        $this->assertFalse($this->_model->checkId(111));
    }

    public function testVerifyIds()
    {
        $ids = $this->_model->verifyIds(array(1, 2, 3, 4, 100));
        $this->assertContains(4, $ids);
        $this->assertNotContains(100, $ids);
    }

    public function testHasChildren()
    {
        $this->_model->load(3);
        $this->assertTrue($this->_model->hasChildren());
        $this->_model->load(5);
        $this->assertFalse($this->_model->hasChildren());
    }

    public function testGetRequestPath()
    {
        $this->assertNull($this->_model->getRequestPath());
        $this->_model->setData('request_path', 'test');
        $this->assertEquals('test', $this->_model->getRequestPath());
    }

    public function testGetName()
    {
        $this->assertNull($this->_model->getName());
        $this->_model->setData('name', 'test');
        $this->assertEquals('test', $this->_model->getName());
    }

    public function testGetProductCount()
    {
        $this->_model->load(6);
        $this->assertEquals(0, $this->_model->getProductCount());
        $this->_model->setData(array());
        $this->_model->load(3);
        $this->assertEquals(1, $this->_model->getProductCount());
    }

    public function testGetAvailableSortBy()
    {
        $this->assertEquals(array(), $this->_model->getAvailableSortBy());
        $this->_model->setData('available_sort_by', 'test,and,test');
        $this->assertEquals(array('test', 'and', 'test'), $this->_model->getAvailableSortBy());
    }

    public function testGetAvailableSortByOptions()
    {
        $options = $this->_model->getAvailableSortByOptions();
        $this->assertContains('price', array_keys($options));
        $this->assertContains('position', array_keys($options));
        $this->assertContains('name', array_keys($options));
    }

    public function testGetDefaultSortBy()
    {
        $this->assertEquals('position', $this->_model->getDefaultSortBy());
    }

    public function testValidate()
    {
        $this->assertNotEmpty($this->_model->validate());
    }
}
