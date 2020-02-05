<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Model;

use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\CatalogUrlRewrite\Model\Map\DataCategoryUrlRewriteDatabaseMap;
use Magento\CatalogUrlRewrite\Model\Map\DataProductUrlRewriteDatabaseMap;
use Magento\CatalogUrlRewrite\Model\ResourceModel\Category\Product;
use Magento\Store\Model\ScopeInterface;
use Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException;
use Magento\UrlRewrite\Model\OptionProvider;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollection;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
 * Class for category url rewrites tests
 *
 * @magentoDbIsolation enabled
 * @magentoConfigFixture default/catalog/seo/generate_category_product_rewrites 1
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CategoryUrlRewriteTest extends AbstractUrlRewriteTest
{
    /** @var CategoryRepositoryInterface */
    private $categoryRepository;

    /** @var CategoryResource */
    private $categoryResource;

    /** @var CategoryLinkManagementInterface */
    private $categoryLinkManagement;

    /** @var CategoryFactory */
    private $categoryFactory;

    /** @var string */
    private $suffix;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->categoryRepository = $this->objectManager->create(CategoryRepositoryInterface::class);
        $this->categoryResource = $this->objectManager->get(CategoryResource::class);
        $this->categoryLinkManagement = $this->objectManager->create(CategoryLinkManagementInterface::class);
        $this->categoryFactory = $this->objectManager->get(CategoryFactory::class);
        $this->suffix = $this->config->getValue(
            CategoryUrlPathGenerator::XML_PATH_CATEGORY_URL_SUFFIX,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/category_with_position.php
     * @dataProvider categoryProvider
     * @param array $data
     * @return void
     */
    public function testUrlRewriteOnCategorySave(array $data): void
    {
        $categoryModel = $this->saveCategory($data['data']);
        $this->assertNotNull($categoryModel->getId(), 'The category was not created');
        $urlRewriteCollection = $this->getEntityRewriteCollection($categoryModel->getId());
        $this->assertRewrites(
            $urlRewriteCollection,
            $this->prepareData($data['expected_data'], (int)$categoryModel->getId())
        );
    }

    /**
     * @return array
     */
    public function categoryProvider(): array
    {
        return [
            'without_url_key' => [
                [
                    'data' => [
                        'name' => 'Test Category',
                        'attribute_set_id' => '3',
                        'parent_id' => 2,
                        'path' => '1/2',
                        'is_active' => true,
                    ],
                    'expected_data' => [
                        [
                            'request_path' => 'test-category%suffix%',
                            'target_path' => 'catalog/category/view/id/%id%',
                        ],
                    ],
                ],
            ],
            'subcategory_without_url_key' => [
                [
                    'data' => [
                        'name' => 'Test Sub Category',
                        'attribute_set_id' => '3',
                        'parent_id' => 444,
                        'path' => '1/2/444',
                        'is_active' => true,
                    ],
                    'expected_data' => [
                        [
                            'request_path' => 'category-1/test-sub-category%suffix%',
                            'target_path' => 'catalog/category/view/id/%id%',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/category_tree.php
     * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
     * @dataProvider productRewriteProvider
     * @param array $data
     * @return void
     */
    public function testCategoryProductUrlRewrite(array $data): void
    {
        $category = $this->categoryRepository->get(402);
        $this->categoryLinkManagement->assignProductToCategories('simple2', [$category->getId()]);
        $productRewriteCollection = $this->getCategoryProductRewriteCollection(
            array_keys($category->getParentCategories())
        );
        $this->assertRewrites($productRewriteCollection, $this->prepareData($data));
    }

    /**
     * @return array
     */
    public function productRewriteProvider(): array
    {
        return [
            [
                [
                    [
                        'request_path' => 'category-1/category-1-1/category-1-1-1/simple-product2%suffix%',
                        'target_path' => 'catalog/product/view/id/6/category/402',
                    ],
                    [
                        'request_path' => 'category-1/simple-product2%suffix%',
                        'target_path' => 'catalog/product/view/id/6/category/400',
                    ],
                    [
                        'request_path' => 'category-1/category-1-1/simple-product2%suffix%',
                        'target_path' => 'catalog/product/view/id/6/category/401',
                    ],
                ],
            ],
        ];
    }

    /**
     * @magentoDataFixture Magento/CatalogUrlRewrite/_files/categories_with_products.php
     * @magentoAppIsolation enabled
     * @dataProvider existingUrlProvider
     * @param array $data
     * @return void
     */
    public function testUrlRewriteOnCategorySaveWithExistingUrlKey(array $data): void
    {
        $this->expectException(UrlAlreadyExistsException::class);
        $this->expectExceptionMessage((string)__('URL key for specified store already exists.'));
        $this->saveCategory($data);
    }

    /**
     * @return array
     */
    public function existingUrlProvider(): array
    {
        return [
            'with_specified_existing_product_url_key' => [
                'data' => [
                    'name' => 'Test Category',
                    'attribute_set_id' => '3',
                    'parent_id' => 2,
                    'path' => '1/2',
                    'is_active' => true,
                    'url_key' => 'simple-product',
                ],
            ],
            'with_autogenerated_existing_product_url_key' => [
                'data' => [
                    'name' => 'Simple Product',
                    'attribute_set_id' => '3',
                    'parent_id' => 2,
                    'path' => '1/2',
                    'is_active' => true,
                ],
            ],
            'with_specified_existing_category_url_key' => [
                'data' => [
                    'name' => 'Test Category',
                    'attribute_set_id' => '3',
                    'parent_id' => 2,
                    'path' => '1/2',
                    'is_active' => true,
                    'url_key' => 'category-1',
                ],
            ],
            'with_autogenerated_existing_category_url_key' => [
                'data' => [
                    'name' => 'Category 1',
                    'attribute_set_id' => '3',
                    'parent_id' => 2,
                    'path' => '1/2',
                    'is_active' => true,
                ],
            ],
        ];
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/category_product.php
     * @magentoDataFixture Magento/Catalog/_files/catalog_category_with_slash.php
     * @dataProvider categoryMoveProvider
     * @param array $data
     * @return void
     */
    public function testUrlRewriteOnCategoryMove(array $data): void
    {
        $categoryId = $data['data']['id'];
        $category = $this->categoryRepository->get($categoryId);
        $category->move($data['data']['pid'], $data['data']['aid']);
        $productRewriteCollection = $this->getCategoryProductRewriteCollection(
            array_keys($category->getParentCategories())
        );
        $categoryRewriteCollection = $this->getEntityRewriteCollection($categoryId);
        $this->assertRewrites($categoryRewriteCollection, $this->prepareData($data['expected_data']['category']));
        $this->assertRewrites($productRewriteCollection, $this->prepareData($data['expected_data']['product']));
    }

    /**
     * @return array
     */
    public function categoryMoveProvider(): array
    {
        return [
            'append_category' => [
                [
                    'data' => [
                        'id' => '333',
                        'pid' => '3331',
                        'aid' => '0',
                    ],
                    'expected_data' => [
                        'category' => [
                            [
                                'request_path' => 'category-1.html',
                                'target_path' => 'category-with-slash-symbol/category-1%suffix%',
                                'redirect_type' => OptionProvider::PERMANENT,
                            ],
                            [
                                'request_path' => 'category-with-slash-symbol/category-1%suffix%',
                                'target_path' => 'catalog/category/view/id/333',
                            ],
                        ],
                        'product' => [
                            [
                                'request_path' => 'category-with-slash-symbol/simple-product-three%suffix%',
                                'target_path' => 'catalog/product/view/id/333/category/3331',
                            ],
                            [
                                'request_path' => 'category-with-slash-symbol/category-1/simple-product-three%suffix%',
                                'target_path' => 'catalog/product/view/id/333/category/333',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/category.php
     * @return void
     */
    public function testUrlRewritesAfterCategoryDelete(): void
    {
        $categoryId = 333;
        $categoryItemIds = $this->getEntityRewriteCollection($categoryId)->getAllIds();
        $this->categoryRepository->deleteByIdentifier($categoryId);
        $this->assertEmpty(
            array_intersect($this->getAllRewriteIds(), $categoryItemIds),
            'Not all expected category url rewrites were deleted'
        );
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoDataFixture Magento/CatalogUrlRewrite/_files/categories_with_product_ids.php
     * @return void
     */
    public function testUrlRewritesAfterCategoryWithProductsDelete(): void
    {
        $category = $this->categoryRepository->get(3);
        $childIds = explode(',', $category->getAllChildren());
        $productRewriteIds = $this->getCategoryProductRewriteCollection($childIds)->getAllIds();
        $categoryItemIds = $this->getEntityRewriteCollection($childIds)->getAllIds();
        $this->categoryRepository->deleteByIdentifier($category->getId());
        $allIds = $this->getAllRewriteIds();
        $this->assertEmpty(
            array_intersect($allIds, $categoryItemIds),
            'Not all expected category url rewrites were deleted'
        );
        $this->assertEmpty(
            array_intersect($allIds, $productRewriteIds),
            'Not all expected category-product url rewrites were deleted'
        );
    }

    /**
     * @magentoDataFixture Magento/Store/_files/second_store.php
     * @magentoDataFixture Magento/Catalog/_files/category.php
     * @return void
     */
    public function testCategoryUrlRewritePerStoreViews(): void
    {
        $urlSuffix = $this->config->getValue(
            CategoryUrlPathGenerator::XML_PATH_CATEGORY_URL_SUFFIX,
            ScopeInterface::SCOPE_STORE
        );
        $urlKeySecondStore = 'url-key-for-second-store';
        $secondStoreId = $this->storeRepository->get('fixture_second_store')->getId();
        $categoryId = 333;
        $category = $this->categoryRepository->get($categoryId);
        $urlKeyFirstStore = $category->getUrlKey();
        $this->saveCategory(
            ['store_id' => $secondStoreId, 'url_key' => $urlKeySecondStore],
            $category
        );
        $urlRewriteItems = $this->getEntityRewriteCollection($categoryId)->getItems();
        $this->assertTrue(count($urlRewriteItems) == 2);
        foreach ($urlRewriteItems as $item) {
            $item->getData('store_id') == $secondStoreId
                ? $this->assertEquals($urlKeySecondStore . $urlSuffix, $item->getRequestPath())
                : $this->assertEquals($urlKeyFirstStore . $urlSuffix, $item->getRequestPath());
        }
    }

    /**
     * @inheritdoc
     */
    protected function getUrlSuffix(): string
    {
        return $this->suffix;
    }

    /**
     * @inheritdoc
     */
    protected function getEntityType(): string
    {
        return DataCategoryUrlRewriteDatabaseMap::ENTITY_TYPE;
    }

    /**
     * Save product with data using resource model directly
     *
     * @param array $data
     * @param CategoryInterface|null $category
     * @return CategoryInterface
     */
    private function saveCategory(array $data, $category = null): CategoryInterface
    {
        $category = $category ?: $this->categoryFactory->create();
        $category->addData($data);
        $this->categoryResource->save($category);

        return $category;
    }

    /**
     * Get products url rewrites collection referred to categories
     *
     * @param string|array $categoryId
     * @return UrlRewriteCollection
     */
    private function getCategoryProductRewriteCollection($categoryId): UrlRewriteCollection
    {
        $condition = is_array($categoryId) ? ['in' => $categoryId] : $categoryId;
        $productRewriteCollection = $this->urlRewriteCollectionFactory->create();
        $productRewriteCollection
            ->join(
                ['p' => $this->categoryResource->getTable(Product::TABLE_NAME)],
                'main_table.url_rewrite_id = p.url_rewrite_id',
                'category_id'
            )
            ->addFieldToFilter('category_id', $condition)
            ->addFieldToFilter(UrlRewrite::ENTITY_TYPE, ['eq' => DataProductUrlRewriteDatabaseMap::ENTITY_TYPE]);

        return $productRewriteCollection;
    }
}
