<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model;

/**
 * Test class for \Magento\Catalog\Model\Category.
 * - general behaviour is tested
 *
 * @see \Magento\Catalog\Model\CategoryTreeTest
 * @magentoDataFixture Magento/Catalog/_files/categories.php
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
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

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var $storeManager \Magento\Store\Model\StoreManagerInterface */
        $storeManager = $this->objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);
        $this->_store = $storeManager->getStore();
        $this->_model = $this->objectManager->create(\Magento\Catalog\Model\Category::class);
    }

    public function testGetUrlInstance()
    {
        $instance = $this->_model->getUrlInstance();
        $this->assertInstanceOf(\Magento\Framework\Url::class, $instance);
        $this->assertSame($instance, $this->_model->getUrlInstance());
    }

    public function testGetTreeModel()
    {
        $model = $this->_model->getTreeModel();
        $this->assertInstanceOf(\Magento\Catalog\Model\ResourceModel\Category\Tree::class, $model);
        $this->assertNotSame($model, $this->_model->getTreeModel());
    }

    public function testGetTreeModelInstance()
    {
        $model = $this->_model->getTreeModelInstance();
        $this->assertInstanceOf(\Magento\Catalog\Model\ResourceModel\Category\Tree::class, $model);
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
        $this->assertInstanceOf(\Magento\Catalog\Model\ResourceModel\Product\Collection::class, $collection);
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
        $this->_model = $this->getCategoryByName('Category 2');
        $this->assertEquals([], $this->_model->getProductsPosition());

        $this->_model->unsetData();
        $this->_model = $this->getCategoryByName('Category 1.1.1');
        $this->assertNotEmpty($this->_model->getProductsPosition());
    }

    public function testGetStoreIds()
    {
        $this->_model = $this->getCategoryByName('Category 1.1');
        /* id from fixture */
        $this->assertContains(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                \Magento\Store\Model\StoreManagerInterface::class
            )->getStore()->getId(),
            $this->_model->getStoreIds()
        );
    }

    public function testSetGetStoreId()
    {
        $this->assertEquals(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                \Magento\Store\Model\StoreManagerInterface::class
            )->getStore()->getId(),
            $this->_model->getStoreId()
        );
        $this->_model->setStoreId(1000);
        $this->assertEquals(1000, $this->_model->getStoreId());
    }

    /**
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store catalog/frontend/flat_catalog_product 1
     */
    public function testSetStoreIdWithNonNumericValue()
    {
        /** @var $store \Magento\Store\Model\Store */
        $store = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Store\Model\Store::class);
        $store->load('fixturestore');

        $this->assertNotEquals($this->_model->getStoreId(), $store->getId());

        $this->_model->setStoreId('fixturestore');

        $this->assertEquals($this->_model->getStoreId(), $store->getId());
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
        $this->_model = $this->getCategoryByName('Category 1.1.1');
        $categoryId = $this->_model->getId();
        $this->assertEquals($categoryId, $this->_model->checkId($categoryId));
        $this->assertFalse($this->_model->checkId(111));
    }

    public function testVerifyIds()
    {
        $ids = $this->_model->verifyIds($this->_model->getParentIds());
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
        $this->_model->setData([]);
        $this->_model->load(3);
        $this->assertEquals(1, $this->_model->getProductCount());
    }

    public function testGetAvailableSortBy()
    {
        $this->assertEquals([], $this->_model->getAvailableSortBy());
        $this->_model->setData('available_sort_by', 'test,and,test');
        $this->assertEquals(['test', 'and', 'test'], $this->_model->getAvailableSortBy());
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
        $this->_model->addData([
            "include_in_menu" => false,
            "is_active" => false,
            'name' => 'test',
        ]);
        $this->assertNotEmpty($this->_model->validate());
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/category_with_position.php
     */
    public function testSaveCategoryWithPosition()
    {
        $category = $this->_model->load('444');
        $this->assertEquals('5', $category->getPosition());
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testSaveCategoryWithoutImage()
    {
        $model = $this->objectManager->create(\Magento\Catalog\Model\Category::class);
        $repository = $this->objectManager->get(\Magento\Catalog\Api\CategoryRepositoryInterface::class);

        $model->setName('Test Category 100')
            ->setParentId(2)
            ->setLevel(2)
            ->setAvailableSortBy(['position', 'name'])
            ->setDefaultSortBy('name')
            ->setIsActive(true)
            ->setPosition(1)
            ->isObjectNew(true);

        $repository->save($model);
        $this->assertEmpty($model->getImage());
    }

    /**
     * @magentoAppArea adminhtml
     */
    public function testDeleteChildren()
    {
        $this->_model->unsetData();
        $this->_model->load(4);
        $this->_model->setSkipDeleteChildren(true);
        $this->_model->delete();

        $this->_model->unsetData();
        $this->_model->load(5);
        $this->assertEquals($this->_model->getId(), 5);

        $this->_model->unsetData();
        $this->_model->load(3);
        $this->assertEquals($this->_model->getId(), 3);
        $this->_model->delete();

        $this->_model->unsetData();
        $this->_model->load(5);
        $this->assertEquals($this->_model->getId(), null);
    }

    protected function getCategoryByName($categoryName)
    {
        /* @var \Magento\Catalog\Model\ResourceModel\Category\Collection $collection */

        $collection = $this->objectManager->create(\Magento\Catalog\Model\ResourceModel\Category\Collection::class);
        $collection->addNameToResult()->load();
        return $collection->getItemByColumnValue('name', $categoryName);
    }
}
