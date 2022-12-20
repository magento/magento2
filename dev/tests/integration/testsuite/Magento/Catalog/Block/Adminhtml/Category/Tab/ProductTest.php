<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Block\Adminhtml\Category\Tab;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\ResourceModel\Collection\AbstractCollection;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Magento\Framework\ObjectManagerInterface;
use Magento\Catalog\Api\Data\ProductInterface;

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
    public function optionsFilterProvider(): array
    {
        return [
            'filter_yes' => [
                'filter_column' => 'in_category=1',
                'id_category' => 333,
                'store_id' => 1,
                'items' => [
                    'simple333',
                    'simple2',
                ],
            ],
            'filter_no' => [
                'filter_column' => 'in_category=0',
                'id_category' => 333,
                'store_id' => 1,
                'items' => [
                    'product_disabled',
                    'simple',
                ],
            ],
            'filter_any' => [
                'filter_column' => "",
                'id_category' => 333,
                'store_id' => 1,
                'items' => [
                    'product_disabled',
                    'simple333',
                    'simple2',
                    'simple',
                ],
            ],
            'flag_status' => [
                'filter_column' => 'status=1',
                'id_category' => 333,
                'store_id' => 1,
                'items' => [
                    'simple333',
                    'simple2',
                    'simple',
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
}
