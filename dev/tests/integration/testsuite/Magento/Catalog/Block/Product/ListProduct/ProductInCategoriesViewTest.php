<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Block\Product\ListProduct;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Checks products displaying on category page
 *
 * @magentoDbIsolation disabled
 * @magentoAppIsolation enabled
 */
class ProductInCategoriesViewTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var ListProduct */
    private $block;

    /** @var CategoryRepositoryInterface */
    private $categoryRepository;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var StoreManagerInterface */
    private $storeManager;

    /** @var LayoutInterface */
    private $layout;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->categoryRepository = $this->objectManager->create(CategoryRepositoryInterface::class);
        $this->productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $this->layout = $this->objectManager->get(LayoutInterface::class);
        $this->block = $this->layout->createBlock(ListProduct::class);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/category_with_two_products.php
     * @dataProvider productDataProvider
     * @param array $data
     * @return void
     */
    public function testCategoryProductView(array $data): void
    {
        $this->updateProduct($data['sku'], $data);
        $collection = $this->getCategoryProductCollection(333);

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
     * @dataProvider productVisibilityProvider
     * @param array $data
     * @return void
     */
    public function testCategoryProductVisibility(array $data): void
    {
        $this->updateProduct($data['data']['sku'], $data['data']);
        $collection = $this->getCategoryProductCollection(333);

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
     * @magentoDataFixture Magento/Catalog/_files/category_tree.php
     * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
     * @return void
     */
    public function testAnchorCategoryProductVisibility(): void
    {
        $this->updateCategoryIsAnchor(400, true);
        $this->assignProductCategories('simple2', [402]);
        $parentCategoryCollection = $this->getCategoryProductCollection(400);
        $childCategoryCollection = $this->getCategoryProductCollection(402, true);

        $this->assertEquals(1, $parentCategoryCollection->getSize());
        $this->assertEquals(
            $childCategoryCollection->getAllIds(),
            $parentCategoryCollection->getAllIds()
        );
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/category_tree.php
     * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
     * @return void
     */
    public function testNonAnchorCategoryProductVisibility(): void
    {
        $this->updateCategoryIsAnchor(400, false);
        $this->assignProductCategories('simple2', [402]);
        $parentCategoryCollectionSize = $this->getCategoryProductCollection(400)->getSize();
        $childCategoryCollectionSize = $this->getCategoryProductCollection(402, true)->getSize();

        $this->assertEquals(0, $parentCategoryCollectionSize);
        $this->assertEquals(1, $childCategoryCollectionSize);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/products_with_websites_and_stores.php
     * @magentoDataFixture Magento/Catalog/_files/category.php
     * @return void
     */
    public function testCategoryProductViewOnMultiWebsite(): void
    {
        $this->assignProductCategories(['simple-1', 'simple-2'], [3, 333]);
        $store = $this->storeManager->getStore('fixture_second_store');
        $currentStore = $this->storeManager->getStore();

        try {
            $this->storeManager->setCurrentStore($store->getId());
            $collection = $this->block->getLoadedProductCollection();
            $collectionSize = $collection->getSize();
        } finally {
            $this->storeManager->setCurrentStore($currentStore);
        }

        $this->assertEquals(1, $collectionSize);
        $this->assertNull($collection->getItemByColumnValue('sku', 'simple-1'));
        $this->assertNotNull($collection->getItemByColumnValue('sku', 'simple-2'));
    }

    /**
     * Set categories to the products
     *
     * @param string|array $sku
     * @param $categoryIds
     * @return void
     */
    private function assignProductCategories($sku, array $categoryIds): void
    {
        $skus = !is_array($sku) ? [$sku] : $sku;
        foreach ($skus as $sku) {
            $product = $this->productRepository->get($sku);
            $product->setCategoryIds($categoryIds);
            $this->productRepository->save($product);
        }
    }

    /**
     * Update product
     *
     * @param string $sku
     * @param array $data
     * @return void
     */
    private function updateProduct(string $sku, array $data): void
    {
        $product = $this->productRepository->get($sku);
        $product->addData($data);
        $this->productRepository->save($product);
    }

    /**
     * Returns category collection by category id
     *
     * @param int $categoryId
     * @param bool $refreshBlock
     * @return AbstractCollection
     */
    private function getCategoryProductCollection(int $categoryId, bool $refreshBlock = false): AbstractCollection
    {
        $block = $this->getListingBlock($refreshBlock);
        $block->getLayer()->setCurrentCategory($categoryId);

        return $block->getLoadedProductCollection();
    }

    /**
     * Update is_anchor attribute of the category
     *
     * @param int $categoryId
     * @param bool $isAnchor
     * @return void
     */
    private function updateCategoryIsAnchor(int $categoryId, bool $isAnchor): void
    {
        $category = $this->categoryRepository->get($categoryId);
        $category->setIsAnchor($isAnchor);
        $this->categoryRepository->save($category);
    }

    /**
     * Get product listing block
     *
     * @param bool $refresh
     * @return ListProduct
     */
    private function getListingBlock(bool $refresh): ListProduct
    {
        if ($refresh) {
            $this->block = $this->layout->createBlock(ListProduct::class);
        }

        return $this->block;
    }
}
