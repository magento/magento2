<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Model;

use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\CategoryFactory as CategoryResourceFactory;
use Magento\CatalogUrlRewrite\Model\Map\DataCategoryUrlRewriteDatabaseMap;
use Magento\CatalogUrlRewrite\Model\Map\DataProductUrlRewriteDatabaseMap;
use Magento\CatalogUrlRewrite\Model\ResourceModel\Category\Product;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException;
use Magento\UrlRewrite\Model\OptionProvider;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollection;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollectionFactory;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use PHPUnit\Framework\TestCase;

/**
 * Class for category url rewrites tests
 *
 * @magentoDbIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CategoryUrlRewriteTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var CategoryFactory */
    private $categoryFactory;

    /** @var UrlRewriteCollectionFactory */
    private $urlRewriteCollectionFactory;

    /** @var CategoryRepositoryInterface */
    private $categoryRepository;

    /** @var CategoryResourceFactory */
    private $categoryResourceFactory;

    /** @var CategoryLinkManagementInterface */
    private $categoryLinkManagment;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var StoreRepositoryInterface */
    private $storeRepository;

    /** @var ScopeConfigInterface */
    private $config;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->categoryFactory = $this->objectManager->get(CategoryFactory::class);
        $this->urlRewriteCollectionFactory = $this->objectManager->get(UrlRewriteCollectionFactory::class);
        $this->categoryRepository = $this->objectManager->create(CategoryRepositoryInterface::class);
        $this->categoryResourceFactory = $this->objectManager->get(CategoryResourceFactory::class);
        $this->categoryLinkManagment = $this->objectManager->create(CategoryLinkManagementInterface::class);
        $this->productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $this->storeRepository = $this->objectManager->create(StoreRepositoryInterface::class);
        $this->config = $this->objectManager->get(ScopeConfigInterface::class);
    }

    /**
     * @magentoConfigFixture default/catalog/seo/generate_category_product_rewrites 1
     * @magentoDataFixture Magento/Catalog/_files/category_with_position.php
     * @dataProvider categoryProvider
     * @param array $data
     * @return void
     */
    public function testUrlRewriteOnCategorySave(array $data): void
    {
        $categoryModel = $this->categoryFactory->create();
        $categoryModel->isObjectNew(true);
        $categoryModel->setData($data['data']);
        $categoryResource = $this->categoryResourceFactory->create();
        $categoryResource->save($categoryModel);
        $this->assertNotNull($categoryModel->getId(), 'The category was not created');
        $urlRewriteCollection = $this->getCategoryRewriteCollection($categoryModel->getId());
        foreach ($urlRewriteCollection as $item) {
            foreach ($data['expected_data'] as $field => $expectedItem) {
                $this->assertEquals(
                    sprintf($expectedItem, $categoryModel->getId()),
                    $item[$field],
                    'The expected data does not match actual value'
                );
            }
        }
    }

    /**
     * @return array
     */
    public static function categoryProvider(): array
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
                        'request_path' => 'test-category.html',
                        'target_path' => 'catalog/category/view/id/%s',
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
                        'request_path' => 'category-1/test-sub-category.html',
                        'target_path' => 'catalog/category/view/id/%s',
                    ],
                ],
            ],
        ];
    }

    /**
     * @magentoConfigFixture default/catalog/seo/generate_category_product_rewrites 1
     * @magentoDataFixture Magento/Catalog/_files/category_tree.php
     * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
     * @dataProvider productRewriteProvider
     * @param array $data
     * @return void
     */
    public function testCategoryProductUrlRewrite(array $data): void
    {
        $category = $this->categoryRepository->get(402);
        $this->categoryLinkManagment->assignProductToCategories('simple2', [$category->getId()]);
        $productRewriteCollection = $this->getProductRewriteCollection(array_keys($category->getParentCategories()));
        $this->assertRewrites($productRewriteCollection, $data);
    }

    /**
     * @return array
     */
    public static function productRewriteProvider(): array
    {
        return [
            [
                [
                    [
                        'request_path' => 'category-1/category-1-1/category-1-1-1/simple-product2.html',
                        'target_path' => 'catalog/product/view/id/6/category/402',
                    ],
                    [
                        'request_path' => 'category-1/simple-product2.html',
                        'target_path' => 'catalog/product/view/id/6/category/400',
                    ],
                    [
                        'request_path' => 'category-1/category-1-1/simple-product2.html',
                        'target_path' => 'catalog/product/view/id/6/category/401',
                    ],
                ],
            ],
        ];
    }

    /**
     * @magentoConfigFixture default/catalog/seo/generate_category_product_rewrites 1
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
        $category = $this->categoryFactory->create();
        $category->setData($data);
        $categoryResource = $this->categoryResourceFactory->create();
        $categoryResource->save($category);
    }

    /**
     * @return array
     */
    public static function existingUrlProvider(): array
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
     * @magentoConfigFixture default/catalog/seo/generate_category_product_rewrites 1
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
        $productRewriteCollection = $this->getProductRewriteCollection(array_keys($category->getParentCategories()));
        $categoryRewriteCollection = $this->getCategoryRewriteCollection($categoryId);
        $this->assertRewrites($categoryRewriteCollection, $data['expected_data']['category']);
        $this->assertRewrites($productRewriteCollection, $data['expected_data']['product']);
    }

    /**
     * @return array
     */
    public static function categoryMoveProvider(): array
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
                                'target_path' => 'category-with-slash-symbol/category-1.html',
                                'redirect_type' => OptionProvider::PERMANENT,
                            ],
                            [
                                'request_path' => 'category-with-slash-symbol/category-1.html',
                                'target_path' => 'catalog/category/view/id/333',
                            ],
                        ],
                        'product' => [
                            [
                                'request_path' => 'category-with-slash-symbol/simple-product-three.html',
                                'target_path' => 'catalog/product/view/id/333/category/3331',
                            ],
                            [
                                'request_path' => 'category-with-slash-symbol/category-1/simple-product-three.html',
                                'target_path' => 'catalog/product/view/id/333/category/333',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @magentoConfigFixture default/catalog/seo/generate_category_product_rewrites 1
     * @magentoDataFixture Magento/Catalog/_files/category.php
     * @magentoAppArea adminhtml
     * @return void
     */
    public function testUrlRewritesAfterCategoryDelete(): void
    {
        $categoryId = 333;
        $categoryItemIds = $this->getCategoryRewriteCollection($categoryId)->getAllIds();
        $this->categoryRepository->deleteByIdentifier($categoryId);
        $this->assertEmpty(
            array_intersect($this->getAllRewriteIds(), $categoryItemIds),
            'Not all expected category url rewrites were deleted'
        );
    }

    /**
     * @magentoConfigFixture default/catalog/seo/generate_category_product_rewrites 1
     * @magentoAppArea adminhtml
     * @magentoDataFixture Magento/CatalogUrlRewrite/_files/categories_with_product_ids.php
     * @return void
     */
    public function testUrlRewritesAfterCategoryWithProductsDelete(): void
    {
        $category = $this->categoryRepository->get(3);
        $childIds = explode(',', $category->getAllChildren());
        $productRewriteIds = $this->getProductRewriteCollection($childIds)->getAllIds();
        $categoryItemIds = $this->getCategoryRewriteCollection($childIds)->getAllIds();
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
     * @magentoConfigFixture default/catalog/seo/generate_category_product_rewrites 1
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
        $category->setStoreId($secondStoreId);
        $category->setUrlKey($urlKeySecondStore);
        $categoryResource = $this->categoryResourceFactory->create();
        $categoryResource->save($category);
        $urlRewriteItems = $this->getCategoryRewriteCollection($categoryId)->getItems();
        foreach ($urlRewriteItems as $item) {
            $item->getData('store_id') == $secondStoreId
                ? $this->assertEquals($urlKeySecondStore . $urlSuffix, $item->getRequestPath())
                : $this->assertEquals($urlKeyFirstStore . $urlSuffix, $item->getRequestPath());
        }
    }

    /**
     * Get products url rewrites collection referred to categories
     *
     * @param string|array $categoryId
     * @return UrlRewriteCollection
     */
    private function getProductRewriteCollection($categoryId): UrlRewriteCollection
    {
        $condition = is_array($categoryId) ? ['in' => $categoryId] : $categoryId;
        $productRewriteCollection = $this->urlRewriteCollectionFactory->create();
        $productRewriteCollection
            ->join(
                ['p' => Product::TABLE_NAME],
                'main_table.url_rewrite_id = p.url_rewrite_id',
                'category_id'
            )
            ->addFieldToFilter('category_id', $condition)
            ->addFieldToFilter(UrlRewrite::ENTITY_TYPE, ['eq' => DataProductUrlRewriteDatabaseMap::ENTITY_TYPE]);

        return $productRewriteCollection;
    }

    /**
     * Retrieve all rewrite ids
     *
     * @return array
     */
    private function getAllRewriteIds(): array
    {
        $urlRewriteCollection = $this->urlRewriteCollectionFactory->create();

        return $urlRewriteCollection->getAllIds();
    }

    /**
     * Get category url rewrites collection
     *
     * @param string|array $categoryId
     * @return UrlRewriteCollection
     */
    private function getCategoryRewriteCollection($categoryId): UrlRewriteCollection
    {
        $condition = is_array($categoryId) ? ['in' => $categoryId] : $categoryId;
        $categoryRewriteCollection = $this->urlRewriteCollectionFactory->create();
        $categoryRewriteCollection->addFieldToFilter(UrlRewrite::ENTITY_ID, $condition)
            ->addFieldToFilter(UrlRewrite::ENTITY_TYPE, ['eq' => DataCategoryUrlRewriteDatabaseMap::ENTITY_TYPE]);

        return $categoryRewriteCollection;
    }

    /**
     * Check that actual data contains of expected values
     *
     * @param UrlRewriteCollection $collection
     * @param array $expectedData
     * @return void
     */
    private function assertRewrites(UrlRewriteCollection $collection, array $expectedData): void
    {
        $collectionItems = $collection->toArray()['items'];
        foreach ($collectionItems as $item) {
            $found = false;
            foreach ($expectedData as $expectedItem) {
                $found = array_intersect_assoc($item, $expectedItem) == $expectedItem;
                if ($found) {
                    break;
                }
            }
            $this->assertTrue($found, 'The actual data does not contains of expected values');
        }
    }
}
