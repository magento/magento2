<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Cms\Api\GetBlockByIdentifierInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Api\StoreManagementInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Catalog\Model\CategoryLayoutUpdateManager;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Provide tests for CategoryRepository model.
 *
 * @magentoDbIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CategoryRepositoryTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var CategoryLayoutUpdateManager */
    private $layoutManager;

    /** @var CategoryRepositoryInterface */
    private $categoryRepository;

    /** @var CollectionFactory */
    private $productCollectionFactory;

    /** @var CategoryCollectionFactory */
    private $categoryCollectionFactory;

    /** @var StoreManagementInterface */
    private $storeManager;

    /** @var GetBlockByIdentifierInterface */
    private $getBlockByIdentifier;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->objectManager->configure([
            'preferences' => [
                \Magento\Catalog\Model\Category\Attribute\LayoutUpdateManager::class
                => \Magento\TestFramework\Catalog\Model\CategoryLayoutUpdateManager::class
            ]
        ]);
        $this->layoutManager = $this->objectManager->get(CategoryLayoutUpdateManager::class);
        $this->productCollectionFactory = $this->objectManager->get(CollectionFactory::class);
        $this->categoryCollectionFactory = $this->objectManager->get(CategoryCollectionFactory::class);
        $this->categoryRepository = $this->objectManager->get(CategoryRepositoryInterface::class);
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $this->getBlockByIdentifier = $this->objectManager->get(GetBlockByIdentifierInterface::class);
    }

    /**
     * Test that custom layout file attribute is saved.
     *
     * @magentoDataFixture Magento/Catalog/_files/category.php
     * @magentoAppIsolation enabled
     *
     * @return void
     */
    public function testCustomLayout(): void
    {
        $category = $this->categoryRepository->get(333);
        $newFile = 'test';
        $this->layoutManager->setCategoryFakeFiles(333, [$newFile]);
        $category->setCustomAttribute('custom_layout_update_file', $newFile);
        $this->categoryRepository->save($category);
        $category = $this->categoryRepository->get(333);
        $this->assertEquals($newFile, $category->getCustomAttribute('custom_layout_update_file')->getValue());

        //Setting non-existent value
        $newFile = 'does not exist';
        $category->setCustomAttribute('custom_layout_update_file', $newFile);
        $caughtException = false;
        try {
            $this->categoryRepository->save($category);
        } catch (LocalizedException $exception) {
            $caughtException = true;
        }
        $this->assertTrue($caughtException);
    }

    /**
     * Test removal of categories.
     *
     * @magentoDataFixture Magento/Catalog/_files/categories.php
     * @magentoAppArea adminhtml
     *
     * @return void
     */
    public function testCategoryBehaviourAfterDelete(): void
    {
        $productCollection = $this->productCollectionFactory->create();
        $deletedCategories = ['3', '4', '5', '13'];
        $categoryCollectionIds = $this->categoryCollectionFactory->create()->getAllIds();
        $this->categoryRepository->deleteByIdentifier(3);
        $this->assertEquals(
            0,
            $productCollection->addCategoriesFilter(['in' => $deletedCategories])->getSize(),
            'The category-products relations was not deleted after category delete'
        );
        $newCategoryCollectionIds = $this->categoryCollectionFactory->create()->getAllIds();
        $difference = array_diff($categoryCollectionIds, $newCategoryCollectionIds);
        sort($difference);
        $this->assertEquals($deletedCategories, $difference, 'Wrong categories was deleted');
    }

    /**
     * Verifies whether `get()` method `$storeId` attribute works as expected.
     *
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     * @magentoDataFixture Magento/Catalog/_files/category_with_two_stores.php
     *
     * @return void
     */
    public function testGetCategoryForProvidedStore(): void
    {
        $categoryId = 555;
        $categoryDefault = $this->categoryRepository->get($categoryId);
        $this->assertSame('category-defaultstore', $categoryDefault->getUrlKey());
        $defaultStoreId = $this->storeManager->getStore('default')->getId();
        $categoryFirstStore = $this->categoryRepository->get($categoryId, $defaultStoreId);
        $this->assertSame('category-defaultstore', $categoryFirstStore->getUrlKey());
        $fixtureStoreId = $this->storeManager->getStore('fixturestore')->getId();
        $categorySecondStore = $this->categoryRepository->get($categoryId, $fixtureStoreId);
        $this->assertSame('category-fixturestore', $categorySecondStore->getUrlKey());
    }

    /**
     * @magentoDataFixture Magento/Store/_files/second_store.php
     * @magentoDataFixture Magento/Catalog/_files/category.php
     * @magentoDataFixture Magento/Cms/_files/block.php
     *
     * @return void
     */
    public function testUpdateCategoryDefaultStoreView(): void
    {
        $categoryId = 333;
        $defaultStoreId = (int)$this->storeManager->getStore('default')->getId();
        $secondStoreId = (int)$this->storeManager->getStore('fixture_second_store')->getId();
        $blockId = $this->getBlockByIdentifier->execute('fixture_block', $defaultStoreId)->getId();
        $origData = $this->categoryRepository->get($categoryId)->getData();
        unset($origData[CategoryInterface::KEY_UPDATED_AT]);
        $category = $this->categoryRepository->get($categoryId, $defaultStoreId);
        $dataForDefaultStore = [
            CategoryInterface::KEY_IS_ACTIVE => 0,
            CategoryInterface::KEY_INCLUDE_IN_MENU => 0,
            CategoryInterface::KEY_NAME => 'Category default store',
            'image' => 'test.png',
            'description' => 'Description for default store',
            'landing_page' => $blockId,
            'display_mode' => Category::DM_MIXED,
            CategoryInterface::KEY_AVAILABLE_SORT_BY => ['name', 'price'],
            'default_sort_by' => 'price',
            'filter_price_range' => 5,
            'url_key' => 'default-store-category',
            'meta_title' => 'meta_title default store',
            'meta_keywords' => 'meta_keywords default store',
            'meta_description' => 'meta_description default store',
            'custom_use_parent_settings' => '0',
            'custom_design' => '2',
            'page_layout' => '2columns-right',
            'custom_apply_to_products' => '1',
        ];
        $category->addData($dataForDefaultStore);
        $updatedCategory = $this->categoryRepository->save($category);
        $this->assertCategoryData($dataForDefaultStore, $updatedCategory);
        $categorySecondStore = $this->categoryRepository->get($categoryId, $secondStoreId);
        $this->assertCategoryData($origData, $categorySecondStore);
        foreach ($dataForDefaultStore as $key => $value) {
            $this->assertNotEquals($value, $categorySecondStore->getData($key));
        }
    }

    /**
     * Assert category data.
     *
     * @param array $expectedData
     * @param CategoryInterface $category
     * @return void
     */
    private function assertCategoryData(array $expectedData, CategoryInterface $category): void
    {
        foreach ($expectedData as $key => $value) {
            $this->assertEquals($value, $category->getData($key));
        }
    }
}
