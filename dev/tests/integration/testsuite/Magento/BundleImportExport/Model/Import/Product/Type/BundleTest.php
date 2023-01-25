<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BundleImportExport\Model\Import\Product\Type;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
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
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\App\Filesystem\DirectoryList;

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
    public function valuesDataProvider(): array
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
    public function shouldUpdateBundleStockStatusIfChildProductsStockStatusChangedDataProvider(): array
    {
        return [
            'all options are required' => [
                true,
                true,
                'out-of-stock' => 'import_bundle_set_option1_products_out_of_stock.csv',
                'in-stock' => 'import_bundle_set_option1_products_in_stock.csv'
            ],
            'all options are optional' => [
                false,
                false,
                'out-of-stock' => 'import_bundle_set_all_products_out_of_stock.csv',
                'in-stock' => 'import_bundle_set_option1_products_in_stock.csv'
            ]
        ];
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
