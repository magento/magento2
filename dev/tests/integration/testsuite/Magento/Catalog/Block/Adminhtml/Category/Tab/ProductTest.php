<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Block\Adminhtml\Category\Tab;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Collection\AbstractCollection;
use Magento\Catalog\Test\Fixture\AssignProducts as AssignProductsFixture;
use Magento\Catalog\Test\Fixture\Category as CategoryFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Test\Fixture\Store as StoreFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Checks grid data on the tab 'Products in Category' category view page.
 *
 * @see \Magento\Catalog\Block\Adminhtml\Category\Tab\Product
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class ProductTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var LayoutInterface */
    private $layout;

    /** @var CategoryRepositoryInterface */
    private $categoryRepository;

    /** @var Registry */
    private $registry;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->layout = $this->objectManager->get(LayoutInterface::class);
        $this->categoryRepository = $this->objectManager->get(CategoryRepositoryInterface::class);
        $this->registry = $this->objectManager->get(Registry::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->registry->unregister('category');

        parent::tearDown();
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/category_with_two_products.php
     * @magentoDataFixture Magento/Catalog/_files/product_associated.php
     * @magentoDataFixture Magento/Catalog/_files/simple_product_disabled.php
     * @dataProvider optionsFilterProvider
     * @param string $filterColumn
     * @param int $categoryId
     * @param int $storeId
     * @param array $items
     * @return void
     */
    public function testFilterProductInCategory(string $filterColumn, int $categoryId, int $storeId, array $items): void
    {
        $collection = $this->filterProductInGrid($filterColumn, $categoryId, $storeId);
        $this->assertCount(count($items), $collection->getItems());
        foreach ($items as $item) {
            $this->assertNotNull($collection->getItemByColumnValue(ProductInterface::SKU, $item));
        }
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/category.php
     * @return void
     */
    public function testEmptyProductIdColumnFilter(): void
    {
        $this->assertCount(0, $this->filterProductInGrid('in_category=1', 333, 1)->getItems());
    }

    /**
     * Different variations for filter test.
     *
     * @return array
     */
    public static function optionsFilterProvider(): array
    {
        return [
            'filter_yes' => [
                'filterColumn' => 'in_category=1',
                'categoryId' => 333,
                'storeId' => 1,
                'items' => [
                    'simple333',
                    'simple2',
                ],
            ],
            'filter_no' => [
                'filterColumn' => 'in_category=0',
                'categoryId' => 333,
                'storeId' => 1,
                'items' => [
                    'product_disabled',
                    'simple',
                ],
            ],
            'filter_any' => [
                'filterColumn' => "",
                'categoryId' => 333,
                'storeId' => 1,
                'items' => [
                    'product_disabled',
                    'simple333',
                    'simple2',
                    'simple',
                ],
            ],
            'flag_status' => [
                'filterColumn' => 'status=1',
                'categoryId' => 333,
                'storeId' => 1,
                'items' => [
                    'simple333',
                    'simple2',
                    'simple',
                ],
            ],
        ];
    }

    /**
     * @dataProvider sortingOptionsProvider
     * @param string $sortField
     * @param string $sortDirection
     * @param string $store
     * @param array $items
     * @return void
     */
    #[
        DataFixture(CategoryFixture::class, ['name' => 'CategoryA'], as: 'category'),
        DataFixture(
            ProductFixture::class,
            ['name' => 'ProductA','sku' => 'ProductA'],
            as: 'productA'
        ),
        DataFixture(
            ProductFixture::class,
            ['name' => 'ProductB','sku' => 'ProductB'],
            as: 'productB'
        ),
        DataFixture(
            AssignProductsFixture::class,
            ['products' => ['$productA$', '$productB$'], 'category' => '$category$'],
            as: 'assignProducts'
        ),
        DataFixture(StoreFixture::class, ['code' => 'second_store'], as: 'store2'),
    ]
    public function testSortProductsInCategory(
        string $sortField,
        string $sortDirection,
        string $store,
        array $items
    ): void {
        $fixtures = DataFixtureStorageManager::getStorage();
        $fixtures->get('productA')->addAttributeUpdate('name', 'SimpleProductA', $fixtures->get('store2')->getId());
        $fixtures->get('productB')->addAttributeUpdate('name', 'SimpleProductB', $fixtures->get('store2')->getId());
        $collection = $this->sortProductsInGrid(
            $sortField,
            $sortDirection,
            (int)$fixtures->get('category')->getId(),
            $store === 'default' ? 1 : (int)$fixtures->get($store)->getId(),
        );
        $productNames = [];
        foreach ($collection as $product) {
            $productNames[] = $product->getName();
        }
        $this->assertEquals($productNames, $items);
    }

    /**
     * Different variations for sorting test.
     *
     * @return array
     */
    public static function sortingOptionsProvider(): array
    {
        return [
            'default_store_sort_name_asc' => [
                'sortField' => 'name',
                'sortDirection' => 'asc',
                'store' => 'default',
                'items' => [
                    'ProductA',
                    'ProductB',
                ],
            ],
            'default_store_sort_name_desc' => [
                'sortField' => 'name',
                'sortDirection' => 'desc',
                'store' => 'default',
                'items' => [
                    'ProductB',
                    'ProductA',
                ],
            ],
            'second_store_sort_name_asc' => [
                'sortField' => 'name',
                'sortDirection' => 'asc',
                'store' => 'store2',
                'items' => [
                    'SimpleProductA',
                    'SimpleProductB',
                ],
            ],
            'second_store_sort_name_desc' => [
                'sortField' => 'name',
                'sortDirection' => 'desc',
                'store' => 'store2',
                'items' => [
                    'SimpleProductB',
                    'SimpleProductA',
                ],
            ],
        ];
    }

    /**
     * Filter product in grid
     *
     * @param string $filterOption
     * @param int $categoryId
     * @param int $storeId
     * @return AbstractCollection
     */
    private function filterProductInGrid(string $filterOption, int $categoryId, int $storeId): AbstractCollection
    {
        $this->registerCategory($this->categoryRepository->get($categoryId));
        $block = $this->layout->createBlock(Product::class);
        $block->getRequest()->setParams([
            'id' => $categoryId,
            'filter' => base64_encode($filterOption),
            'store' => $storeId,
        ]);
        $block->toHtml();

        return $block->getCollection();
    }

    /**
     * Register category in registry
     *
     * @param CategoryInterface $category
     * @return void
     */
    private function registerCategory(CategoryInterface $category): void
    {
        $this->registry->unregister('category');
        $this->registry->register('category', $category);
    }

    /**
     * Sort products in grid
     *
     * @param string $sortField
     * @param string $sortDirection
     * @param int $categoryId
     * @param int $storeId
     * @return AbstractCollection
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function sortProductsInGrid(
        string $sortField,
        string $sortDirection,
        int $categoryId,
        int $storeId
    ): AbstractCollection {
        $this->registerCategory($this->categoryRepository->get($categoryId));
        $block = $this->layout->createBlock(Product::class);
        $block->getRequest()->setParams([
            'id' => $categoryId,
            'sort' => $sortField,
            'dir' => $sortDirection,
            'store' => $storeId,
        ]);
        $block->toHtml();

        return $block->getCollection();
    }
}
