<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Model\ResourceModel\Stock;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Indexer\Product\Price\Processor as PriceProcessor;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Model\Configuration;
use Magento\CatalogInventory\Model\Indexer\Stock\Processor as StockProcessor;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Item as ItemResource;
use Magento\CatalogInventory\Model\Stock;
use Magento\CatalogInventory\Model\StockRegistryStorage;
use Magento\ConfigurableProduct\Test\Fixture\Attribute as ConfigurableAttributeFixture;
use Magento\ConfigurableProduct\Test\Fixture\Product as ConfigurableProductFixture;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\Framework\Indexer\StateInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Indexer\Model\Processor as IndexerProcessor;
use Magento\Indexer\Test\Fixture\ScheduleMode;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureBeforeTransaction;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests for stock item resource model
 *
 * @see \Magento\CatalogInventory\Model\ResourceModel\Stock\Item
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ItemTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var MutableScopeConfigInterface */
    private $mutableConfig;

    /** @var ItemResource */
    private $stockItemResource;

    /** @var StoreManagerInterface */
    private $storeManager;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var StockRegistryStorage */
    private $stockRegistryStorage;

    /** @var StockProcessor */
    private $stockIndexerProcessor;

    /** @var PriceProcessor */
    private $priceIndexerProcessor;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->mutableConfig = $this->objectManager->get(MutableScopeConfigInterface::class);
        $this->stockItemResource = $this->objectManager->get(ItemResource::class);
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->productRepository->cleanCache();
        $this->stockRegistryStorage = $this->objectManager->get(StockRegistryStorage::class);
        $this->stockIndexerProcessor = $this->objectManager->get(StockProcessor::class);
        $this->priceIndexerProcessor = $this->objectManager->get(PriceProcessor::class);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple_duplicated.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple_backorders_no.php
     * @magentoConfigFixture current_store cataloginventory/item_options/min_qty 105
     * @magentoConfigFixture current_store cataloginventory/item_options/manage_stock 1
     * @magentoDbIsolation disabled
     * @param int $backorders
     * @param array $expectedStockItems
     * @return void
     */
    #[DataProvider('updateSetOutOfStockDataProvider')]
    public function testUpdateSetOutOfStock(int $backorders, array $expectedStockItems): void
    {
        $this->stockIndexerProcessor->reindexAll();
        $this->priceIndexerProcessor->reindexAll();
        $this->mutableConfig->setValue(Configuration::XML_PATH_BACKORDERS, $backorders, ScopeInterface::SCOPE_STORE);
        $websiteId = (int)$this->storeManager->getWebsite('admin')->getId();
        $this->stockItemResource->updateSetOutOfStock($websiteId);

        $this->assertProductsStockItem($expectedStockItems);
        $this->assertEquals(StateInterface::STATUS_INVALID, $this->stockIndexerProcessor->getIndexer()->getStatus());
        $this->assertEquals(StateInterface::STATUS_INVALID, $this->priceIndexerProcessor->getIndexer()->getStatus());
    }

    /**
     * @return array
     */
    public static function updateSetOutOfStockDataProvider(): array
    {
        return [
            'backorders_no' => [
                'backorders' => Stock::BACKORDERS_NO,
                'expectedStockItems' => [
                    'simple-1' => [
                        'is_in_stock' => Stock::STOCK_OUT_OF_STOCK,
                        'stock_status_changed_auto' => 1,
                    ],
                    'simple-backorders-no' => [
                        'is_in_stock' => Stock::STOCK_OUT_OF_STOCK,
                        'stock_status_changed_auto' => 1,
                    ],
                ],
            ],
            'backorders_yes' => [
                'backorders' => Stock::BACKORDERS_YES_NONOTIFY,
                'expectedStockItems' => [
                    'simple-1' => [
                        'is_in_stock' => Stock::STOCK_IN_STOCK,
                        'stock_status_changed_auto' => 0,
                    ],
                    'simple-backorders-no' => [
                        'is_in_stock' => Stock::STOCK_OUT_OF_STOCK,
                        'stock_status_changed_auto' => 1,
                    ],
                ],
            ],
        ];
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/out_of_stock_product_with_category.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple_out_of_stock.php
     * @magentoConfigFixture current_store cataloginventory/item_options/min_qty 50
     * @magentoDbIsolation disabled
     * @param int $manageStock
     * @param array $expectedStockItems
     * @return void
     */
    #[DataProvider('updateUpdateSetInStockDataProvider')]
    public function testUpdateSetInStock(int $manageStock, array $expectedStockItems): void
    {
        $this->updateProductsStockItem([
            'out-of-stock-product' => [
                'qty' => 60,
                'stock_status_changed_automatically_flag' => true,
            ],
            'simple-out-of-stock' => [
                'use_config_manage_stock' => 0,
                'manage_stock' => 1,
                'qty' => 80,
                'stock_status_changed_automatically_flag' => true,
            ],
        ]);
        $this->stockIndexerProcessor->reindexAll();
        $this->priceIndexerProcessor->reindexAll();
        $this->mutableConfig->setValue(Configuration::XML_PATH_MANAGE_STOCK, $manageStock, ScopeInterface::SCOPE_STORE);
        $websiteId = (int)$this->storeManager->getWebsite('admin')->getId();
        $this->stockItemResource->updateSetInStock($websiteId);

        $this->assertProductsStockItem($expectedStockItems);
        $this->assertEquals(StateInterface::STATUS_INVALID, $this->stockIndexerProcessor->getIndexer()->getStatus());
        $this->assertEquals(StateInterface::STATUS_INVALID, $this->priceIndexerProcessor->getIndexer()->getStatus());
    }

    /**
     * @return array
     */
    public static function updateUpdateSetInStockDataProvider(): array
    {
        return [
            'manage_stock_yes' => [
                'manageStock' => 1,
                'expectedStockItems' => [
                    'out-of-stock-product' => [
                        'is_in_stock' => Stock::STOCK_IN_STOCK,
                    ],
                    'simple-out-of-stock' => [
                        'is_in_stock' => Stock::STOCK_IN_STOCK,
                    ],
                ],
            ],
            'manage_stock_no' => [
                'manageStock' => 0,
                'expectedStockItems' => [
                    'out-of-stock-product' => [
                        'is_in_stock' => Stock::STOCK_OUT_OF_STOCK,
                    ],
                    'simple-out-of-stock' => [
                        'is_in_stock' => Stock::STOCK_IN_STOCK,
                    ],
                ],
            ],
        ];
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple_with_url_key.php
     * @param int $manageStock
     * @param array $expectedLowStockDate
     * @return void
     */
    #[DataProvider('updateLowStockDateDataProvider')]
    public function testLowStockDate(int $manageStock, array $expectedLowStockDate): void
    {
        $this->updateProductsStockItem([
            'simple2' => [
                'use_config_manage_stock' => 0,
                'manage_stock' => 1,
            ],
        ]);
        $this->mutableConfig->setValue(Configuration::XML_PATH_MANAGE_STOCK, $manageStock, ScopeInterface::SCOPE_STORE);
        $this->mutableConfig->setValue(Configuration::XML_PATH_NOTIFY_STOCK_QTY, 105, ScopeInterface::SCOPE_STORE);
        $websiteId = (int)$this->storeManager->getWebsite('admin')->getId();
        $this->stockItemResource->updateLowStockDate($websiteId);

        $this->assertLowStockDate($expectedLowStockDate);
    }

    /**
     * @return array
     */
    public static function updateLowStockDateDataProvider(): array
    {
        return [
            'manage_stock_yes' => [
                'manageStock' => 1,
                'expectedLowStockDate' => [
                    'simple1' => [
                        'is_low_stock_date_null' => false,
                    ],
                    'simple2' => [
                        'is_low_stock_date_null' => false,
                    ],
                ],
            ],
            'manage_stock_no' => [
                'manageStock' => 0,
                'expectedLowStockDate' => [
                    'simple1' => [
                        'is_low_stock_date_null' => true,
                    ],
                    'simple2' => [
                        'is_low_stock_date_null' => false,
                    ],
                ],
            ],
        ];
    }

    /**
     * Update products stock item
     *
     * @param array $productsStockData
     * @return void
     */
    private function updateProductsStockItem(array $productsStockData): void
    {
        foreach ($productsStockData as $sku => $stockData) {
            $product = $this->productRepository->get($sku, true, null, true);
            $stockItem = $product->getExtensionAttributes()->getStockItem();
            $stockItem->addData($stockData);
            $this->productRepository->save($product);
        }
    }

    /**
     * Assert products stock item
     *
     * @param array $expectedStockItems
     * @return void
     */
    private function assertProductsStockItem(array $expectedStockItems): void
    {
        $this->stockRegistryStorage->clean();
        foreach ($expectedStockItems as $sku => $expectedData) {
            $product = $this->productRepository->get($sku, false, null, true);
            $stockItem = $product->getExtensionAttributes()->getStockItem();
            $this->assertEmpty(
                array_diff_assoc($expectedData, $stockItem->getData()),
                'Actual stock item data not equals expected data.'
            );
        }
    }

    /**
     * Assert low_stock_date value of products stock item
     *
     * @param array $expectedLowStockDate
     * @return void
     */
    private function assertLowStockDate(array $expectedLowStockDate): void
    {
        $this->stockRegistryStorage->clean();
        foreach ($expectedLowStockDate as $sku => $expectedData) {
            $product = $this->productRepository->get($sku, false, null, true);
            $stockItem = $product->getExtensionAttributes()->getStockItem();
            if ($expectedData['is_low_stock_date_null']) {
                $this->assertNull($stockItem->getLowStockDate());
            } else {
                $this->assertNotNull($stockItem->getLowStockDate());
            }
        }
    }

    /**
     * A stock status change saved through the stock item API must reach the price index in the scheduled mode
     */
    #[
        AppIsolation(true),
        DbIsolation(false),
        DataFixtureBeforeTransaction(ScheduleMode::class, ['indexer' => PriceProcessor::INDEXER_ID]),
        DataFixtureBeforeTransaction(ScheduleMode::class, ['indexer' => StockProcessor::INDEXER_ID]),
        DataFixture(ConfigurableAttributeFixture::class, as: 'attribute'),
        DataFixture(ProductFixture::class, ['price' => 500], 'expensive'),
        DataFixture(ProductFixture::class, ['price' => 100], 'cheap'),
        DataFixture(
            ConfigurableProductFixture::class,
            ['_options' => ['$attribute$'], '_links' => ['$expensive$', '$cheap$']],
            'configurable'
        ),
    ]
    public function testConfigurablePriceFollowsVariantStockChangedInScheduledMode(): void
    {
        $fixtures = DataFixtureStorageManager::getStorage();
        $configurableId = (int)$fixtures->get('configurable')->getId();
        $cheapSku = $fixtures->get('cheap')->getSku();
        $this->updateMview();
        $this->assertEquals(100, $this->getIndexedMinPrice($configurableId));

        $this->updateStockStatusBySku($cheapSku, false);
        $this->updateMview();
        $this->assertEquals(500, $this->getIndexedMinPrice($configurableId));

        $this->updateStockStatusBySku($cheapSku, true);
        $this->updateMview();
        $this->assertEquals(100, $this->getIndexedMinPrice($configurableId));
    }

    /**
     * Change the stock status the same way PUT /V1/products/:productSku/stockItems/:itemId does
     *
     * @param string $sku
     * @param bool $isInStock
     * @return void
     */
    private function updateStockStatusBySku(string $sku, bool $isInStock): void
    {
        $stockRegistry = $this->objectManager->get(StockRegistryInterface::class);
        $stockItem = $stockRegistry->getStockItemBySku($sku);
        $stockItem->setIsInStock($isInStock);
        $stockItem->setQty($isInStock ? 10 : 0);
        $stockRegistry->updateStockItemBySku($sku, $stockItem);
    }

    /**
     * Process the indexer changelogs the same way the indexer_update_all_views cron job does
     *
     * @return void
     */
    private function updateMview(): void
    {
        $indexerProcessor = $this->objectManager->create(IndexerProcessor::class);
        $indexerProcessor->updateMview();
        $indexerProcessor->updateMview();
    }

    /**
     * Get the indexed minimal price of the product for the main website and the NOT LOGGED IN group
     *
     * @param int $productId
     * @return float|null
     */
    private function getIndexedMinPrice(int $productId): ?float
    {
        $connection = $this->objectManager->get(ResourceConnection::class)->getConnection();
        $minPrice = $connection->fetchOne(
            $connection->select()
                ->from($connection->getTableName('catalog_product_index_price'), ['min_price'])
                ->where('entity_id = ?', $productId)
                ->where('website_id = ?', 1)
                ->where('customer_group_id = ?', 0)
        );

        return $minPrice === false ? null : (float)$minPrice;
    }
}
