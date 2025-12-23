<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\BundleImportExport\Model\Import\Product\Type;

use Magento\Bundle\Test\Fixture\Link as BundleSelectionFixture;
use Magento\Bundle\Test\Fixture\Option as BundleOptionFixture;
use Magento\Bundle\Test\Fixture\Product as BundleProductFixture;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\Adapter as ImportAdapter;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\Import\Source\Csv;
use Magento\ImportExport\Test\Fixture\CsvFile as CsvFileFixture;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\TestFramework\Fixture\ScopeFixture;

/**
 * @magentoAppArea adminhtml
 * @magentoAppIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BundleTest extends \Magento\TestFramework\Indexer\TestCase
{
    /**
     * Bundle product test Name
     */
    private const TEST_PRODUCT_NAME = 'Bundle 1';

    /**
     * Bundle product test Type
     */
    private const TEST_PRODUCT_TYPE = 'bundle';

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var string[]
     */
    private $importedProductSkus;

    /**
     * List of Bundle options SKU
     *
     * @var array
     */
    protected $optionSkuList = ['Simple 1', 'Simple 2', 'Simple 3'];

    public static function setUpBeforeClass(): void
    {
        $db = Bootstrap::getInstance()->getBootstrap()
            ->getApplication()
            ->getDbInstance();
        if (!$db->isDbDumpExists()) {
            throw new \LogicException('DB dump does not exist.');
        }
        $db->restoreFromDbDump();

        parent::setUpBeforeClass();
    }

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testBundleImport()
    {
        // import data from CSV file
        $pathToFile = __DIR__ . '/../../_files/import_bundle.csv';
        $errors = $this->doImport($pathToFile, Import::BEHAVIOR_APPEND);
        $this->assertEquals(0, $errors->getErrorsCount());

        $resource = $this->objectManager->get(ProductResource::class);
        $productId = $resource->getIdBySku(self::TEST_PRODUCT_NAME);
        $this->assertIsNumeric($productId);
        /** @var Product $product */
        $product = $this->objectManager->create(Product::class);
        $product->load($productId);

        $this->assertFalse($product->isObjectNew());
        $this->assertEquals(self::TEST_PRODUCT_NAME, $product->getName());
        $this->assertEquals(self::TEST_PRODUCT_TYPE, $product->getTypeId());
        $this->assertEquals(1, $product->getShipmentType());

        $optionIdList = $resource->getProductsIdsBySkus($this->optionSkuList);
        $bundleOptionCollection = $product->getExtensionAttributes()->getBundleProductOptions();
        $this->assertCount(2, $bundleOptionCollection);
        foreach ($bundleOptionCollection as $optionKey => $option) {
            $this->assertEquals('checkbox', $option->getData('type'));
            $this->assertEquals('Option ' . ($optionKey + 1), $option->getData('title'));
            $this->assertEquals(self::TEST_PRODUCT_NAME, $option->getData('sku'));
            $this->assertEquals($optionKey + 1, count($option->getData('product_links')));
            foreach ($option->getData('product_links') as $linkKey => $productLink) {
                $optionSku = 'Simple ' . ($optionKey + 1 + $linkKey);
                $this->assertEquals($optionIdList[$optionSku], $productLink->getData('entity_id'));
                $this->assertEquals($optionSku, $productLink->getData('sku'));

                switch ($optionKey + 1 + $linkKey) {
                    case 1:
                        $this->assertEquals(1, (int) $productLink->getCanChangeQuantity());
                        break;
                    case 2:
                        $this->assertEquals(0, (int) $productLink->getCanChangeQuantity());
                        break;
                    case 3:
                        $this->assertEquals(1, (int) $productLink->getCanChangeQuantity());
                        break;
                }
            }
        }
        $this->importedProductSkus = ['Simple 1', 'Simple 2', 'Simple 3', 'Bundle 1'];
    }

    /**
     * Test that Bundle options are updated correctly by import
     *
     * @dataProvider valuesDataProvider
     *
     * @magentoAppArea adminhtml
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @param array $expectedValues
     * @return void
     */
    public function testBundleImportUpdateValues(array $expectedValues): void
    {
        // import data from CSV file
        $pathToFile = __DIR__ . '/../../_files/import_bundle.csv';
        $errors = $this->doImport($pathToFile, Import::BEHAVIOR_APPEND);
        $this->assertEquals(0, $errors->getErrorsCount());

        // import data from CSV file to update values
        $pathToFile2 = __DIR__ . '/../../_files/import_bundle_update_values.csv';
        $errors = $this->doImport($pathToFile2, Import::BEHAVIOR_APPEND);
        $this->assertEquals(0, $errors->getErrorsCount());

        $resource = $this->objectManager->get(ProductResource::class);
        $productId = $resource->getIdBySku(self::TEST_PRODUCT_NAME);
        $this->assertIsNumeric($productId);
        /** @var Product $product */
        $product = $this->objectManager->create(Product::class);
        $product->load($productId);

        $this->assertFalse($product->isObjectNew());
        $this->assertEquals(self::TEST_PRODUCT_NAME, $product->getName());
        $this->assertEquals(self::TEST_PRODUCT_TYPE, $product->getTypeId());
        $this->assertEquals(1, $product->getShipmentType());

        $optionIdList = $resource->getProductsIdsBySkus($this->optionSkuList);
        $bundleOptionCollection = $product->getExtensionAttributes()->getBundleProductOptions();
        $this->assertCount(3, $bundleOptionCollection);
        foreach ($bundleOptionCollection as $optionKey => $option) {
            $this->assertEquals('checkbox', $option->getData('type'));
            $this->assertEquals($expectedValues[$optionKey]['title'], $option->getData('title'));
            $this->assertEquals(self::TEST_PRODUCT_NAME, $option->getData('sku'));
            foreach ($option->getData('product_links') as $linkKey => $productLink) {
                $optionSku = $expectedValues[$optionKey]['product_links'][$linkKey];
                $this->assertEquals($optionIdList[$optionSku], $productLink->getData('entity_id'));
                $this->assertEquals($optionSku, $productLink->getData('sku'));
            }
        }
        $this->importedProductSkus = ['Simple 1', 'Simple 2', 'Simple 3', 'Bundle 1'];
    }

    /**
     * Test that Bundle options with question mark are updated correctly by import
     *
     *
     * @magentoAppArea adminhtml
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @return void
     */
    public function testBundleImportUpdateValuesWithQuestionMark(): void
    {
        // import data from CSV file
        $pathToFile = __DIR__ . '/../../_files/import_bundle_with_question_mark.csv';
        $errors = $this->doImport($pathToFile, Import::BEHAVIOR_APPEND);
        $this->assertEquals(0, $errors->getErrorsCount());

        // import data from CSV file to update values
        $pathToFile2 = __DIR__ . '/../../_files/import_bundle_with_question_mark.csv';
        $errors = $this->doImport($pathToFile2, Import::BEHAVIOR_APPEND);
        $this->assertEquals(0, $errors->getErrorsCount());

        $resource = $this->objectManager->get(ProductResource::class);
        $productId = $resource->getIdBySku(self::TEST_PRODUCT_NAME);
        $this->assertIsNumeric($productId);
        /** @var Product $product */
        $product = $this->objectManager->create(ProductRepositoryInterface::class);
        $ProductRepository = $product->get(self::TEST_PRODUCT_NAME);

        $this->assertEquals(self::TEST_PRODUCT_NAME, $ProductRepository->getName());
        $this->assertEquals(self::TEST_PRODUCT_TYPE, $ProductRepository->getTypeId());
        $this->assertEquals(1, $ProductRepository->getShipmentType());

        $bundleOptionCollection = $ProductRepository->getExtensionAttributes()->getBundleProductOptions();
        $this->assertCount(1, $bundleOptionCollection);

        $this->importedProductSkus = ['Simple 1', 'Bundle 1'];
    }

    /**
     * @magentoDataFixture Magento/Store/_files/second_store.php
     * @magentoDbIsolation disabled
     * @magentoAppArea adminhtml
     * @return void
     */
    public function testBundleImportWithMultipleStoreViews(): void
    {
        // import data from CSV file
        $pathToFile = __DIR__ . '/../../_files/import_bundle_multiple_store_views.csv';
        $errors = $this->doImport($pathToFile, Import::BEHAVIOR_APPEND);
        $this->assertEquals(0, $errors->getErrorsCount());
        $resource = $this->objectManager->get(ProductResource::class);
        $productId = $resource->getIdBySku(self::TEST_PRODUCT_NAME);
        $this->assertIsNumeric($productId);
        /** @var Product $product */
        $product = $this->objectManager->create(Product::class);
        $product->load($productId);
        $this->assertFalse($product->isObjectNew());
        $this->assertEquals(self::TEST_PRODUCT_NAME, $product->getName());
        $this->assertEquals(self::TEST_PRODUCT_TYPE, $product->getTypeId());
        $this->assertEquals(1, $product->getShipmentType());
        $optionIdList = $resource->getProductsIdsBySkus($this->optionSkuList);
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $i = 0;
        foreach ($product->getStoreIds() as $storeId) {
            $bundleOptionCollection = $productRepository->get(self::TEST_PRODUCT_NAME, false, $storeId)
                ->getExtensionAttributes()->getBundleProductOptions();
            $this->assertCount(2, $bundleOptionCollection);
            $i++;
            foreach ($bundleOptionCollection as $optionKey => $option) {
                $this->assertEquals('checkbox', $option->getData('type'));
                $this->assertEquals('Option ' . $i . ' ' . ($optionKey + 1), $option->getData('title'));
                $this->assertEquals(self::TEST_PRODUCT_NAME, $option->getData('sku'));
                $this->assertEquals($optionKey + 1, count($option->getData('product_links')));
                foreach ($option->getData('product_links') as $linkKey => $productLink) {
                    $optionSku = 'Simple ' . ($optionKey + 1 + $linkKey);
                    $this->assertEquals($optionIdList[$optionSku], $productLink->getData('entity_id'));
                    $this->assertEquals($optionSku, $productLink->getData('sku'));
                }
            }
        }
        $this->importedProductSkus = ['Simple 1', 'Simple 2', 'Simple 3', 'Bundle 1'];
    }

    /**
     * Provider for testBundleImportUpdateValues
     *
     * @return array
     */
    public static function valuesDataProvider(): array
    {
        return [
            [
                [
                    0 => [
                        'title' => 'Option 1',
                        'product_links' => ['Simple 1'],
                    ],
                    1 => [
                        'title' => 'Option 1 new',
                        'product_links' => ['Simple 1'],
                    ],
                    2 => [
                        'title' => 'Option 2',
                        'product_links' => ['Simple 2', 'Simple 3'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @magentoDbIsolation enabled
     * @dataProvider shouldUpdateBundleStockStatusIfChildProductsStockStatusChangedDataProvider
     * @param bool $isOption1Required
     * @param bool $isOption2Required
     * @param string $outOfStockImportFile
     * @param string $inStockImportFile
     * @throws NoSuchEntityException
     */
    public function testShouldUpdateBundleStockStatusIfChildProductsStockStatusChanged(
        bool $isOption1Required,
        bool $isOption2Required,
        string $outOfStockImportFile,
        string $inStockImportFile
    ): void {
        // import data from CSV file
        $pathToFile = __DIR__ . '/../../_files/import_bundle.csv';
        $errors = $this->doImport($pathToFile, Import::BEHAVIOR_APPEND);
        $this->assertEquals(0, $errors->getErrorsCount());
        $this->importedProductSkus = ['Simple 1', 'Simple 2', 'Simple 3', 'Bundle 1'];
        $sku = 'Bundle 1';
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        /** @var ProductInterface $product */
        $product = $productRepository->get($sku, true, null, true);
        $options = $product->getExtensionAttributes()->getBundleProductOptions();
        $options[0]->setRequired($isOption1Required);
        $options[1]->setRequired($isOption2Required);
        $extension = $product->getExtensionAttributes();
        $extension->setBundleProductOptions($options);
        $product->setExtensionAttributes($extension);
        $productRepository->save($product);

        $stockItem = $this->getStockItem((int) $product->getId());
        $this->assertNotNull($stockItem);
        $this->assertTrue($stockItem->getIsInStock());

        $errors = $this->doImport(__DIR__ . '/../../_files/' . $outOfStockImportFile);
        $this->assertEquals(0, $errors->getErrorsCount());

        $stockItem = $this->getStockItem((int) $product->getId());
        $this->assertNotNull($stockItem);
        $this->assertFalse($stockItem->getIsInStock());

        $errors = $this->doImport(__DIR__ . '/../../_files/' . $inStockImportFile);
        $this->assertEquals(0, $errors->getErrorsCount());

        $stockItem = $this->getStockItem((int) $product->getId());
        $this->assertNotNull($stockItem);
        $this->assertTrue($stockItem->getIsInStock());
    }

    /**
     * @return array
     */
    public static function shouldUpdateBundleStockStatusIfChildProductsStockStatusChangedDataProvider(): array
    {
        return [
            'all options are required' => [
                true,
                true,
                'outOfStockImportFile' => 'import_bundle_set_option1_products_out_of_stock.csv',
                'inStockImportFile' => 'import_bundle_set_option1_products_in_stock.csv'
            ],
            'all options are optional' => [
                false,
                false,
                'outOfStockImportFile' => 'import_bundle_set_all_products_out_of_stock.csv',
                'inStockImportFile' => 'import_bundle_set_option1_products_in_stock.csv'
            ]
        ];
    }

    #[
        DbIsolation(false),
        Config(\Magento\Catalog\Helper\Data::XML_PATH_PRICE_SCOPE, 1, ScopeInterface::SCOPE_STORE, 'default'),
        DataFixture(ScopeFixture::class, as: 'global_scope'),
        DataFixture(ScopeFixture::class, ['code' => 'default'], as: 'default_store'),
        DataFixture(ProductFixture::class, ['sku' => 'bundle_child_1'], as: 'p1'),
        DataFixture(ProductFixture::class, ['sku' => 'bundle_child_2'], as: 'p2'),
        DataFixture(BundleSelectionFixture::class, ['sku' => '$p1.sku$', 'price' => 10, 'price_type' => 0], 'link1'),
        DataFixture(BundleSelectionFixture::class, ['sku' => '$p2.sku$', 'price' => 20, 'price_type' => 1], 'link2'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$link1$'], 'title' => 'opt1'], 'opt1'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$link2$'], 'title' => 'opt2'], 'opt2'),
        DataFixture(
            BundleProductFixture::class,
            ['price' => 50,'price_type' => 1, '_options' => ['$opt1$','$opt2$']],
            'bundle',
            'global_scope',
        ),
        DataFixture(
            CsvFileFixture::class,
            [
                'rows' => [
                    [
                        'sku' => '$bundle.sku$',
                        'bundle_values' => 'name=opt1,type=select,required=1,sku=bundle_child_1,price=10.0000' .
                            ',default=0,default_qty=1.0000,price_type=fixed,can_change_qty=0' .
                            ',price_website_base=40.000000,price_type_website_base=percent' .
                            '|name=opt2,type=select,required=1,sku=bundle_child_2,price=20.0000' .
                            ',default=0,default_qty=1.0000,price_type=percent,can_change_qty=0' .
                            ',price_website_base=50.000000,price_type_website_base=percent'
                    ]
                ]
            ],
            'importFile',
        ),
    ]
    public function testImportWhenPriceScopeIsWebsite(): void
    {
        $fixtures = DataFixtureStorageManager::getStorage();
        $pathToFile = $fixtures->get('importFile')->getAbsolutePath();
        $sku = $fixtures->get('bundle')->getSku();
        $store = $fixtures->get('default_store');
        
        // import data from CSV file
        $errors = $this->doImport($pathToFile, Import::BEHAVIOR_APPEND);
        $this->assertEquals(0, $errors->getErrorsCount());
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        
        // verify selection prices in default scope
        /** @var ProductInterface $product */
        $product = $productRepository->get($sku, false, 0, true);
        $options = $product->getExtensionAttributes()->getBundleProductOptions();
        $this->assertCount(2, $options);
        $this->assertEquals(10, $options[0]->getProductLinks()[0]->getPrice());
        $this->assertEquals(0, $options[0]->getProductLinks()[0]->getPriceType());
        $this->assertEquals(20, $options[1]->getProductLinks()[0]->getPrice());
        $this->assertEquals(1, $options[1]->getProductLinks()[0]->getPriceType());
        
        // verify selection prices in default store
        $product = $productRepository->get($sku, false, $store->getId(), true);
        $options = $product->getExtensionAttributes()->getBundleProductOptions();
        $this->assertCount(2, $options);
        $this->assertEquals(40, $options[0]->getProductLinks()[0]->getPrice());
        $this->assertEquals(1, $options[0]->getProductLinks()[0]->getPriceType());
        $this->assertEquals(50, $options[1]->getProductLinks()[0]->getPrice());
        $this->assertEquals(1, $options[1]->getProductLinks()[0]->getPriceType());
    }

    /**
     * @param int $productId
     * @return StockItemInterface|null
     */
    private function getStockItem(int $productId): ?StockItemInterface
    {
        $criteriaFactory = $this->objectManager->create(StockItemCriteriaInterfaceFactory::class);
        $stockItemRepository = $this->objectManager->create(StockItemRepositoryInterface::class);
        $stockConfiguration = $this->objectManager->create(StockConfigurationInterface::class);
        $criteria = $criteriaFactory->create();
        $criteria->setScopeFilter($stockConfiguration->getDefaultScopeId());
        $criteria->setProductsFilter($productId);
        $stockItemCollection = $stockItemRepository->getList($criteria);
        $stockItems = $stockItemCollection->getItems();
        return reset($stockItems);
    }

    /**
     * @param string $file
     * @param string $behavior
     * @param bool $validateOnly
     * @return ProcessingErrorAggregatorInterface
     */
    private function doImport(
        string $file,
        string $behavior = Import::BEHAVIOR_ADD_UPDATE,
        bool $validateOnly = false
    ): ProcessingErrorAggregatorInterface {
        /** @var Filesystem $filesystem */
        $filesystem = $this->objectManager->create(Filesystem::class);
        $directoryWrite = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = ImportAdapter::findAdapterFor($file, $directoryWrite);
        $model = $this->objectManager->create(\Magento\CatalogImportExport\Model\Import\Product::class);
        $model->setParameters(['behavior' => $behavior, 'entity' => 'catalog_product']);
        $model->setSource($source);
        $errors = $model->validateData();
        if (!$validateOnly && !$errors->getAllErrors()) {
            $model->importData();
        }
        return $errors;
    }

    /**
     * teardown
     */
    protected function tearDown(): void
    {
        if (!empty($this->importedProductSkus)) {
            $objectManager = Bootstrap::getObjectManager();
            /** @var ProductRepositoryInterface $productRepository */
            $productRepository = $objectManager->create(ProductRepositoryInterface::class);
            $registry = $objectManager->get(\Magento\Framework\Registry::class);
            /** @var ProductRepositoryInterface $productRepository */
            $registry->unregister('isSecureArea');
            $registry->register('isSecureArea', true);

            foreach ($this->importedProductSkus as $sku) {
                try {
                    $productRepository->deleteById($sku);
                } catch (NoSuchEntityException $e) {
                    // product already deleted
                }
            }
            $registry->unregister('isSecureArea');
            $registry->register('isSecureArea', false);
        }

        parent::tearDown();
    }
}
