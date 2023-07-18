<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableImportExport\Model\Import\Product\Type;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\ConfigurableProduct\Test\Fixture\Attribute as AttributeFixture;
use Magento\ConfigurableProduct\Test\Fixture\Product as ConfigurableProductFixture;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\EntityManager\EntityMetadata;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Filesystem;
use Magento\Framework\ObjectManagerInterface;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\Import\Adapter as ImportAdapter;
use Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory;
use Magento\ImportExport\Test\Fixture\CsvFile as CsvFileFixture;
use Magento\Store\Model\Store;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoAppArea adminhtml
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigurableTest extends TestCase
{
    /**
     * Configurable product test Type
     */
    public const TEST_PRODUCT_TYPE = 'configurable';

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product
     */
    protected $model;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var EntityMetadata
     */
    protected $productMetadata;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->model = $this->objectManager->create(\Magento\CatalogImportExport\Model\Import\Product::class);
        /** @var MetadataPool $metadataPool */
        $metadataPool = $this->objectManager->get(MetadataPool::class);
        $this->productMetadata = $metadataPool->getMetadata(ProductInterface::class);
    }

    /**
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_products.php
     */
    public function testShouldUpdateConfigurableStockStatusIfChildProductsStockStatusChanged(): void
    {
        $sku = 'configurable';
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        /** @var ProductInterface $product */
        $product = $productRepository->get($sku, true, null, true);
        $stockItem = $this->getStockItem((int) $product->getId());
        $this->assertNotNull($stockItem);
        $this->assertTrue($stockItem->getIsInStock());

        // Set all child product out of stock
        $pathToFile = __DIR__ . '/../../_files/import_configurable_child_products_stock_item_status_out_of_stock.csv';
        $errors = $this->doImport($pathToFile);
        $this->assertEquals(0, $errors->getErrorsCount());

        $stockItem = $this->getStockItem((int) $product->getId());
        $this->assertNotNull($stockItem);
        $this->assertFalse($stockItem->getIsInStock());

        // Set some child product in stock
        $pathToFile = __DIR__ . '/../../_files/import_configurable_child_products_stock_item_status_in_stock.csv';
        $errors = $this->doImport($pathToFile);
        $this->assertEquals(0, $errors->getErrorsCount());

        $stockItem = $this->getStockItem((int) $product->getId());
        $this->assertNotNull($stockItem);
        $this->assertTrue($stockItem->getIsInStock());
    }

    public function configurableImportDataProvider()
    {
        return [
            'Configurable 1' => [
                __DIR__ . '/../../_files/import_configurable.csv',
                'Configurable 1',
                ['Configurable 1-Option 1', 'Configurable 1-Option 2'],
            ],
            '12345' => [
                __DIR__ . '/../../_files/import_configurable_12345.csv',
                '12345',
                ['Configurable 1-Option 1', 'Configurable 1-Option 2'],
            ],
        ];
    }

    /**
     * @param string $pathToFile Path to import file
     * @param string $productName Name/sku of configurable product
     * @param array $optionSkuList Name of variations for configurable product
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_attribute.php
     * @magentoAppArea adminhtml
     * @magentoAppIsolation enabled
     * @dataProvider configurableImportDataProvider
     */
    public function testConfigurableImport($pathToFile, $productName, $optionSkuList)
    {
        $errors = $this->doImport($pathToFile, Import::BEHAVIOR_APPEND);

        $this->assertTrue($errors->getErrorsCount() == 0);
        $this->model->importData();

        /** @var \Magento\Catalog\Model\ResourceModel\Product $resource */
        $resource = $this->objectManager->get(\Magento\Catalog\Model\ResourceModel\Product::class);
        $productId = $resource->getIdBySku($productName);
        $this->assertIsNumeric($productId);
        /** @var Product $product */
        $product = $this->objectManager->get(ProductRepositoryInterface::class)->getById($productId);

        $this->assertFalse($product->isObjectNew());
        $this->assertEquals($productName, $product->getName());
        $this->assertEquals(self::TEST_PRODUCT_TYPE, $product->getTypeId());

        $optionCollection = $product->getTypeInstance()->getConfigurableOptions($product);
        foreach ($optionCollection as $option) {
            foreach ($option as $optionData) {
                $this->assertContains($optionData['sku'], $optionSkuList);
            }
        }

        $optionIdList = $resource->getProductsIdsBySkus($optionSkuList);
        foreach ($optionIdList as $optionId) {
            $this->assertArrayHasKey($optionId, $product->getExtensionAttributes()->getConfigurableProductLinks());
        }

        $configurableOptionCollection = $product->getExtensionAttributes()->getConfigurableProductOptions();
        $this->assertCount(1, $configurableOptionCollection);
        foreach ($configurableOptionCollection as $option) {
            $optionData = $option->getData();
            $this->assertArrayHasKey('product_super_attribute_id', $optionData);
            $this->assertArrayHasKey('product_id', $optionData);
            $this->assertEquals($product->getData($this->productMetadata->getLinkField()), $optionData['product_id']);
            $this->assertArrayHasKey('attribute_id', $optionData);
            $this->assertArrayHasKey('position', $optionData);
            $this->assertArrayHasKey('extension_attributes', $optionData);
            $this->assertArrayHasKey('product_attribute', $optionData);
            $productAttributeData = $optionData['product_attribute']->getData();
            $this->assertArrayHasKey('attribute_id', $productAttributeData);
            $this->assertArrayHasKey('entity_type_id', $productAttributeData);
            $this->assertArrayHasKey('attribute_code', $productAttributeData);
            $this->assertEquals('test_configurable', $productAttributeData['attribute_code']);
            $this->assertArrayHasKey('frontend_label', $productAttributeData);
            $this->assertEquals('Test Configurable', $productAttributeData['frontend_label']);
            $this->assertArrayHasKey('label', $optionData);
            $this->assertEquals('test_configurable_custom_label', $optionData['label']);
            $this->assertArrayHasKey('use_default', $optionData);
            $this->assertArrayHasKey('options', $optionData);
            $this->assertEquals('Option 1', $optionData['options'][0]['label']);
            $this->assertEquals('Option 1', $optionData['options'][0]['default_label']);
            $this->assertEquals('Option 1', $optionData['options'][0]['store_label']);
            $this->assertEquals('Option 2', $optionData['options'][1]['label']);
            $this->assertEquals('Option 2', $optionData['options'][1]['default_label']);
            $this->assertEquals('Option 2', $optionData['options'][1]['store_label']);
            $this->assertArrayHasKey('values', $optionData);
            $valuesData = $optionData['values'];
            $this->assertCount(2, $valuesData);
        }
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/enable_reindex_schedule.php
     * @magentoDataFixture Magento/Store/_files/second_store.php
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_attribute.php
     * @magentoAppArea adminhtml
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     */
    public function testConfigurableImportWithMultipleStores()
    {
        $productSku = 'Configurable 1';
        $products = [
            'default' => 'Configurable 1',
            'fixture_second_store' => 'Configurable 1 Second Store'
        ];
        $pathToFile = __DIR__ . '/../../_files/import_configurable_for_multiple_store_views.csv';
        $errors = $this->doImport($pathToFile, Import::BEHAVIOR_APPEND);

        $this->assertTrue($errors->getErrorsCount() == 0);
        $this->model->importData();

        foreach ($products as $storeCode => $productName) {
            $store = $this->objectManager->create(Store::class);
            $store->load($storeCode, 'code');
            /** @var ProductRepositoryInterface $productRepository */
            $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
            /** @var ProductInterface $product */
            $product = $productRepository->get($productSku, 0, $store->getId());
            $this->assertFalse($product->isObjectNew());
            $this->assertEquals($productName, $product->getName());
            $this->assertEquals(self::TEST_PRODUCT_TYPE, $product->getTypeId());
        }
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/enable_reindex_schedule.php
     * @magentoDataFixture Magento/Store/_files/second_store.php
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_attribute.php
     * @magentoDbIsolation disabled
     * @magentoAppArea adminhtml
     */
    public function testConfigurableImportWithStoreSpecifiedMainItem()
    {
        {
            $expectedErrorMessage = 'Product with assigned super attributes should not have specified "store_view_code"'
                . ' value';
            $pathToFile = __DIR__ . '/../../_files/import_configurable_for_multiple_store_views_error.csv';
            $errors = $this->doImport($pathToFile, Import::BEHAVIOR_APPEND);

            $this->assertTrue($errors->getErrorsCount() == 1);
            $this->assertEquals($expectedErrorMessage, $errors->getAllErrors()[0]->getErrorMessage());
        }
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

    #[
        DataFixture(ProductFixture::class, ['sku' => 'cp1-10,2cm'], as: 'p1'),
        DataFixture(ProductFixture::class, ['sku' => 'cp1-15,5cm'], as: 'p2'),
        DataFixture(
            AttributeFixture::class,
            [
                'attribute_code' => 'size',
                'options' => [['label' => '10,2cm'], ['label' => '15,5cm']],
            ],
            as: 'attr'
        ),
        DataFixture(
            ConfigurableProductFixture::class,
            ['_options' => ['$attr$'], '_links' => ['$p1$', '$p2$']],
            'cp1'
        ),
        DataFixture(
            CsvFileFixture::class,
            [
                'rows' => [
                    ['sku', 'configurable_variations'],
                    ['$cp1.sku$', 'sku=cp1-10,2cm,size=10,2cm|sku=cp1-15,5cm,size=15,5cm'],
                ]
            ],
            'file'
        )
    ]
    public function testSpecialCharactersInConfigurableVariations(): void
    {
        $fixtures = DataFixtureStorageManager::getStorage();
        $attrId = $fixtures->get('attr')->getId();
        $sku = $fixtures->get('cp1')->getSku();
        $p1Id = $fixtures->get('p1')->getId();
        $p2Id = $fixtures->get('p2')->getId();
        $pathToFile = $fixtures->get('file')->getAbsolutePath();
        $errors = $this->doImport($pathToFile, Import::BEHAVIOR_APPEND);
        $this->assertEquals(
            0,
            $errors->getErrorsCount(),
            implode(PHP_EOL, array_map(fn ($error) => $error->getErrorMessage(), $errors->getAllErrors()))
        );
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        /** @var ProductInterface $product */
        $product = $productRepository->get($sku, forceReload: true);
        $options = $product->getExtensionAttributes()->getConfigurableProductOptions();
        $this->assertCount(1, $options);
        $this->assertEquals($attrId, reset($options)->getAttributeId());
        $childIds = $product->getExtensionAttributes()->getConfigurableProductLinks();
        $this->assertCount(2, $childIds);
        $this->assertContains($p1Id, $childIds);
        $this->assertContains($p2Id, $childIds);
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
        $filesystem =$this->objectManager->create(Filesystem::class);
        $directoryWrite = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = ImportAdapter::findAdapterFor($file, $directoryWrite);
        $errors = $this->model
            ->setParameters(['behavior' => $behavior, 'entity' => 'catalog_product'])
            ->setSource($source)
            ->validateData();
        if (!$validateOnly && !$errors->getAllErrors()) {
            $this->model->importData();
        }
        return $errors;
    }
}
