<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Block\Product\ListProduct;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Block\Product\ProductList\Toolbar;
use Magento\Catalog\Model\Config;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
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
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $this->layout = $this->objectManager->get(LayoutInterface::class);
        $this->layout->createBlock(Toolbar::class, 'product_list_toolbar');
        $this->block = $this->layout->createBlock(ListProduct::class)->setToolbarBlockName('product_list_toolbar');
        $this->categoryCollectionFactory = $this->objectManager->get(CollectionFactory::class);
        $this->categoryRepository = $this->objectManager->get(CategoryRepositoryInterface::class);
        $this->scopeConfig = $this->objectManager->get(MutableScopeConfigInterface::class);
        parent::setUp();
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/products_with_not_empty_layered_navigation_attribute.php
     * @dataProvider productListSortOrderDataProvider
     * @param string $sort
     * @param string $direction
     * @param array $expectation
     * @return void
     */
    public function testProductListSortOrder(string $sort, string $direction, array $expectation): void
    {
        $category = $this->updateCategorySortBy('Category 1', $sort, Store::DEFAULT_STORE_ID);
        $this->callBlock($direction, $category);
        $this->assertBlockSorting($sort, $expectation);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/products_with_not_empty_layered_navigation_attribute.php
     * @dataProvider productListSortOrderDataProvider
     * @param string $sort
     * @param string $direction
     * @param array $expectation
     * @return void
     */
    public function testProductListSortOrderWithConfig(string $sort, string $direction, array $expectation): void
    {
        $this->objectManager->removeSharedInstance(Config::class);
        $this->scopeConfig->setValue(
            Config::XML_PATH_LIST_DEFAULT_SORT_BY,
            $sort,
            ScopeInterface::SCOPE_STORE,
            Store::DEFAULT_STORE_ID
        );
        $category = $this->updateCategorySortBy('Category 1', null, Store::DEFAULT_STORE_ID);
        $this->callBlock($direction, $category);
        $this->assertBlockSorting($sort, $expectation);
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
            ],
            'default_order_custom_attribute_desc' => [
                'sort' => 'test_configurable',
                'direction' => 'desc',
                'expectation' => ['simple3', 'simple2', 'simple1'],
            ],
        ];
    }

    /**
     * @magentoDataFixture Magento/Store/_files/second_store.php
     * @magentoDataFixture Magento/Catalog/_files/products_with_not_empty_layered_navigation_attribute.php
     * @dataProvider productListSortOrderDataProviderOnStoreView
     * @param string $sort
     * @param string $direction
     * @param array $expectation
     * @param string $defaultSort
     * @return void
     */
    public function testProductListSortOrderOnStoreView(
        string $sort,
        string $direction,
        array $expectation,
        string $defaultSort
    ): void {
        $secondStoreId = (int)$this->storeManager->getStore('fixture_second_store')->getId();
        $this->updateCategorySortBy('Category 1', $defaultSort, Store::DEFAULT_STORE_ID);
        $category = $this->updateCategorySortBy('Category 1', $sort, $secondStoreId);
        $this->callBlock($direction, $category);
        $this->assertBlockSorting($sort, $expectation);
    }

    /**
     * @magentoDataFixture Magento/Store/_files/second_store.php
     * @magentoDataFixture Magento/Catalog/_files/products_with_not_empty_layered_navigation_attribute.php
     * @dataProvider productListSortOrderDataProviderOnStoreView
     * @param string $sort
     * @param string $direction
     * @param array $expectation
     * @param string $defaultSort
     * @return void
     */
    public function testProductListSortOrderWithConfigOnStoreView(
        string $sort,
        string $direction,
        array $expectation,
        string $defaultSort
    ): void {
        $this->objectManager->removeSharedInstance(Config::class);
        $secondStoreId = (int)$this->storeManager->getStore('fixture_second_store')->getId();
        $this->scopeConfig->setValue(
            Config::XML_PATH_LIST_DEFAULT_SORT_BY,
            $defaultSort,
            ScopeInterface::SCOPE_STORE,
            Store::DEFAULT_STORE_ID
        );
        $this->scopeConfig->setValue(
            Config::XML_PATH_LIST_DEFAULT_SORT_BY,
            $sort,
            ScopeInterface::SCOPE_STORE,
            'fixture_second_store'
        );
        $this->updateCategorySortBy('Category 1', null, Store::DEFAULT_STORE_ID);
        $category = $this->updateCategorySortBy('Category 1', null, $secondStoreId);
        $this->callBlock($direction, $category);
        $this->assertBlockSorting($sort, $expectation);
    }

    /**
     * @return array
     */
    public function productListSortOrderDataProviderOnStoreView(): array
    {
        return array_merge_recursive(
            $this->productListSortOrderDataProvider(),
            [
                'default_order_price_asc' => ['default_sort' => 'position'],
                'default_order_price_desc' => ['default_sort' => 'position'],
                'default_order_position_asc' => ['default_sort' => 'price'],
                'default_order_position_desc' => ['default_sort' => 'price'],
                'default_order_name_asc' => ['default_sort' => 'price'],
                'default_order_name_desc' => ['default_sort' => 'price'],
                'default_order_custom_attribute_asc' => ['default_sort' => 'price'],
                'default_order_custom_attribute_desc' => ['default_sort' => 'price'],
            ]
        );
    }

    /**
     * Renders block to apply sorting.
     *
     * @param string $direction
     * @param CategoryInterface $category
     * @return void
     */
    private function callBlock(string $direction, CategoryInterface $category): void
    {
        $this->block->getLayer()->setCurrentCategory($category);
        $this->block->setDefaultDirection($direction);
        $this->block->toHtml();
    }

    /**
     * Checks product list block correct sorting.
     *
     * @param string $sort
     * @param array $expectation
     * @return void
     */
    private function assertBlockSorting(string $sort, array $expectation): void
    {
        $this->assertArrayHasKey($sort, $this->block->getAvailableOrders());
        $this->assertEquals($sort, $this->block->getSortBy());
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
        /** @var CategoryInterface $category */
        $categoryIds = $categoryCollection->setStoreId($storeId)
            ->addAttributeToFilter(CategoryInterface::KEY_NAME, $categoryName)
            ->setPageSize(1)
            ->getAllIds();

        return $this->categoryRepository->get(reset($categoryIds), $storeId);
    }

    /**
     * Updates category default sort by field.
     *
     * @param string $categoryName
     * @param string|null $sortBy
     * @param int $storeId
     * @return CategoryInterface
     */
    private function updateCategorySortBy(
        string $categoryName,
        ?string $sortBy,
        int $storeId
    ): CategoryInterface {
        $oldStoreId = $this->storeManager->getStore()->getId();
        $this->storeManager->setCurrentStore($storeId);
        $category = $this->loadCategory($categoryName, $storeId);
        $category->addData(['default_sort_by' => $sortBy]);
        $category = $this->categoryRepository->save($category);
        $this->storeManager->setCurrentStore($oldStoreId);

        return $category;
    }
}
