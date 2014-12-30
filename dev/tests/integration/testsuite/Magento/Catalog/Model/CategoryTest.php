<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Catalog\Model;

/**
 * Test class for \Magento\Catalog\Model\Category.
 * - general behaviour is tested
 *
 * @see \Magento\Catalog\Model\CategoryTreeTest
 * @magentoDataFixture Magento/Catalog/_files/categories.php
 */
class CategoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Store\Model\Store
     */
    protected $_store;

    /**
     * @var \Magento\Catalog\Model\Category
     */
    protected $_model;

    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var $storeManager \Magento\Store\Model\StoreManagerInterface */
        $storeManager = $objectManager->get('Magento\Store\Model\StoreManagerInterface');
        $this->_store = $storeManager->getStore();
        $this->_model = $objectManager->create('Magento\Catalog\Model\Category');
    }

    public function testGetUrlInstance()
    {
        $instance = $this->_model->getUrlInstance();
        $this->assertInstanceOf('Magento\Framework\Url', $instance);
        $this->assertSame($instance, $this->_model->getUrlInstance());
    }

    public function testGetTreeModel()
    {
        $model = $this->_model->getTreeModel();
        $this->assertInstanceOf('Magento\Catalog\Model\Resource\Category\Tree', $model);
        $this->assertNotSame($model, $this->_model->getTreeModel());
    }

    public function testGetTreeModelInstance()
    {
        $model = $this->_model->getTreeModelInstance();
        $this->assertInstanceOf('Magento\Catalog\Model\Resource\Category\Tree', $model);
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
        $this->assertInstanceOf('Magento\Catalog\Model\Resource\Product\Collection', $collection);
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
        $this->assertEquals([], $this->_model->getProductsPosition());
        $this->_model->unsetData();
        $this->_model->load(6);
        $this->assertEquals([], $this->_model->getProductsPosition());

        $this->_model->unsetData();
        $this->_model->load(4);
        $this->assertContains(1, $this->_model->getProductsPosition());
    }

    public function testGetStoreIds()
    {
        $this->_model->load(3);
        /* id from fixture */
        $this->assertContains(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                'Magento\Store\Model\StoreManagerInterface'
            )->getStore()->getId(),
            $this->_model->getStoreIds()
        );
    }

    public function testSetGetStoreId()
    {
        $this->assertEquals(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                'Magento\Store\Model\StoreManagerInterface'
            )->getStore()->getId(),
            $this->_model->getStoreId()
        );
        $this->_model->setStoreId(1000);
        $this->assertEquals(1000, $this->_model->getStoreId());
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/category_with_position.php
     */
    public function testSaveCategoryWithPosition()
    {
        $category = $this->_model->load('444');
        $this->assertEquals('5', $category->getPosition());
    }
}
