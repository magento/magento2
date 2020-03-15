<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Catalog\Model\CategoryLayoutUpdateManager;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Provide tests for CategoryRepository model.
 */
class CategoryRepositoryTest extends TestCase
{
    private const FIXTURE_CATEGORY_ID = 333;
    private const FIXTURE_TWO_STORES_CATEGORY_ID = 555;
    private const FIXTURE_SECOND_STORE_CODE = 'fixturestore';
    private const FIXTURE_FIRST_STORE_CODE = 'default';

    private const STUB_EXISTING_FILE = 'test';
    private const STUB_NOT_EXISTING_FILE = 'does not exist';
    const SCOPE_GLOBAL = 0;

    /**
     * @var CategoryLayoutUpdateManager
     */
    private $layoutManager;

    /**
     * @var CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var CategoryCollectionFactory
     */
    private $categoryCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Sets up common objects.
     *
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->layoutManager = Bootstrap::getObjectManager()->get(CategoryLayoutUpdateManager::class);
        $this->productCollectionFactory = Bootstrap::getObjectManager()->get(CollectionFactory::class);
        $this->categoryCollectionFactory = Bootstrap::getObjectManager()->create(CategoryCollectionFactory::class);
        $this->storeManager = Bootstrap::getObjectManager()->create(StoreManagerInterface::class);
    }

    private function getRepository(): CategoryRepositoryInterface
    {
        return ObjectManager::getInstance()->get(CategoryRepositoryInterface::class);
    }

    /**
     * Create new instance of Category Repository
     *
     * @return CategoryRepositoryInterface
     */
    private function createRepository(): CategoryRepositoryInterface
    {
        return ObjectManager::getInstance()->create(CategoryRepositoryInterface::class);
    }

    /**
     * Test that custom layout file attribute is saved.
     *
     * @return void
     * @throws \Throwable
     * @magentoDataFixture Magento/Catalog/_files/category.php
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testCustomLayout(): void
    {
        $category = $this->getRepository()->get(self::FIXTURE_CATEGORY_ID);

        $this->layoutManager->setCategoryFakeFiles(self::FIXTURE_CATEGORY_ID, [self::STUB_EXISTING_FILE]);
        $category->setCustomAttribute('custom_layout_update_file', self::STUB_EXISTING_FILE);
        $this->getRepository()->save($category);

        $category = $this->getRepository()->get(self::FIXTURE_CATEGORY_ID);
        $this->assertEquals(
            self::STUB_EXISTING_FILE,
            $category->getCustomAttribute('custom_layout_update_file')->getValue()
        );

        $category->setCustomAttribute('custom_layout_update_file', self::STUB_NOT_EXISTING_FILE);
        $this->expectException(LocalizedException::class);
        $this->getRepository()->save($category);
    }

    /**
     * Test removal of categories.
     *
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/categories.php
     * @magentoAppArea adminhtml
     * @return void
     */
    public function testCategoryBehaviourAfterDelete(): void
    {
        $productCollection = $this->productCollectionFactory->create();
        $deletedCategories = ['3', '4', '5', '13'];
        $categoryCollectionIds = $this->categoryCollectionFactory->create()->getAllIds();
        $this->createRepository()->deleteByIdentifier(3);
        $this->assertEquals(
            0,
            $productCollection->addCategoriesFilter(['in' => $deletedCategories])->getSize(),
            'The category-products relations was not deleted after category delete'
        );
        $newCategoryCollectionIds = $this->categoryCollectionFactory->create()->getAllIds();
        $difference = array_diff($categoryCollectionIds, $newCategoryCollectionIds);
        sort($difference);
        $this->assertEquals(
            $deletedCategories,
            $difference,
            'Wrong categories was deleted'
        );
    }

    /**
     * Verifies whether `get()` method `$storeId` attribute works as expected.
     *
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     * @magentoDataFixture Magento/Catalog/_files/category_with_two_stores.php
     */
    public function testGetCategoryForProvidedStore()
    {
        $categoryRepository = $this->getRepository();

        $categoryDefault = $categoryRepository->get(
            self::FIXTURE_TWO_STORES_CATEGORY_ID
        );

        $this->assertSame('category-defaultstore', $categoryDefault->getUrlKey());

        $categoryFirstStore = $categoryRepository->get(
            self::FIXTURE_TWO_STORES_CATEGORY_ID,
            self::FIXTURE_FIRST_STORE_CODE
        );

        $this->assertSame('category-defaultstore', $categoryFirstStore->getUrlKey());

        $categorySecondStore = $categoryRepository->get(
            self::FIXTURE_TWO_STORES_CATEGORY_ID,
            self::FIXTURE_SECOND_STORE_CODE
        );

        $this->assertSame('category-fixturestore', $categorySecondStore->getUrlKey());
    }

    /**
     * There are 2 ways to remove custom value of attribute for custom `store_id`:
     * - using `[use_default => ['attribute_code' = true]]` syntax
     * - by assigning `null` value to attribute - @TODO to be explained and introduced
     *
     * @return array
     */
    public function useDefaultAttributesDataProvider(): array
    {
        return [
            'with-use-default' => [
                'attribute_code' => 'use_default',
                'attribute_value' => ['url_key']
            ],
//            'with-null-value' => [
//                'attribute_code' => 'url_key',
//                'attribute_value' => null
//            ]
        ];
    }

    /**
     * @magentoDbIsolation disabled
     * @magentoDataFixture Magento/Catalog/_files/category.php
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     *
     * @dataProvider useDefaultAttributesDataProvider
     *
     * @param string $attributeCode
     * @param array|null $attributeValue
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testCategoryAttributeShouldFollowDefaultAttributeValue(
        string $attributeCode,
        ?array $attributeValue
    ): void {
        $fixtureCategoryUrlKey = 'category-1';

        // Expect that both Global and Second Store values are the same
        $this->assertCategoryAttributeValue(self::FIXTURE_CATEGORY_ID, 'url_key', $fixtureCategoryUrlKey);
        $this->assertCategoryAttributeValue(
            self::FIXTURE_CATEGORY_ID,
            'url_key',
            $fixtureCategoryUrlKey,
            self::FIXTURE_SECOND_STORE_CODE
        );

        $updatedUrlKey = 'temporary-scope-key';

        // Set custom value for Second Store
        $this->updateCategoryAttribute(
            self::FIXTURE_CATEGORY_ID,
            'url_key',
            $updatedUrlKey,
            self::FIXTURE_SECOND_STORE_CODE
        );

        // Expect that `url_key` are different globally and for Second Store
        $this->assertCategoryAttributeValue(self::FIXTURE_CATEGORY_ID, 'url_key', $fixtureCategoryUrlKey);
        $this->assertCategoryAttributeValue(
            self::FIXTURE_CATEGORY_ID,
            'url_key',
            $updatedUrlKey,
            self::FIXTURE_SECOND_STORE_CODE
        );

        // -- HERE -- Removing custom value for Second Store
        $this->updateCategoryAttribute(
            self::FIXTURE_CATEGORY_ID,
            $attributeCode,
            $attributeValue,
            self::FIXTURE_SECOND_STORE_CODE
        );

        // Value for both Global and Store Scope should be equal
        $this->assertCategoryAttributeValue(self::FIXTURE_CATEGORY_ID, 'url_key', $fixtureCategoryUrlKey);
        $this->assertCategoryAttributeValue(
            self::FIXTURE_CATEGORY_ID,
            'url_key',
            $fixtureCategoryUrlKey,
            self::FIXTURE_SECOND_STORE_CODE
        );

        $newGlobalUrlKey = 'new-global-key';

        $this->updateCategoryAttribute(
            self::FIXTURE_CATEGORY_ID,
            'url_key',
            $newGlobalUrlKey,
            '0'
        );

        // Value for both should change (Store scope should follow default)
        $categoryGlobalScope = $this->getRepository()->get(self::FIXTURE_CATEGORY_ID);
        $this->assertSame($newGlobalUrlKey, $categoryGlobalScope->getUrlKey());
        $categoryStoreScope = $this->getRepository()->get(self::FIXTURE_CATEGORY_ID, self::FIXTURE_SECOND_STORE_CODE);
        $this->assertSame($newGlobalUrlKey, $categoryStoreScope->getUrlKey());
    }

    private function updateCategoryAttribute(
        int $categoryId,
        string $attributeCode,
        $attributeValue,
        $storeCode = null
    ): void {
        $fallbackStoreCode = $this->getStore()->getCode();

        if ($storeCode !== null) {
            $this->storeManager->setCurrentStore($storeCode);
        }

        $updatedCategory = $this->getRepository()->get($categoryId);
        $updatedCategory->setData($attributeCode, $attributeValue);
        $updatedCategory->setData('store_id', $this->getStore($storeCode)->getId());
        $this->assertSame($attributeValue, $updatedCategory->getData($attributeCode));
        $this->getRepository()->save($updatedCategory);

        if ($storeCode !== null) {
            $this->storeManager->setCurrentStore($fallbackStoreCode);
        }
    }

    private function getStore(?string $storeCode = null): StoreInterface
    {
        return $this->storeManager->getStore($storeCode);
    }

    private function assertCategoryAttributeValue(
        int $categoryId,
        string $attributeCode,
        ?string $attributeValue,
        ?string $storeCode = null
    ): void {
        $categoryGlobalScope = $this->getRepository()->get($categoryId, $storeCode);
        $this->assertSame($attributeValue, $categoryGlobalScope->getData($attributeCode));
    }
}
