<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryBundleIndexer\Test\Integration;

use Magento\Bundle\Model\ResourceModel\Selection;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Inventory\Model\GetStockItemDataInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\InventoryCatalog\Model\GetProductIdsBySkus;
use Magento\InventoryCatalog\Model\GetProductIdsBySkusInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoDbIsolation disabled
 */
class AddBundleDataToIndexTest extends TestCase
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var GetStockItemDataInterface
     */
    private $getStockItemData;

    /**
     * @var Selection
     */
    private $selection;

    /**
     * @var GetProductIdsBySkus
     */
    private $getProductIdsBySkusInterface;

    /**
     * @var SourceItemRepositoryInterface
     */
    private $sourceItemRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SourceItemsSaveInterface
     */
    private $sourceItemsSave;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        $this->getStockItemData = Bootstrap::getObjectManager()->get(GetStockItemDataInterface::class);
        $this->selection = Bootstrap::getObjectManager()->get(Selection::class);
        $this->getProductIdsBySkusInterface = Bootstrap::getObjectManager()->get(GetProductIdsBySkusInterface::class);
        $this->sourceItemRepository = Bootstrap::getObjectManager()->get(SourceItemRepositoryInterface::class);
        $this->searchCriteriaBuilder = Bootstrap::getObjectManager()->get(SearchCriteriaBuilder::class);
        $this->sourceItemsSave = Bootstrap::getObjectManager()->get(SourceItemsSaveInterface::class);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryBundleIndexer/Test/_files/bundle_product_eu_website.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryBundleIndexer/Test/_files/source_items_bundle.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     *
     * @param int $qtyChildrenToOutOfStock
     * @param bool $expectedResult
     * @return void
     *
     * @dataProvider executeDataProvider
     */
    public function testExecute(int $qtyChildrenToOutOfStock, bool $expectedResult)
    {
        $bundleSku = 'bundle-product-eu-website';
        if ($qtyChildrenToOutOfStock !== 0) {
            $this->makeChildrenOutOfStock($qtyChildrenToOutOfStock, $bundleSku);
        }
        $bundleStockItemData = $this->getStockItemData->execute($bundleSku, 10);

        self::assertEquals($expectedResult, (bool)$bundleStockItemData['is_salable']);
    }

    public function executeDataProvider(): array
    {
        return [
            [0, true],
            [1, true],
            [3, false],
        ];
    }

    /**
     * @param int $qty
     * @param string $parentSku
     * @return void
     */
    private function makeChildrenOutOfStock(int $qty, string $parentSku)
    {
        $ids = $this->getProductIdsBySkusInterface->execute([$parentSku]);
        $id = reset($ids);

        $childrenIds = $this->selection->getChildrenIds($id)[0];
        foreach ($childrenIds as $childId) {
            if ($qty === 0) {
                break;
            }
            $child = $this->productRepository->getById($childId);

            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter(SourceItemInterface::SKU, $child->getSku())
                ->create();
            $items = $this->sourceItemRepository->getList($searchCriteria)->getItems();
            $sourceItem = reset($items);
            $sourceItem->setQuantity(0);
            $sourceItem->setStatus(0);

            $this->sourceItemsSave->execute([$sourceItem]);
            $qty--;
        }
    }
}
