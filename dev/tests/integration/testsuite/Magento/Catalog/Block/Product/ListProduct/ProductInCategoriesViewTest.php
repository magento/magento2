<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Block\Product\ListProduct;

use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Checks products displaying on category page
 *
 * @magentoDbIsolation disabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AbstractTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var ListProduct */
    private $block;

    /** @var CategoryRepositoryInterface */
    private $categoryRepository;

    /** @var Registry */
    private $registry;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var CategoryLinkManagementInterface */
    private $categoryLinkManagement;

    /** @var StoreManagerInterface */
    private $storeManager;

    /** @var StoreRepositoryInterface */
    private $storeRepository;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->categoryRepository = $this->objectManager->create(CategoryRepositoryInterface::class);
        $this->block = $this->objectManager->get(LayoutInterface::class)->createBlock(ListProduct::class);
        $this->registry = $this->objectManager->get(Registry::class);
        $this->productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $this->categoryLinkManagement = $this->objectManager->create(CategoryLinkManagementInterface::class);
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $this->storeRepository = $this->objectManager->create(StoreRepositoryInterface::class);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/category_with_two_products.php
     * @magentoAppIsolation enabled
     * @dataProvider productDataProvider
     * @param array $data
     * @return void
     */
    public function testCategoryProductView(array $data): void
    {
        $collection = $this->processCategoryViewTest($data['sku'], $data);

        $this->assertEquals(1, $collection->getSize());
        $this->assertEquals('simple333', $collection->getFirstItem()->getSku());
    }

    /**
     * @return array
     */
    public function productDataProvider(): array
    {
        return [
            'simple_product_enabled_disabled' => [
                [
                    'sku' => 'simple2',
                    'status' => 0,
                ],
            ],
            'simple_product_in_stock_out_of_stock' => [
                [
                    'sku' => 'simple2',
                    'stock_data' => [
                        'use_config_manage_stock' => 1,
                        'qty' => 0,
                        'is_qty_decimal' => 0,
                        'is_in_stock' => 0,
                    ],
                ],
            ],
        ];
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/category_product.php
     * @magentoAppIsolation enabled
     * @dataProvider productVisibilityProvider
     * @param array $data
     * @return void
     */
    public function testCategoryProductVisibilityTest(array $data): void
    {
        $collection = $this->processCategoryViewTest($data['data']['sku'], $data['data']);

        $this->assertEquals($data['expected_count'], $collection->getSize());
    }

    /**
     * @return array
     */
    public function productVisibilityProvider(): array
    {
        return [
            'not_visible' => [
                [
                    'data' => [
                        'sku' => 'simple333',
                        'visibility' => Visibility::VISIBILITY_NOT_VISIBLE,
                    ],
                    'expected_count' => 0,
                ],

            ],
            'catalog_search' => [
                [
                    'data' => [
                        'sku' => 'simple333',
                        'visibility' => Visibility::VISIBILITY_BOTH,
                    ],
                    'expected_count' => 1,
                ],

            ],
            'search' => [
                [
                    'data' => [
                        'sku' => 'simple333',
                        'visibility' => Visibility::VISIBILITY_IN_SEARCH,
                    ],
                    'expected_count' => 0,
                ],
            ],
            'catalog' => [
                [
                    'data' => [
                        'sku' => 'simple333',
                        'visibility' => Visibility::VISIBILITY_IN_CATALOG,
                    ],
                    'expected_count' => 1,
                ],
            ],
        ];
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/category_tree.php
     * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
     * @return void
     */
    public function testAnchorCategoryProductVisibility(): void
    {
        $collections = $this->processAnchorTest(true);

        $this->assertEquals(1, $collections['parent_collection']->getSize());
        $this->assertEquals(
            $collections['child_collection']->getAllIds(),
            $collections['parent_collection']->getAllIds()
        );
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/category_tree.php
     * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
     * @return void
     */
    public function testNonAnchorCategoryProductVisibility(): void
    {
        $collections = $this->processAnchorTest(false);

        $this->assertCount(0, $collections['parent_collection']);
        $this->assertCount(1, $collections['child_collection']);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/products_with_websites_and_stores.php
     * @magentoDataFixture Magento/Catalog/_files/category.php
     * @return void
     */
    public function testCategoryProductViewOnMultiWebsite(): void
    {
        $this->setCategoriesToProducts(['simple-1', 'simple-2']);
        $store = $this->storeRepository->get('fixture_second_store');
        $this->storeManager->setCurrentStore($store->getId());
        $category = $this->categoryRepository->get(333);
        $this->registerCategory($category);
        $collection = $this->block->getLoadedProductCollection();
        $this->assertEquals(1, $collection->getSize());
        $this->assertEquals('simple-2', $collection->getFirstItem()->getSku());
    }

    /**
     * Set categories to the products
     *
     * @param array $skus
     * @return void
     */
    private function setCategoriesToProducts(array $skus): void
    {
        foreach ($skus as $sku) {
            $product = $this->productRepository->get($sku);
            $product->setCategoryIds([2, 333]);
            $this->productRepository->save($product);
        }
    }

    /**
     * Proccess for anchor and non anchor category test
     *
     * @param bool $isAnchor
     * @return array
     */
    private function processAnchorTest(bool $isAnchor): array
    {
        $category = $this->categoryRepository->get(400);
        $category->setIsAnchor($isAnchor);
        $this->categoryRepository->save($category);
        $childCategory = $this->categoryRepository->get(402);
        $this->categoryLinkManagement->assignProductToCategories('simple2', [$childCategory->getId()]);
        $this->registerCategory($category);
        $parentCategoryCollection = $this->block->getLoadedProductCollection();
        $this->objectManager->removeSharedInstance(Resolver::class);
        $this->objectManager->removeSharedInstance(Layer::class);
        $this->registerCategory($childCategory);
        $newBlock = $this->objectManager->get(LayoutInterface::class)->createBlock(ListProduct::class);
        $childCategoryCollection = $newBlock->getLoadedProductCollection();

        return [
            'parent_collection' => $parentCategoryCollection,
            'child_collection' => $childCategoryCollection,
        ];
    }

    /**
     * Proccess category view test
     *
     * @param string $sku
     * @param array $data
     * @return AbstractCollection
     */
    private function processCategoryViewTest(string $sku, array $data): AbstractCollection
    {
        $product = $this->productRepository->get($sku);
        $product->addData($data);
        $this->productRepository->save($product);
        $category = $this->categoryRepository->get(333);
        $this->registerCategory($category);

        return $this->block->getLoadedProductCollection();
    }

    /**
     * Register current category
     *
     * @param CategoryInterface $category
     * @retun void
     */
    private function registerCategory(CategoryInterface $category): void
    {
        $this->registry->unregister('current_category');
        $this->registry->register('current_category', $category);
    }
}
