<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Block\Product\ListProduct;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Block\Product\ProductList\Toolbar;
use Magento\Catalog\Model\Config;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\CatalogInventory\Model\Configuration;
use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Tests for products sorting on category page.
 *
 * @magentoDbIsolation disabled
 * @magentoAppIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SortingTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ListProduct
     */
    private $block;

    /**
     * @var CollectionFactory
     */
    private $categoryCollectionFactory;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @var MutableScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $this->layout = $this->objectManager->get(LayoutInterface::class);
        $this->layout->createBlock(Toolbar::class, 'product_list_toolbar');
        $this->block = $this->layout->createBlock(ListProduct::class)->setToolbarBlockName('product_list_toolbar');
        $this->categoryCollectionFactory = $this->objectManager->get(CollectionFactory::class);
        $this->categoryRepository = $this->objectManager->get(CategoryRepositoryInterface::class);
        $this->scopeConfig = $this->objectManager->get(MutableScopeConfigInterface::class);
        $this->productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        parent::setUp();
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/products_with_not_empty_layered_navigation_attribute.php
     * @dataProvider productListSortOrderDataProvider
     * @param string $sortBy
     * @param string $direction
     * @param array $expectation
     * @param string|null $incompleteReason
     * @return void
     */
    public function testProductListSortOrder(
        string $sortBy,
        string $direction,
        array $expectation,
        string $incompleteReason = null
    ): void {
        if ($incompleteReason) {
            $this->markTestIncomplete($incompleteReason);
        }
        $category = $this->updateCategorySortBy('Category 1', Store::DEFAULT_STORE_ID, $sortBy);
        $this->renderBlock($category, $direction);
        $this->assertBlockSorting($sortBy, $expectation);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/products_with_not_empty_layered_navigation_attribute.php
     * @dataProvider productListSortOrderDataProvider
     * @param string $sortBy
     * @param string $direction
     * @param array $expectation
     * @param string|null $incompleteReason
     * @return void
     */
    public function testProductListSortOrderWithConfig(
        string $sortBy,
        string $direction,
        array $expectation,
        string $incompleteReason = null
    ): void {
        if ($incompleteReason) {
            $this->markTestIncomplete($incompleteReason);
        }
        $this->assertProductListSortOrderWithConfig($sortBy, $direction, $expectation);
    }

    /**
     * @return array
     */
    public function productListSortOrderDataProvider(): array
    {
        return [
            'default_order_price_asc' => [
                'sort' => 'price',
                'direction' => 'asc',
                'expectation' => ['simple1', 'simple2', 'simple3'],
            ],
            'default_order_price_desc' => [
                'sort' => 'price',
                'direction' => 'desc',
                'expectation' => ['simple3', 'simple2', 'simple1'],
            ],
            'default_order_position_asc' => [
                'sort' => 'position',
                'direction' => 'asc',
                'expectation' => ['simple1', 'simple2', 'simple3'],
            ],
            'default_order_position_desc' => [
                'sort' => 'position',
                'direction' => 'desc',
                'expectation' => ['simple3', 'simple2', 'simple1'],
            ],
            'default_order_name_asc' => [
                'sort' => 'name',
                'direction' => 'asc',
                'expectation' => ['simple1', 'simple2', 'simple3'],
            ],
            'default_order_name_desc' => [
                'sort' => 'name',
                'direction' => 'desc',
                'expectation' => ['simple3', 'simple2', 'simple1'],
            ],
            'default_order_custom_attribute_asc' => [
                'sort' => 'test_configurable',
                'direction' => 'asc',
                'expectation' => ['simple1', 'simple3', 'simple2'],
                'incomplete_reason' => 'MC-33825:'
                    . 'Stabilize skipped test cases for Integration SortingTest with elasticsearch',
            ],
            'default_order_custom_attribute_desc' => [
                'sort' => 'test_configurable',
                'direction' => 'desc',
                'expectation' => ['simple3', 'simple2', 'simple1'],
                'incomplete_reason' => 'MC-33825:'
                    . 'Stabilize skipped test cases for Integration SortingTest with elasticsearch',
            ],
        ];
    }

    /**
     * @magentoDataFixture Magento/Store/_files/second_store.php
     * @magentoDataFixture Magento/Catalog/_files/products_with_not_empty_layered_navigation_attribute.php
     * @dataProvider productListSortOrderDataProviderOnStoreView
     * @param string $sortBy
     * @param string $direction
     * @param array $expectation
     * @param string $defaultSortBy
     * @param string|null $incompleteReason
     * @return void
     */
    public function testProductListSortOrderOnStoreView(
        string $sortBy,
        string $direction,
        array $expectation,
        string $defaultSortBy,
        string $incompleteReason = null
    ): void {
        if ($incompleteReason) {
            $this->markTestIncomplete($incompleteReason);
        }
        $secondStoreId = (int)$this->storeManager->getStore('fixture_second_store')->getId();
        $this->updateCategorySortBy('Category 1', Store::DEFAULT_STORE_ID, $defaultSortBy);
        $category = $this->updateCategorySortBy('Category 1', $secondStoreId, $sortBy);
        $this->renderBlock($category, $direction);
        $this->assertBlockSorting($sortBy, $expectation);
    }

    /**
     * @magentoDataFixture Magento/Store/_files/second_store.php
     * @magentoDataFixture Magento/Catalog/_files/products_with_not_empty_layered_navigation_attribute.php
     * @dataProvider productListSortOrderDataProviderOnStoreView
     * @param string $sortBy
     * @param string $direction
     * @param array $expectation
     * @param string $defaultSortBy
     * @param string|null $incompleteReason,
     * @return void
     */
    public function testProductListSortOrderWithConfigOnStoreView(
        string $sortBy,
        string $direction,
        array $expectation,
        string $defaultSortBy,
        string $incompleteReason = null
    ): void {
        if ($incompleteReason) {
            $this->markTestIncomplete($incompleteReason);
        }
        $this->objectManager->removeSharedInstance(Config::class);
        $secondStoreId = (int)$this->storeManager->getStore('fixture_second_store')->getId();
        $this->scopeConfig->setValue(
            Config::XML_PATH_LIST_DEFAULT_SORT_BY,
            $defaultSortBy,
            ScopeInterface::SCOPE_STORE,
            Store::DEFAULT_STORE_ID
        );
        $this->scopeConfig->setValue(
            Config::XML_PATH_LIST_DEFAULT_SORT_BY,
            $sortBy,
            ScopeInterface::SCOPE_STORE,
            'fixture_second_store'
        );
        $this->updateCategorySortBy('Category 1', Store::DEFAULT_STORE_ID, null);
        $category = $this->updateCategorySortBy('Category 1', $secondStoreId, null);
        $this->renderBlock($category, $direction);
        $this->assertBlockSorting($sortBy, $expectation);
    }

    /**
     * @return array
     */
    public function productListSortOrderDataProviderOnStoreView(): array
    {
        return [
            'default_order_price_asc' => [
                'sort' => 'price',
                'direction' => 'asc',
                'expectation' => ['simple1', 'simple2', 'simple3'],
                'default_sort' => 'position'
            ],
            'default_order_price_desc' => [
                'sort' => 'price',
                'direction' => 'desc',
                'expectation' => ['simple3', 'simple2', 'simple1'],
                'default_sort' => 'position'
            ],
            'default_order_position_asc' => [
                'sort' => 'position',
                'direction' => 'asc',
                'expectation' => ['simple1', 'simple2', 'simple3'],
                'default_sort' => 'price'
            ],
            'default_order_position_desc' => [
                'sort' => 'position',
                'direction' => 'desc',
                'expectation' => ['simple3', 'simple2', 'simple1'],
                'default_sort' => 'price'
            ],
            'default_order_name_asc' => [
                'sort' => 'name',
                'direction' => 'asc',
                'expectation' => ['simple1', 'simple2', 'simple3'],
                'default_sort' => 'price'
            ],
            'default_order_name_desc' => [
                'sort' => 'name',
                'direction' => 'desc',
                'expectation' => ['simple3', 'simple2', 'simple1'],
                'default_sort' => 'price'
            ],
            'default_order_custom_attribute_asc' => [
                'sort' => 'test_configurable',
                'direction' => 'asc',
                'expectation' => ['simple1', 'simple3', 'simple2'],
                'default_sort' => 'price',
                'incomplete_reason' => 'MC-33825:'
                    . 'Stabilize skipped test cases for Integration SortingTest with elasticsearch',
            ],
            'default_order_custom_attribute_desc' => [
                'sort' => 'test_configurable',
                'direction' => 'desc',
                'expectation' => ['simple3', 'simple2', 'simple1'],
            'default_sort' => 'price',
                'incomplete_reason' => 'MC-33825:'
                    . 'Stabilize skipped test cases for Integration SortingTest with elasticsearch',
            ],
        ];
    }

    /**
     * Renders block to apply sorting.
     *
     * @param CategoryInterface $category
     * @param string $direction
     * @return void
     */
    private function renderBlock(CategoryInterface $category, string $direction): void
    {
        $this->block->getLayer()->setCurrentCategory($category);
        $this->block->setDefaultDirection($direction);
        $this->block->toHtml();
    }

    /**
     * Checks product list block correct sorting.
     *
     * @param string $sortBy
     * @param array $expectation
     * @return void
     */
    private function assertBlockSorting(string $sortBy, array $expectation): void
    {
        $this->assertArrayHasKey($sortBy, $this->block->getAvailableOrders());
        $this->assertEquals($sortBy, $this->block->getSortBy());
        $this->assertEquals($expectation, $this->block->getLoadedProductCollection()->getColumnValues('sku'));
    }

    /**
     * Loads category by name.
     *
     * @param string $categoryName
     * @param int $storeId
     * @return CategoryInterface
     */
    private function loadCategory(string $categoryName, int $storeId): CategoryInterface
    {
        /** @var Collection $categoryCollection */
        $categoryCollection = $this->categoryCollectionFactory->create();
        $categoryId = $categoryCollection->setStoreId($storeId)
            ->addAttributeToFilter(CategoryInterface::KEY_NAME, $categoryName)
            ->setPageSize(1)
            ->getFirstItem()
            ->getId();

        return $this->categoryRepository->get($categoryId, $storeId);
    }

    /**
     * Updates category default sort by field.
     *
     * @param string $categoryName
     * @param int $storeId
     * @param string|null $sortBy
     * @return CategoryInterface
     */
    private function updateCategorySortBy(
        string $categoryName,
        int $storeId,
        ?string $sortBy
    ): CategoryInterface {
        $oldStoreId = $this->storeManager->getStore()->getId();
        $this->storeManager->setCurrentStore($storeId);
        $category = $this->loadCategory($categoryName, $storeId);
        $category->addData(['default_sort_by' => $sortBy]);
        $category = $this->categoryRepository->save($category);
        $this->storeManager->setCurrentStore($oldStoreId);

        return $category;
    }

    /**
     * Test product list ordered by price with out-of-stock configurable product options with elasticsearch engine
     *
     * @magentoDataFixture Magento/Catalog/_files/products_with_not_empty_layered_navigation_attribute.php
     * @magentoDataFixture Magento/Framework/Search/_files/product_configurable_with_out-of-stock_child.php
     * @magentoConfigFixture current_store cataloginventory/options/show_out_of_stock 1
     * @magentoConfigFixture default/catalog/search/engine elasticsearch7
     * @dataProvider productListWithOutOfStockSortOrderDataProvider
     * @param string $sortBy
     * @param string $direction
     * @param array $expected
     * @return void
     */
    public function testProductListOutOfStockSortOrderWithElasticsearch(
        string $sortBy,
        string $direction,
        array $expected
    ): void {
        $this->markTestSkipped('MC-40449: ListProduct\SortingTest failure on 2.4-develop');
        $this->assertProductListSortOrderWithConfig($sortBy, $direction, $expected);
    }

    /**
     * Test product list ordered by price with out-of-stock configurable product options with mysql search engine
     *
     * @magentoDataFixture Magento/Catalog/_files/products_with_not_empty_layered_navigation_attribute.php
     * @magentoDataFixture Magento/Framework/Search/_files/product_configurable_with_out-of-stock_child.php
     * @magentoConfigFixture current_store cataloginventory/options/show_out_of_stock 1
     * @magentoConfigFixture default/catalog/search/engine mysql
     * @dataProvider productListWithOutOfStockSortOrderDataProvider
     * @param string $sortBy
     * @param string $direction
     * @param array $expected
     * @return void
     */
    public function testProductListOutOfStockSortOrderWithMysql(
        string $sortBy,
        string $direction,
        array $expected
    ): void {
        $this->markTestSkipped('MC-40449: ListProduct\SortingTest failure on 2.4-develop');
        $this->assertProductListSortOrderWithConfig($sortBy, $direction, $expected);
    }

    /**
     * Product list with out-of-stock sort order data provider
     *
     * @return array
     */
    public function productListWithOutOfStockSortOrderDataProvider(): array
    {
        return [
            'default_order_price_asc' => [
                'sort' => 'price',
                'direction' => Collection::SORT_ORDER_ASC,
                'expectation' => ['simple1', 'simple2', 'simple3', 'configurable'],
            ],
            'default_order_price_desc' => [
                'sort' => 'price',
                'direction' => Collection::SORT_ORDER_DESC,
                'expectation' => ['configurable', 'simple3', 'simple2', 'simple1'],
            ],
        ];
    }

    /**
     * Assert product list order
     *
     * @param string $sortBy
     * @param string $direction
     * @param array $expected
     * @return void
     */
    private function assertProductListSortOrderWithConfig(string $sortBy, string $direction, array $expected): void
    {
        $this->objectManager->removeSharedInstance(Config::class);
        $this->scopeConfig->setValue(
            Config::XML_PATH_LIST_DEFAULT_SORT_BY,
            $sortBy,
            ScopeInterface::SCOPE_STORE,
            Store::DEFAULT_STORE_ID
        );
        $category = $this->updateCategorySortBy('Category 1', Store::DEFAULT_STORE_ID, null);
        $this->renderBlock($category, $direction);
        $this->assertBlockSorting($sortBy, $expected);
    }

    /**
     * Test product list ordered by product name with out-of-stock configurable product options.
     *
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_product_show_out_of_stock.php
     * @dataProvider productListWithShowOutOfStockSortOrderDataProvider
     * @param string $sortBy
     * @param string $direction
     * @param array $expected
     * @return void
     */
    public function testProductListOutOfStockSortOrderBySaleability(
        string $sortBy,
        string $direction,
        array $expected
    ): void {
        $this->scopeConfig->setValue(
            Config::XML_PATH_LIST_DEFAULT_SORT_BY,
            $sortBy,
            ScopeInterface::SCOPE_STORE,
            Store::DEFAULT_STORE_ID
        );
        $this->scopeConfig->setValue(
            Configuration::XML_PATH_SHOW_OUT_OF_STOCK,
            1,
            ScopeInterface::SCOPE_STORE,
            \Magento\Framework\App\ScopeInterface::SCOPE_DEFAULT
        );

        /** @var CategoryInterface $category */
        $category = $this->categoryRepository->get(333);
        if ($category->getId()) {
            $category->setAvailableSortBy(['position', 'name', 'price']);
            $category->addData(['available_sort_by' => 'position,name,price']);
            $category->setDefaultSortBy($sortBy);
            $this->categoryRepository->save($category);
        }

        foreach (['simple_41', 'simple_42', 'configurable_12345'] as $sku) {
            $product = $this->productRepository->get($sku);
            $product->setStockData(['is_in_stock' => 0]);
            $this->productRepository->save($product);
        }
        $this->renderBlock($category, $direction);
        $this->assertBlockSorting($sortBy, $expected);
    }

    /**
     * Product list with out-of-stock sort order data provider
     *
     * @return array
     */
    public function productListWithShowOutOfStockSortOrderDataProvider(): array
    {
        return [
            'default_order_position_asc' => [
                'sort' => 'position',
                'direction' => 'ASC',
                'expectation' => ['simple2', 'simple1', 'configurable', 'configurable_12345'],
            ],
            'default_order_position_desc' => [
                'sort' => 'position',
                'direction' => 'DESC',
                'expectation' => ['simple2', 'simple1', 'configurable', 'configurable_12345'],
            ],
            'default_order_price_asc' => [
                'sort' => 'price',
                'direction' => 'ASC',
                'expectation' => ['simple1', 'simple2', 'configurable', 'configurable_12345'],
            ],
            'default_order_price_desc' => [
                'sort' => 'price',
                'direction' => 'DESC',
                'expectation' => ['configurable', 'simple2', 'simple1', 'configurable_12345'],
            ],
            'default_order_name_asc' => [
                'sort' => 'name',
                'direction' => 'ASC',
                'expectation' => ['configurable', 'simple1', 'simple2', 'configurable_12345'],
            ],
            'default_order_name_desc' => [
                'sort' => 'name',
                'direction' => 'DESC',
                'expectation' => ['simple2', 'simple1', 'configurable', 'configurable_12345'],
            ],
        ];
    }
}
