<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Model;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category as Category;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\Tree;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Test\Fixture\Category as CategoryFixture;
use Magento\Eav\Model\Entity\Attribute\Exception as AttributeException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Math\Random;
use Magento\Framework\Url;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Catalog\Model\Category.
 * - general behaviour is tested
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @see \Magento\Catalog\Model\CategoryTreeTest
 * @magentoDataFixture Magento/Catalog/_files/categories.php
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class CategoryTest extends TestCase
{
    /**
     * @var Store
     */
    protected $_store;

    /**
     * @var Category
     */
    protected $_model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /** @var CategoryResource */
    private $categoryResource;

    /** @var CategoryRepositoryInterface */
    private $categoryRepository;

    /**
     * @var DataFixtureStorage
     */
    private $dataFixtureStorage;

    /**
     * @inheritdoc
     * @throws LocalizedException
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        /** @var $storeManager StoreManagerInterface */
        $storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $this->_store = $storeManager->getStore();
        $this->_model = $this->objectManager->create(Category::class);
        $this->categoryResource = $this->objectManager->get(CategoryResource::class);
        $this->categoryRepository = $this->objectManager->get(CategoryRepositoryInterface::class);
        $this->dataFixtureStorage = DataFixtureStorageManager::getStorage();
    }

    public function testGetUrlInstance(): void
    {
        $instance = $this->_model->getUrlInstance();
        $this->assertInstanceOf(Url::class, $instance);
        $this->assertSame($instance, $this->_model->getUrlInstance());
    }

    public function testGetTreeModel(): void
    {
        $model = $this->_model->getTreeModel();
        $this->assertInstanceOf(Tree::class, $model);
        $this->assertNotSame($model, $this->_model->getTreeModel());
    }

    public function testGetTreeModelInstance(): void
    {
        $model = $this->_model->getTreeModelInstance();
        $this->assertInstanceOf(Tree::class, $model);
        $this->assertSame($model, $this->_model->getTreeModelInstance());
    }

    public function testGetDefaultAttributeSetId(): void
    {
        /* based on value installed in DB */
        $this->assertEquals(3, $this->_model->getDefaultAttributeSetId());
    }

    public function testGetProductCollection(): void
    {
        $collection = $this->_model->getProductCollection();
        $this->assertInstanceOf(ProductCollection::class, $collection);
        $this->assertEquals($this->_model->getStoreId(), $collection->getStoreId());
    }

    public function testGetAttributes(): void
    {
        $attributes = $this->_model->getAttributes();
        $this->assertArrayHasKey('name', $attributes);
        $this->assertArrayHasKey('custom_design', $attributes);

        $attributes = $this->_model->getAttributes(true);
        $this->assertArrayHasKey('name', $attributes);
        $this->assertArrayNotHasKey('custom_design', $attributes);
    }

    public function testGetProductsPosition(): void
    {
        $this->assertEquals([], $this->_model->getProductsPosition());
        $this->_model->unsetData();
        $this->_model = $this->getCategoryByName('Category 2');
        $this->assertEquals([], $this->_model->getProductsPosition());

        $this->_model->unsetData();
        $this->_model = $this->getCategoryByName('Category 1.1.1');
        $this->assertNotEmpty($this->_model->getProductsPosition());
    }

    public function testGetStoreIds(): void
    {
        $this->_model = $this->getCategoryByName('Category 1.1');
        /* id from fixture */
        $this->assertContains(
            Bootstrap::getObjectManager()->get(StoreManagerInterface::class)->getStore()->getId(),
            $this->_model->getStoreIds()
        );
    }

    public function testSetGetStoreId(): void
    {
        $this->assertEquals(
            Bootstrap::getObjectManager()->get(
                StoreManagerInterface::class
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
    public function testSetStoreIdWithNonNumericValue(): void
    {
        /** @var $store Store */
        $store = Bootstrap::getObjectManager()->create(Store::class);
        $store->load('fixturestore');

        $this->assertNotEquals($this->_model->getStoreId(), $store->getId());

        $this->_model->setStoreId('fixturestore');

        $this->assertEquals($this->_model->getStoreId(), $store->getId());
    }

    public function testGetUrl(): void
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

    public function testGetCategoryIdUrl(): void
    {
        $this->assertStringEndsWith('catalog/category/view/', $this->_model->getCategoryIdUrl());
        $this->_model->setUrlKey('test_key');
        $this->assertStringEndsWith('catalog/category/view/s/test_key/', $this->_model->getCategoryIdUrl());
    }

    public function testFormatUrlKey(): void
    {
        $this->assertEquals('test', $this->_model->formatUrlKey('test'));
        $this->assertEquals('test-some-chars-5', $this->_model->formatUrlKey('test-some#-chars^5'));
        $this->assertEquals('test', $this->_model->formatUrlKey('test-????????'));
    }

    public function testGetImageUrl(): void
    {
        $this->assertFalse($this->_model->getImageUrl());
        $this->_model->setImage('test.gif');
        $this->assertStringEndsWith('media/catalog/category/test.gif', $this->_model->getImageUrl());
    }

    public function testGetCustomDesignDate(): void
    {
        $dates = $this->_model->getCustomDesignDate();
        $this->assertArrayHasKey('from', $dates);
        $this->assertArrayHasKey('to', $dates);
    }

    public function testGetDesignAttributes(): void
    {
        $attributeCodes = array_map(
            function ($elem) {
                return $elem->getAttributeCode();
            },
            $this->_model->getDesignAttributes()
        );

        $this->assertContains('custom_design_from', $attributeCodes);
        $this->assertContains('custom_design_to', $attributeCodes);
    }

    public function testCheckId(): void
    {
        $this->_model = $this->getCategoryByName('Category 1.1.1');
        $categoryId = $this->_model->getId();
        $this->assertEquals($categoryId, $this->_model->checkId($categoryId));
        $this->assertFalse($this->_model->checkId(111));
    }

    public function testVerifyIds(): void
    {
        $ids = $this->_model->verifyIds($this->_model->getParentIds());
        $this->assertNotContains(100, $ids);
    }

    public function testHasChildren(): void
    {
        $this->_model->load(3);
        $this->assertTrue($this->_model->hasChildren());
        $this->_model->load(5);
        $this->assertFalse($this->_model->hasChildren());
    }

    public function testGetRequestPath(): void
    {
        $this->assertNull($this->_model->getRequestPath());
        $this->_model->setData('request_path', 'test');
        $this->assertEquals('test', $this->_model->getRequestPath());
    }

    public function testGetName(): void
    {
        $this->assertNull($this->_model->getName());
        $this->_model->setData('name', 'test');
        $this->assertEquals('test', $this->_model->getName());
    }

    public function testGetProductCount(): void
    {
        $this->_model->load(6);
        $this->assertEquals(0, $this->_model->getProductCount());
        $this->_model->setData([]);
        $this->_model->load(3);
        $this->assertEquals(1, $this->_model->getProductCount());
    }

    public function testGetAvailableSortBy(): void
    {
        $this->assertEquals(null, $this->_model->getAvailableSortBy());
        $this->_model->setData('available_sort_by', 'test,and,test');
        $this->assertEquals(['test', 'and', 'test'], $this->_model->getAvailableSortBy());
    }

    public function testGetAvailableSortByOptions(): void
    {
        $options = $this->_model->getAvailableSortByOptions();
        $this->assertContains('price', array_keys($options));
        $this->assertContains('position', array_keys($options));
        $this->assertContains('name', array_keys($options));
    }

    public function testGetDefaultSortBy(): void
    {
        $this->assertEquals('position', $this->_model->getDefaultSortBy());
    }

    public function testValidate(): void
    {
        $this->_model->addData(
            [
                "include_in_menu" => false,
                "is_active" => false,
                'name' => 'test',
            ]
        );
        $this->assertNotEmpty($this->_model->validate());
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/category_with_position.php
     */
    public function testSaveCategoryWithPosition(): void
    {
        $category = $this->_model->load('444');
        $this->assertEquals('5', $category->getPosition());
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testSaveCategoryWithoutImage(): void
    {
        $model = $this->objectManager->create(Category::class);
        $repository = $this->objectManager->get(CategoryRepositoryInterface::class);

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
    public function testDeleteChildren(): void
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

    /**
     * @magentoDbIsolation enabled
     * @magentoAppArea adminhtml
     * @magentoDataFixture Magento/Catalog/_files/categories_no_products.php
     */
    public function testChildrenCountAfterDeleteParentCategory(): void
    {
        $this->categoryRepository->deleteByIdentifier(3);
        $this->assertEquals(8, $this->categoryResource->getChildrenCount(1));
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/category.php
     */
    public function testAddChildCategory(): void
    {
        $data = [
            'name' => 'Child Category',
            'path' => '1/2/333',
            'is_active' => '1',
            'include_in_menu' => '1',
        ];
        $this->_model->setData($data);
        $this->categoryResource->save($this->_model);
        $parentCategory = $this->categoryRepository->get(333);
        $this->assertStringContainsString((string)$this->_model->getId(), $parentCategory->getChildren());
    }

    /**
     * @return void
     */
    public function testMissingRequiredAttribute(): void
    {
        $data = [
            'path' => '1/2',
            'is_active' => '1',
            'include_in_menu' => '1',
        ];
        $this->expectException(AttributeException::class);
        $this->expectExceptionMessage(
            (string)__('The "Name" attribute value is empty. Set the attribute and try again.')
        );
        $this->_model->setData($data);
        $this->_model->validate();
    }

    /**
     * @dataProvider categoryFieldsProvider
     * @param array $data
     */
    public function testCategoryCreateWithDifferentFields(array $data): void
    {
        $requiredData = [
            'name' => 'Test Category',
            'attribute_set_id' => '3',
            'parent_id' => 2,
        ];
        $this->_model->setData(array_merge($requiredData, $data));
        $this->categoryResource->save($this->_model);
        $category = $this->categoryRepository->get($this->_model->getId());
        $categoryData = $category->toArray(array_keys($data));
        $this->assertSame($data, $categoryData);
    }

    /**
     * Test for Category Description field to be able to contain >64kb of data
     *
     * @throws NoSuchEntityException
     * @throws \Exception
     */
    public function testMaximumDescriptionLength(): void
    {
        $random = Bootstrap::getObjectManager()->get(Random::class);
        $longDescription = $random->getRandomString(70000);

        $requiredData = [
            'name' => 'Test Category',
            'attribute_set_id' => '3',
            'parent_id' => 2,
            'description' => $longDescription
        ];
        $this->_model->setData($requiredData);
        $this->categoryResource->save($this->_model);
        $category = $this->categoryRepository->get($this->_model->getId());
        $this->assertEquals($longDescription, $category->getDescription());
    }

    /**
     * @return array
     */
    public function categoryFieldsProvider(): array
    {
        return [
            [
                'enable_fields' => [
                    'is_active' => '1',
                    'include_in_menu' => '1',
                ],
                'disable_fields' => [
                    'is_active' => '0',
                    'include_in_menu' => '0',
                ],
            ],
        ];
    }

    /**
     * @magentoDataFixture Magento/Store/_files/second_store.php
     * @magentoDataFixture Magento/Catalog/_files/categories.php
     * @magentoDbIsolation disabled
     * @return void
     */
    public function testCreateSubcategoryWithMultipleStores(): void
    {
        $parentCategoryId = 3;
        $storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $storeManager->setCurrentStore(Store::ADMIN_CODE);
        /** @var StoreRepositoryInterface $storeRepository */
        $storeRepository = $this->objectManager->get(StoreRepositoryInterface::class);
        $storeId = $storeRepository->get('fixture_second_store')->getId();
        /** @var CategoryRepositoryInterface $repository */
        $repository = $this->objectManager->get(CategoryRepositoryInterface::class);
        $parentCategory = $repository->get($parentCategoryId, $storeId);
        $parentAllStoresPath = $parentCategory->getUrlPath();
        $parentSecondStoreKey = 'parent-category-url-key-second-store';
        $parentCategory->setUrlKey($parentSecondStoreKey);
        $repository->save($parentCategory);
        /** @var Category $childCategory */
        $childCategory = $this->objectManager->create(Category::class);
        $childCategory->setName('Test Category 100')
            ->setParentId($parentCategoryId)
            ->setLevel(2)
            ->setAvailableSortBy(['position', 'name'])
            ->setDefaultSortBy('name')
            ->setIsActive(true)
            ->setPosition(1)
            ->isObjectNew(true);
        $repository->save($childCategory);
        $childCategorySecondStore = $repository->get($childCategory->getId(), $storeId);

        $this->assertEquals($parentAllStoresPath . '/test-category-100', $childCategory->getUrlPath());
        $this->assertEquals($parentSecondStoreKey . '/test-category-100', $childCategorySecondStore->getUrlPath());
    }

    protected function getCategoryByName($categoryName)
    {
        /* @var Collection $collection */
        $collection = $this->objectManager->create(Collection::class);
        $collection->addNameToResult()->load();

        return $collection->getItemByColumnValue('name', $categoryName);
    }

    /**
     * @return void
     * @throws LocalizedException|\Exception
     */
    #[
        DataFixture(CategoryFixture::class, as: 'category'),
    ]
    public function testGetUrlAfterUpdate()
    {
        $category = $this->dataFixtureStorage->get('category');
        $category->setUrlKey('new-url');
        $category->setSaveRewritesHistory(true);
        $this->categoryResource->save($category);

        $this->assertStringEndsWith('new-url.html', $category->getUrl());
    }
}
