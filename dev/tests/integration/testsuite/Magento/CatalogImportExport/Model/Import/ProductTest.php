<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\CatalogImportExport\Model\Import\Product
 *
 * The "CouplingBetweenObjects" warning is caused by tremendous complexity of the original class
 *
 */
namespace Magento\CatalogImportExport\Model\Import;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductCustomOptionRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface;
use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Registry;
use Magento\ImportExport\Model\Import;
use Magento\Store\Model\Store;
use Psr\Log\LoggerInterface;

/**
 * Class ProductTest
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 * @magentoAppArea adminhtml
 * @magentoDataFixtureBeforeTransaction Magento/Catalog/_files/enable_reindex_schedule.php
 * @magentoDataFixtureBeforeTransaction Magento/Catalog/_files/enable_catalog_product_reindex_schedule.php
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * phpcs:disable Generic.PHP.NoSilencedErrors, Generic.Metrics.NestingLevel, Magento2.Functions.StaticFunction
 */
class ProductTest extends \Magento\TestFramework\Indexer\TestCase
{
    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product
     */
    protected $_model;

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Uploader
     */
    protected $_uploader;

    /**
     * @var \Magento\CatalogImportExport\Model\Import\UploaderFactory
     */
    protected $_uploaderFactory;

    /**
     * @var \Magento\CatalogInventory\Model\Spi\StockStateProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_stockStateProvider;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\CatalogImportExport\Model\Import\Product::class,
            ['logger' => $this->logger]
        );
        $this->importedProducts = [];

        parent::setUp();
    }

    protected function tearDown()
    {
        /* We rollback here the products created during the Import because they were
           created during test execution and we do not have the rollback for them */
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        foreach ($this->importedProducts as $productSku) {
            try {
                $product = $productRepository->get($productSku, false, null, true);
                $productRepository->delete($product);
                // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock
            } catch (NoSuchEntityException $e) {
                // nothing to delete
            }
        }
    }

    /**
     * @var array
     */
    private $importedProducts;

    /**
     * Options for assertion
     *
     * @var array
     */
    protected $_assertOptions = [
        'is_require' => 'required',
        'price' => 'price',
        'sku' => 'sku',
        'sort_order' => 'order',
        'max_characters' => 'max_characters',
    ];

    /**
     * Option values for assertion
     *
     * @var array
     */
    protected $_assertOptionValues = [
        'title' => 'option_title',
        'price' => 'price',
        'sku' => 'sku',
    ];

    /**
     * List of specific custom option types
     *
     * @var array
     */
    private $specificTypes = [
        'drop_down',
        'radio',
        'checkbox',
        'multiple',
    ];

    /**
     * Test if visibility properly saved after import
     *
     * @magentoDataFixture Magento/Catalog/_files/multiple_products.php
     * @magentoAppIsolation enabled
     */
    public function testSaveProductsVisibility()
    {
        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
        $productRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Api\ProductRepositoryInterface::class
        );
        $id1 = $productRepository->get('simple1')->getId();
        $id2 = $productRepository->get('simple2')->getId();
        $id3 = $productRepository->get('simple3')->getId();
        $existingProductIds = [$id1, $id2, $id3];
        $productsBeforeImport = [];
        foreach ($existingProductIds as $productId) {
            $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
                \Magento\Catalog\Model\Product::class
            );
            $product->load($productId);
            $productsBeforeImport[] = $product;
        }

        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Framework\Filesystem::class);
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);

        $source = $this->objectManager->create(
            \Magento\ImportExport\Model\Import\Source\Csv::class,
            [
                'file' => __DIR__ . '/_files/products_to_import.csv',
                'directory' => $directory
            ]
        );
        $errors = $this->_model->setParameters(
            ['behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND, 'entity' => 'catalog_product']
        )->setSource(
            $source
        )->validateData();

        $this->assertTrue($errors->getErrorsCount() == 0);

        $this->_model->importData();

        /** @var $productBeforeImport \Magento\Catalog\Model\Product */
        foreach ($productsBeforeImport as $productBeforeImport) {
            /** @var $productAfterImport \Magento\Catalog\Model\Product */
            $productAfterImport = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
                \Magento\Catalog\Model\Product::class
            );
            $productAfterImport->load($productBeforeImport->getId());

            $this->assertEquals($productBeforeImport->getVisibility(), $productAfterImport->getVisibility());
            unset($productAfterImport);
        }

        unset($productsBeforeImport, $product);
    }

    /**
     * Test if stock item quantity properly saved after import
     *
     * @magentoDataFixture Magento/Catalog/_files/multiple_products.php
     * @magentoAppIsolation enabled
     */
    public function testSaveStockItemQty()
    {
        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
        $productRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Api\ProductRepositoryInterface::class
        );
        $id1 = $productRepository->get('simple1')->getId();
        $id2 = $productRepository->get('simple2')->getId();
        $id3 = $productRepository->get('simple3')->getId();
        $existingProductIds = [$id1, $id2, $id3];
        $stockItems = [];
        foreach ($existingProductIds as $productId) {
            /** @var $stockRegistry \Magento\CatalogInventory\Model\StockRegistry */
            $stockRegistry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
                \Magento\CatalogInventory\Model\StockRegistry::class
            );

            $stockItem = $stockRegistry->getStockItem($productId, 1);
            $stockItems[$productId] = $stockItem;
        }

        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Framework\Filesystem::class);
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = $this->objectManager->create(
            \Magento\ImportExport\Model\Import\Source\Csv::class,
            [
                'file' => __DIR__ . '/_files/products_to_import.csv',
                'directory' => $directory
            ]
        );
        $errors = $this->_model->setParameters(
            ['behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND, 'entity' => 'catalog_product']
        )->setSource(
            $source
        )->validateData();

        $this->assertTrue($errors->getErrorsCount() == 0);

        $this->_model->importData();

        /** @var $stockItmBeforeImport \Magento\CatalogInventory\Model\Stock\Item */
        foreach ($stockItems as $productId => $stockItmBeforeImport) {
            /** @var $stockRegistry \Magento\CatalogInventory\Model\StockRegistry */
            $stockRegistry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
                \Magento\CatalogInventory\Model\StockRegistry::class
            );

            $stockItemAfterImport = $stockRegistry->getStockItem($productId, 1);

            $this->assertEquals($stockItmBeforeImport->getQty(), $stockItemAfterImport->getQty());
            $this->assertEquals(1, $stockItemAfterImport->getIsInStock());
            unset($stockItemAfterImport);
        }

        unset($stockItems, $stockItem);
    }

    /**
     * Test if stock state properly changed after import
     *
     * @magentoDataFixture Magento/Catalog/_files/multiple_products.php
     * @magentoAppIsolation enabled
     */
    public function testStockState()
    {
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Framework\Filesystem::class);
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = $this->objectManager->create(
            \Magento\ImportExport\Model\Import\Source\Csv::class,
            [
                'file' => __DIR__ . '/_files/products_to_import_with_qty.csv',
                'directory' => $directory
            ]
        );

        $errors = $this->_model->setParameters(
            ['behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND, 'entity' => 'catalog_product']
        )->setSource(
            $source
        )->validateData();

        $this->assertTrue($errors->getErrorsCount() == 0);
        $this->_model->importData();
    }

    /**
     * Tests adding of custom options with existing and new product.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @dataProvider getBehaviorDataProvider
     * @param string $importFile
     * @param string $sku
     * @param int $expectedOptionsQty
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @magentoAppIsolation enabled

     *
     * @return void
     */
    public function testSaveCustomOptions(string $importFile, string $sku, int $expectedOptionsQty): void
    {
        $pathToFile = __DIR__ . '/_files/' . $importFile;
        $importModel = $this->createImportModel($pathToFile);
        $errors = $importModel->validateData();

        $this->assertTrue($errors->getErrorsCount() == 0);
        $importModel->importData();

        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
        $productRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Api\ProductRepositoryInterface::class
        );
        $product = $productRepository->get($sku);

        $this->assertInstanceOf(\Magento\Catalog\Model\Product::class, $product);
        $options = $product->getOptionInstance()->getProductOptions($product);

        $expectedData = $this->getExpectedOptionsData($pathToFile);
        $expectedData = $this->mergeWithExistingData($expectedData, $options);
        $actualData = $this->getActualOptionsData($options);

        // assert of equal type+titles
        $expectedOptions = $expectedData['options'];
        // we need to save key values
        $actualOptions = $actualData['options'];
        sort($expectedOptions);
        sort($actualOptions);
        $this->assertEquals($expectedOptions, $actualOptions);

        // assert of options data
        $this->assertCount(count($expectedData['data']), $actualData['data']);
        $this->assertCount(count($expectedData['values']), $actualData['values']);
        $this->assertCount($expectedOptionsQty, $actualData['options']);
        foreach ($expectedData['options'] as $expectedId => $expectedOption) {
            $elementExist = false;
            // find value in actual options and values
            foreach ($actualData['options'] as $actualId => $actualOption) {
                if ($actualOption == $expectedOption) {
                    $elementExist = true;
                    $this->assertEquals($expectedData['data'][$expectedId], $actualData['data'][$actualId]);
                    if (array_key_exists($expectedId, $expectedData['values'])) {
                        $this->assertEquals($expectedData['values'][$expectedId], $actualData['values'][$actualId]);
                    }
                    unset($actualData['options'][$actualId]);
                    // remove value in case of duplicating key values
                    break;
                }
            }
            $this->assertTrue($elementExist, 'Element must exist.');
        }

        // Make sure that after importing existing options again, option IDs and option value IDs are not changed
        $customOptionValues = $this->getCustomOptionValues($sku);
        $this->createImportModel($pathToFile)->importData();
        $this->assertEquals($customOptionValues, $this->getCustomOptionValues($sku));
    }

    /**
     * Tests adding of custom options with multiple store views
     *
     * @magentoDataFixture Magento/Store/_files/second_store.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoAppIsolation enabled
     */
    public function testSaveCustomOptionsWithMultipleStoreViews()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\Store\Model\StoreManagerInterface $storeManager */
        $storeManager = $objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);
        $storeCodes = [
            'admin',
            'default',
            'fixture_second_store',
        ];
        /** @var \Magento\Store\Model\StoreManagerInterface $storeManager */
        $importFile = 'product_with_custom_options_and_multiple_store_views.csv';
        $sku = 'simple';
        $pathToFile = __DIR__ . '/_files/' . $importFile;
        $importModel = $this->createImportModel($pathToFile);
        $errors = $importModel->validateData();
        $this->assertTrue($errors->getErrorsCount() == 0);
        $importModel->importData();
        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
        $productRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Api\ProductRepositoryInterface::class
        );
        foreach ($storeCodes as $storeCode) {
            $storeManager->setCurrentStore($storeCode);
            $product = $productRepository->get($sku);
            $options = $product->getOptionInstance()->getProductOptions($product);
            $expectedData = $this->getExpectedOptionsData($pathToFile, $storeCode);
            $expectedData = $this->mergeWithExistingData($expectedData, $options);
            $actualData = $this->getActualOptionsData($options);
            // assert of equal type+titles
            $expectedOptions = $expectedData['options'];
            // we need to save key values
            $actualOptions = $actualData['options'];
            sort($expectedOptions);
            sort($actualOptions);
            $this->assertEquals($expectedOptions, $actualOptions);

            // assert of options data
            $this->assertCount(count($expectedData['data']), $actualData['data']);
            $this->assertCount(count($expectedData['values']), $actualData['values']);
            foreach ($expectedData['options'] as $expectedId => $expectedOption) {
                $elementExist = false;
                // find value in actual options and values
                foreach ($actualData['options'] as $actualId => $actualOption) {
                    if ($actualOption == $expectedOption) {
                        $elementExist = true;
                        $this->assertEquals($expectedData['data'][$expectedId], $actualData['data'][$actualId]);
                        if (array_key_exists($expectedId, $expectedData['values'])) {
                            $this->assertEquals($expectedData['values'][$expectedId], $actualData['values'][$actualId]);
                        }
                        unset($actualData['options'][$actualId]);
                        // remove value in case of duplicating key values
                        break;
                    }
                }
                $this->assertTrue($elementExist, 'Element must exist.');
            }

            // Make sure that after importing existing options again, option IDs and option value IDs are not changed
            $customOptionValues = $this->getCustomOptionValues($sku);
            $this->createImportModel($pathToFile)->importData();
            $this->assertEquals($customOptionValues, $this->getCustomOptionValues($sku));
        }
    }

    /**
     * @return array
     */
    public function getBehaviorDataProvider(): array
    {
        return [
            'Append behavior with existing product' => [
                'importFile' => 'product_with_custom_options.csv',
                'sku' => 'simple',
                'expectedOptionsQty' => 6,
            ],
            'Append behavior with existing product and without options in import file' => [
                'importFile' => 'product_without_custom_options.csv',
                'sku' => 'simple',
                'expectedOptionsQty' => 0,
            ],
            'Append behavior with new product' => [
                'importFile' => 'product_with_custom_options_new.csv',
                'sku' => 'simple_new',
                'expectedOptionsQty' => 5,
            ],
        ];
    }

    /**
     * @param string $pathToFile
     * @param string $behavior
     * @return \Magento\CatalogImportExport\Model\Import\Product
     */
    private function createImportModel($pathToFile, $behavior = \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND)
    {
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Framework\Filesystem::class);
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);

        /** @var \Magento\ImportExport\Model\Import\Source\Csv $source */
        $source = $this->objectManager->create(
            \Magento\ImportExport\Model\Import\Source\Csv::class,
            [
                'file' => $pathToFile,
                'directory' => $directory
            ]
        );

        /** @var \Magento\CatalogImportExport\Model\Import\Product $importModel */
        $importModel = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\CatalogImportExport\Model\Import\Product::class
        );
        $importModel->setParameters(['behavior' => $behavior, 'entity' => 'catalog_product'])->setSource($source);

        return $importModel;
    }

    /**
     * @param string $productSku
     * @return array ['optionId' => ['optionValueId' => 'optionValueTitle', ...], ...]
     */
    private function getCustomOptionValues($productSku)
    {
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        /** @var ProductCustomOptionRepositoryInterface $customOptionRepository */
        $customOptionRepository = $this->objectManager->get(ProductCustomOptionRepositoryInterface::class);
        $simpleProduct = $productRepository->get($productSku, false, null, true);
        $originalProductOptions = $customOptionRepository->getProductOptions($simpleProduct);
        $optionValues = [];
        foreach ($originalProductOptions as $productOption) {
            foreach ((array)$productOption->getValues() as $optionValue) {
                $optionValues[$productOption->getOptionId()][$optionValue->getOptionTypeId()]
                    = $optionValue->getTitle();
            }
        }
        return $optionValues;
    }

    /**
     * Test if datetime properly saved after import
     *
     * @magentoDataFixture Magento/Catalog/_files/multiple_products.php
     * @magentoAppIsolation enabled
     */
    public function testSaveDatetimeAttribute()
    {
        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
        $productRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Api\ProductRepositoryInterface::class
        );
        $id1 = $productRepository->get('simple1')->getId();
        $id2 = $productRepository->get('simple2')->getId();
        $id3 = $productRepository->get('simple3')->getId();
        $existingProductIds = [$id1, $id2, $id3];
        $productsBeforeImport = [];
        foreach ($existingProductIds as $productId) {
            $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
                \Magento\Catalog\Model\Product::class
            );
            $product->load($productId);
            $productsBeforeImport[$product->getSku()] = $product;
        }

        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Framework\Filesystem::class);
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);

        $source = $this->objectManager->create(
            \Magento\ImportExport\Model\Import\Source\Csv::class,
            [
                'file' => __DIR__ . '/_files/products_to_import_with_datetime.csv',
                'directory' => $directory
            ]
        );
        $errors = $this->_model->setParameters(
            ['behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND, 'entity' => 'catalog_product']
        )->setSource(
            $source
        )->validateData();

        $this->assertTrue($errors->getErrorsCount() == 0);

        $this->_model->importData();

        $source->rewind();
        foreach ($source as $row) {
            /** @var $productAfterImport \Magento\Catalog\Model\Product */
            $productBeforeImport = $productsBeforeImport[$row['sku']];

            /** @var $productAfterImport \Magento\Catalog\Model\Product */
            $productAfterImport = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
                \Magento\Catalog\Model\Product::class
            );
            $productAfterImport->load($productBeforeImport->getId());
            $this->assertEquals(
                @strtotime(date('m/d/Y', @strtotime($row['news_from_date']))),
                @strtotime($productAfterImport->getNewsFromDate())
            );
            $this->assertEquals(
                @strtotime($row['news_to_date']),
                @strtotime($productAfterImport->getNewsToDate())
            );
            unset($productAfterImport);
        }
        unset($productsBeforeImport, $product);
    }

    /**
     * Returns expected product data: current id, options, options data and option values
     *
     * @param string $pathToFile
     * @param string $storeCode
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function getExpectedOptionsData(string $pathToFile, string $storeCode = ''): array
    {
        // phpcs:disable Magento2.Functions.DiscouragedFunction
        $productData = $this->csvToArray(file_get_contents($pathToFile));
        $expectedOptionId = 0;
        $expectedOptions = [];
        // array of type and title types, key is element ID
        $expectedData = [];
        // array of option data
        $expectedValues = [];
        $storeRowId = null;
        foreach ($productData['data'] as $rowId => $rowData) {
            $storeCode = ($storeCode == 'admin') ? '' : $storeCode;
            if ($rowData['store_view_code'] == $storeCode) {
                $storeRowId = $rowId;
                break;
            }
        }
        if (!empty($productData['data'][$storeRowId]['custom_options'])) {
            foreach (explode('|', $productData['data'][$storeRowId]['custom_options']) as $optionData) {
                $option = array_values(
                    array_map(
                        function ($input) {
                            $data = explode('=', $input);
                            return [$data[0] => $data[1]];
                        },
                        explode(',', $optionData)
                    )
                );
                $option = array_merge(...$option);

                if (!empty($option['type']) && !empty($option['name'])) {
                    $lastOptionKey = $option['type'] . '|' . $option['name'];
                    if (!isset($expectedOptions[$expectedOptionId])
                        || $expectedOptions[$expectedOptionId] != $lastOptionKey) {
                        $expectedOptionId++;
                        $expectedOptions[$expectedOptionId] = $lastOptionKey;
                        $expectedData[$expectedOptionId] = [];
                        foreach ($this->_assertOptions as $assertKey => $assertFieldName) {
                            if (array_key_exists($assertFieldName, $option)
                                && !(($assertFieldName == 'price' || $assertFieldName == 'sku')
                                    && in_array($option['type'], $this->specificTypes))
                            ) {
                                $expectedData[$expectedOptionId][$assertKey] = $option[$assertFieldName];
                            }
                        }
                    }
                }
                $optionValue = [];
                if (!empty($option['name']) && !empty($option['option_title'])) {
                    foreach ($this->_assertOptionValues as $assertKey => $assertFieldName) {
                        if (isset($option[$assertFieldName])) {
                            $optionValue[$assertKey] = $option[$assertFieldName];
                        }
                    }
                    $expectedValues[$expectedOptionId][] = $optionValue;
                }
            }
        }

        return [
            'id' => $expectedOptionId,
            'options' => $expectedOptions,
            'data' => $expectedData,
            'values' => $expectedValues,
        ];
    }

    /**
     * Updates expected options data array with existing unique options data
     *
     * @param array $expected
     * @param \Magento\Catalog\Model\ResourceModel\Product\Option\Collection $options
     * @return array
     */
    protected function mergeWithExistingData(
        array $expected,
        $options
    ) {
        $expectedOptionId = $expected['id'];
        $expectedOptions = $expected['options'];
        $expectedData = $expected['data'];
        $expectedValues = $expected['values'];
        foreach ($options as $option) {
            $optionKey = $option->getType() . '|' . $option->getTitle();
            $optionValues = $this->getOptionValues($option);
            if (!in_array($optionKey, $expectedOptions)) {
                $expectedOptionId++;
                $expectedOptions[$expectedOptionId] = $optionKey;
                $expectedData[$expectedOptionId] = $this->getOptionData($option);
                if ($optionValues) {
                    $expectedValues[$expectedOptionId] = $optionValues;
                }
            } else {
                $existingOptionId = array_search($optionKey, $expectedOptions);
                $expectedData[$existingOptionId] = array_merge(
                    $this->getOptionData($option),
                    $expectedData[$existingOptionId]
                );
                if ($optionValues) {
                    foreach ($optionValues as $optionKey => $optionValue) {
                        $expectedValues[$existingOptionId][$optionKey] = array_merge(
                            $optionValue,
                            $expectedValues[$existingOptionId][$optionKey]
                        );
                    }
                }
            }
        }

        return [
            'id' => $expectedOptionId,
            'options' => $expectedOptions,
            'data' => $expectedData,
            'values' => $expectedValues
        ];
    }

    /**
     *  Returns actual product data: current id, options, options data and option values
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Option\Collection $options
     * @return array
     */
    protected function getActualOptionsData($options)
    {
        $actualOptionId = 0;
        $actualOptions = [];
        // array of type and title types, key is element ID
        $actualData = [];
        // array of option data
        $actualValues = [];
        // array of option values data
        /** @var $option \Magento\Catalog\Model\Product\Option */
        foreach ($options as $option) {
            $lastOptionKey = $option->getType() . '|' . $option->getTitle();
            $actualOptionId++;
            if (!in_array($lastOptionKey, $actualOptions)) {
                $actualOptions[$actualOptionId] = $lastOptionKey;
                $actualData[$actualOptionId] = $this->getOptionData($option);
                if ($optionValues = $this->getOptionValues($option)) {
                    $actualValues[$actualOptionId] = $optionValues;
                }
            }
        }
        return [
            'id' => $actualOptionId,
            'options' => $actualOptions,
            'data' => $actualData,
            'values' => $actualValues
        ];
    }

    /**
     * Retrieve option data
     *
     * @param \Magento\Catalog\Model\Product\Option $option
     * @return array
     */
    protected function getOptionData(\Magento\Catalog\Model\Product\Option $option)
    {
        $result = [];
        foreach (array_keys($this->_assertOptions) as $assertKey) {
            $result[$assertKey] = $option->getData($assertKey);
        }
        return $result;
    }

    /**
     * Retrieve option values or false for options which has no values
     *
     * @param \Magento\Catalog\Model\Product\Option $option
     * @return array|bool
     */
    protected function getOptionValues(\Magento\Catalog\Model\Product\Option $option)
    {
        $values = $option->getValues();
        if (!empty($values)) {
            $result = [];
            /** @var $value \Magento\Catalog\Model\Product\Option\Value */
            foreach ($values as $value) {
                $optionData = [];
                foreach (array_keys($this->_assertOptionValues) as $assertKey) {
                    if ($value->hasData($assertKey)) {
                        $optionData[$assertKey] = $value->getData($assertKey);
                    }
                }
                $result[] = $optionData;
            }
            return $result;
        }

        return false;
    }

    /**
     * Test that product import with images works properly
     *
     * @magentoDataFixture mediaImportImageFixture
     * @magentoAppIsolation enabled
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testSaveMediaImage()
    {
        $this->importDataForMediaTest('import_media.csv');

        $product = $this->getProductBySku('simple_new');

        $this->assertEquals('/m/a/magento_image.jpg', $product->getData('image'));
        $this->assertEquals('/m/a/magento_small_image.jpg', $product->getData('small_image'));
        $this->assertEquals('/m/a/magento_thumbnail.jpg', $product->getData('thumbnail'));
        $this->assertEquals('/m/a/magento_image.jpg', $product->getData('swatch_image'));

        $gallery = $product->getMediaGalleryImages();
        $this->assertInstanceOf(\Magento\Framework\Data\Collection::class, $gallery);

        $items = $gallery->getItems();
        $this->assertCount(5, $items);

        $imageItem = array_shift($items);
        $this->assertInstanceOf(\Magento\Framework\DataObject::class, $imageItem);
        $this->assertEquals('/m/a/magento_image.jpg', $imageItem->getFile());
        $this->assertEquals('Image Label', $imageItem->getLabel());

        $smallImageItem = array_shift($items);
        $this->assertInstanceOf(\Magento\Framework\DataObject::class, $smallImageItem);
        $this->assertEquals('/m/a/magento_small_image.jpg', $smallImageItem->getFile());
        $this->assertEquals('Small Image Label', $smallImageItem->getLabel());

        $thumbnailItem = array_shift($items);
        $this->assertInstanceOf(\Magento\Framework\DataObject::class, $thumbnailItem);
        $this->assertEquals('/m/a/magento_thumbnail.jpg', $thumbnailItem->getFile());
        $this->assertEquals('Thumbnail Label', $thumbnailItem->getLabel());

        $additionalImageOneItem = array_shift($items);
        $this->assertInstanceOf(\Magento\Framework\DataObject::class, $additionalImageOneItem);
        $this->assertEquals('/m/a/magento_additional_image_one.jpg', $additionalImageOneItem->getFile());
        $this->assertEquals('Additional Image Label One', $additionalImageOneItem->getLabel());

        $additionalImageTwoItem = array_shift($items);
        $this->assertInstanceOf(\Magento\Framework\DataObject::class, $additionalImageTwoItem);
        $this->assertEquals('/m/a/magento_additional_image_two.jpg', $additionalImageTwoItem->getFile());
        $this->assertEquals('Additional Image Label Two', $additionalImageTwoItem->getLabel());
    }

    /**
     * Test that errors occurred during importing images are logged.
     *
     * @magentoAppIsolation enabled
     * @magentoDataFixture mediaImportImageFixture
     * @magentoDataFixture mediaImportImageFixtureError
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testSaveMediaImageError()
    {
        $this->logger->expects(self::once())->method('critical');
        $this->importDataForMediaTest('import_media.csv', 1);
    }

    /**
     * Copy fixture images into media import directory
     */
    public static function mediaImportImageFixture()
    {
        /** @var \Magento\Framework\Filesystem\Directory\Write $mediaDirectory */
        $mediaDirectory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\Filesystem::class
        )->getDirectoryWrite(
            DirectoryList::MEDIA
        );

        $mediaDirectory->create('import');
        $dirPath = $mediaDirectory->getAbsolutePath('import');

        $items = [
            [
                'source' => __DIR__ . '/../../../../Magento/Catalog/_files/magento_image.jpg',
                'dest' => $dirPath . '/magento_image.jpg',
            ],
            [
                'source' => __DIR__ . '/../../../../Magento/Catalog/_files/magento_small_image.jpg',
                'dest' => $dirPath . '/magento_small_image.jpg',
            ],
            [
                'source' => __DIR__ . '/../../../../Magento/Catalog/_files/magento_thumbnail.jpg',
                'dest' => $dirPath . '/magento_thumbnail.jpg',
            ],
            [
                'source' => __DIR__ . '/_files/magento_additional_image_one.jpg',
                'dest' => $dirPath . '/magento_additional_image_one.jpg',
            ],
            [
                'source' => __DIR__ . '/_files/magento_additional_image_two.jpg',
                'dest' => $dirPath . '/magento_additional_image_two.jpg',
            ],
        ];

        foreach ($items as $item) {
            copy($item['source'], $item['dest']);
        }
    }

    /**
     * Cleanup media import and catalog directories
     */
    public static function mediaImportImageFixtureRollback()
    {
        /** @var \Magento\Framework\Filesystem\Directory\Write $mediaDirectory */
        $mediaDirectory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\Filesystem::class
        )->getDirectoryWrite(
            DirectoryList::MEDIA
        );
        $mediaDirectory->delete('import');
        $mediaDirectory->delete('catalog');
    }

    /**
     * Copy incorrect fixture image into media import directory.
     */
    public static function mediaImportImageFixtureError()
    {
        /** @var \Magento\Framework\Filesystem\Directory\Write $mediaDirectory */
        $mediaDirectory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\Filesystem::class
        )->getDirectoryWrite(
            DirectoryList::MEDIA
        );
        $dirPath = $mediaDirectory->getAbsolutePath('import');
        $items = [
            [
                'source' => __DIR__ . '/_files/magento_additional_image_error.jpg',
                'dest' => $dirPath . '/magento_additional_image_two.jpg',
            ],
        ];
        foreach ($items as $item) {
            copy($item['source'], $item['dest']);
        }
    }

    /**
     * Export CSV string to array
     *
     * @param string $content
     * @param mixed $entityId
     * @return array
     */
    protected function csvToArray($content, $entityId = null)
    {
        $data = ['header' => [], 'data' => []];

        $lines = str_getcsv($content, "\n");
        foreach ($lines as $index => $line) {
            if ($index == 0) {
                $data['header'] = str_getcsv($line);
            } else {
                $row = array_combine($data['header'], str_getcsv($line));
                if ($entityId !== null && !empty($row[$entityId])) {
                    $data['data'][$row[$entityId]] = $row;
                } else {
                    $data['data'][] = $row;
                }
            }
        }
        return $data;
    }

    /**
     * Tests that no products imported if source file contains errors
     *
     * In this case, the second product data has an invalid attribute set.
     *
     * @magentoDbIsolation enabled
     */
    public function testInvalidSkuLink()
    {
        // import data from CSV file
        $pathToFile = __DIR__ . '/_files/products_to_import_invalid_attribute_set.csv';
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Framework\Filesystem::class
        );
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = $this->objectManager->create(
            \Magento\ImportExport\Model\Import\Source\Csv::class,
            [
                'file' => $pathToFile,
                'directory' => $directory
            ]
        );
        $errors = $this->_model->setParameters(
            [
                'behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND,
                Import::FIELD_NAME_VALIDATION_STRATEGY => null,
                'entity' => 'catalog_product'
            ]
        )->setSource(
            $source
        )->validateData();

        $this->assertTrue($errors->getErrorsCount() == 1);
        $this->assertEquals(
            'Invalid value for Attribute Set column (set doesn\'t exist?)',
            $errors->getErrorByRowNumber(1)[0]->getErrorMessage()
        );
        $this->_model->importData();

        $productCollection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\ResourceModel\Product\Collection::class
        );

        $products = [];
        /** @var $product \Magento\Catalog\Model\Product */
        foreach ($productCollection as $product) {
            $products[$product->getSku()] = $product;
        }
        $this->assertArrayNotHasKey("simple1", $products, "Simple Product should not have been imported");
        $this->assertArrayNotHasKey("simple3", $products, "Simple Product 3 should not have been imported");
        $this->assertArrayNotHasKey("simple2", $products, "Simple Product2 should not have been imported");
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/products_with_multiselect_attribute.php
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testValidateInvalidMultiselectValues()
    {
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Framework\Filesystem::class
        );
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = $this->objectManager->create(
            \Magento\ImportExport\Model\Import\Source\Csv::class,
            [
                'file' => __DIR__ . '/_files/products_with_invalid_multiselect_values.csv',
                'directory' => $directory
            ]
        );
        $errors = $this->_model->setParameters(
            ['behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND, 'entity' => 'catalog_product']
        )->setSource(
            $source
        )->validateData();

        $this->assertTrue($errors->getErrorsCount() == 1);
        $this->assertEquals(
            "Value for 'multiselect_attribute' attribute contains incorrect value, "
            . "see acceptable values on settings specified for Admin",
            $errors->getErrorByRowNumber(1)[0]->getErrorMessage()
        );
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/categories.php
     * @magentoDataFixture Magento/Store/_files/website.php
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     * @magentoDataFixture Magento/Catalog/Model/Layer/Filter/_files/attribute_with_option.php
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_attribute.php
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     */
    public function testProductsWithMultipleStores()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $filesystem = $objectManager->create(\Magento\Framework\Filesystem::class);
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = $this->objectManager->create(
            \Magento\ImportExport\Model\Import\Source\Csv::class,
            [
                'file' => __DIR__ . '/_files/products_multiple_stores.csv',
                'directory' => $directory
            ]
        );
        $errors = $this->_model->setParameters(
            ['behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND, 'entity' => 'catalog_product']
        )->setSource(
            $source
        )->validateData();

        $this->assertTrue($errors->getErrorsCount() == 0);

        $this->_model->importData();

        /** @var \Magento\Catalog\Model\Product $product */
        $product = $objectManager->create(\Magento\Catalog\Model\Product::class);
        $id = $product->getIdBySku('Configurable 03');
        $product->load($id);
        $this->assertEquals('1', $product->getHasOptions());

        $objectManager->get(\Magento\Store\Model\StoreManagerInterface::class)->setCurrentStore('fixturestore');

        /** @var \Magento\Catalog\Model\Product $simpleProduct */
        $simpleProduct = $objectManager->create(\Magento\Catalog\Model\Product::class);
        $id = $simpleProduct->getIdBySku('Configurable 03-Option 1');
        $simpleProduct->load($id);
        $this->assertTrue(count($simpleProduct->getWebsiteIds()) == 2);
        $this->assertEquals('Option Label', $simpleProduct->getAttributeText('attribute_with_option'));
    }

    /**
     * Test url keys properly generated in multistores environment.
     *
     * @magentoConfigFixture current_store catalog/seo/product_use_categories 1
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     * @magentoDataFixture Magento/Catalog/_files/category_with_two_stores.php
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testGenerateUrlsWithMultipleStores()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $filesystem = $objectManager->create(\Magento\Framework\Filesystem::class);
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = $this->objectManager->create(
            \Magento\ImportExport\Model\Import\Source\Csv::class,
            [
                'file' => __DIR__ . '/_files/products_to_import_with_two_stores.csv',
                'directory' => $directory
            ]
        );
        $errors = $this->_model->setParameters(
            ['behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE, 'entity' => 'catalog_product']
        )->setSource(
            $source
        )->validateData();

        $this->assertTrue($errors->getErrorsCount() == 0);

        $this->_model->importData();
        $this->assertProductRequestPath('default', 'category-defaultstore/product-default.html');
        $this->assertProductRequestPath('fixturestore', 'category-fixturestore/product-fixture.html');
    }

    /**
     * Check product request path considering store scope.
     *
     * @param string $storeCode
     * @param string $expected
     * @return void
     */
    private function assertProductRequestPath($storeCode, $expected)
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var Store $storeCode */
        $store = $objectManager->get(Store::class);
        $storeId = $store->load($storeCode)->getId();

        /** @var Category $category */
        $category = $objectManager->get(Category::class);
        $category->setStoreId($storeId);
        $category->load(555);

        /** @var Registry $registry */
        $registry = $objectManager->get(Registry::class);
        $registry->register('current_category', $category);

        /** @var \Magento\Catalog\Model\Product $product */
        $product = $objectManager->create(\Magento\Catalog\Model\Product::class);
        $id = $product->getIdBySku('product');
        $product->setStoreId($storeId);
        $product->load($id);
        $product->getProductUrl();
        self::assertEquals($expected, $product->getRequestPath());
        $registry->unregister('current_category');
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testProductWithInvalidWeight()
    {
        // import data from CSV file
        $pathToFile = __DIR__ . '/_files/product_to_import_invalid_weight.csv';
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Framework\Filesystem::class
        );

        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = $this->objectManager->create(
            \Magento\ImportExport\Model\Import\Source\Csv::class,
            [
                'file' => $pathToFile,
                'directory' => $directory
            ]
        );
        $errors = $this->_model->setSource(
            $source
        )->setParameters(
            ['behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND]
        )->validateData();

        $this->assertTrue($errors->getErrorsCount() == 1);
        $this->assertEquals(
            "Value for 'weight' attribute contains incorrect value",
            $errors->getErrorByRowNumber(1)[0]->getErrorMessage()
        );
    }

    /**
     * @magentoAppArea adminhtml
     * @dataProvider categoryTestDataProvider
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testProductCategories($fixture, $separator)
    {
        // import data from CSV file
        $pathToFile = __DIR__ . '/_files/' . $fixture;
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Framework\Filesystem::class
        );

        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = $this->objectManager->create(
            \Magento\ImportExport\Model\Import\Source\Csv::class,
            [
                'file' => $pathToFile,
                'directory' => $directory
            ]
        );
        $errors = $this->_model->setSource(
            $source
        )->setParameters(
            [
                'behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND,
                'entity' => 'catalog_product',
                Import::FIELD_FIELD_MULTIPLE_VALUE_SEPARATOR => $separator
            ]
        )->validateData();

        $this->assertTrue($errors->getErrorsCount() == 0);
        $this->_model->importData();

        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $resource = $objectManager->get(\Magento\Catalog\Model\ResourceModel\Product::class);
        $productId = $resource->getIdBySku('simple1');
        $this->assertTrue(is_numeric($productId));
        /** @var \Magento\Catalog\Model\Product $product */
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Product::class
        );
        $product->load($productId);
        $this->assertFalse($product->isObjectNew());
        $categories = $product->getCategoryIds();
        $this->assertTrue(count($categories) == 2);
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/multiple_products.php
     * @magentoDataFixture Magento/Catalog/_files/category.php
     */
    public function testProductPositionInCategory()
    {
        /* @var \Magento\Catalog\Model\ResourceModel\Category\Collection $collection */
        $collection = $this->objectManager->create(\Magento\Catalog\Model\ResourceModel\Category\Collection::class);
        $collection->addNameToResult()->load();
        /** @var Category $category */
        $category = $collection->getItemByColumnValue('name', 'Category 1');

        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->create(ProductRepositoryInterface::class);

        $categoryProducts = [];
        $i = 51;
        foreach (['simple1', 'simple2', 'simple3'] as $sku) {
            $categoryProducts[$productRepository->get($sku)->getId()] = $i++;
        }
        $category->setPostedProducts($categoryProducts);
        $category->save();

        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Framework\Filesystem::class
        );

        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = $this->objectManager->create(
            \Magento\ImportExport\Model\Import\Source\Csv::class,
            [
                'file' => __DIR__ . '/_files/products_to_import.csv',
                'directory' => $directory
            ]
        );
        $errors = $this->_model->setSource(
            $source
        )->setParameters(
            [
                'behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND,
                'entity' => 'catalog_product'
            ]
        )->validateData();

        $this->assertTrue($errors->getErrorsCount() == 0);
        $this->_model->importData();

        /** @var \Magento\Framework\App\ResourceConnection $resourceConnection */
        $resourceConnection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\App\ResourceConnection::class
        );
        $tableName = $resourceConnection->getTableName('catalog_category_product');
        $select = $resourceConnection->getConnection()->select()->from($tableName)
            ->where('category_id = ?', $category->getId());
        $items = $resourceConnection->getConnection()->fetchAll($select);
        $this->assertCount(3, $items);
        foreach ($items as $item) {
            $this->assertGreaterThan(50, $item['position']);
        }
    }

    /**
     * @return array
     */
    public function categoryTestDataProvider()
    {
        return [
            ['import_new_categories_default_separator.csv', ','],
            ['import_new_categories_custom_separator.csv', '|']
        ];
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/CatalogImportExport/_files/update_category_duplicates.php
     */
    public function testProductDuplicateCategories()
    {
        $csvFixture = 'products_duplicate_category.csv';
        // import data from CSV file
        $pathToFile = __DIR__ . '/_files/' . $csvFixture;
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Framework\Filesystem::class
        );

        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = $this->objectManager->create(
            \Magento\ImportExport\Model\Import\Source\Csv::class,
            [
                'file' => $pathToFile,
                'directory' => $directory
            ]
        );
        $errors = $this->_model->setSource($source)->setParameters(
            [
                'behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND,
                'entity' => 'catalog_product'
            ]
        )->validateData();

        $this->assertTrue($errors->getErrorsCount() === 0);

        $this->_model->importData();

        $errorProcessor = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregator::class
        );
        $errorCount = count($errorProcessor->getAllErrors());
        $this->assertTrue($errorCount === 1, 'Error expected');

        $errorMessage = $errorProcessor->getAllErrors()[0]->getErrorMessage();
        $this->assertContains('URL key for specified store already exists', $errorMessage);
        $this->assertContains('Default Category/Category 2', $errorMessage);

        $categoryAfter = $this->loadCategoryByName('Category 2');
        $this->assertTrue($categoryAfter === null);

        /** @var \Magento\Catalog\Model\Product $product */
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Product::class
        );
        $product->load(1);
        $categories = $product->getCategoryIds();
        $this->assertTrue(count($categories) == 1);
    }

    protected function loadCategoryByName($categoryName)
    {
        /* @var \Magento\Catalog\Model\ResourceModel\Category\Collection $collection */
        $collection = $this->objectManager->create(\Magento\Catalog\Model\ResourceModel\Category\Collection::class);
        $collection->addNameToResult()->load();
        return $collection->getItemByColumnValue('name', $categoryName);
    }

    /**
     * @magentoDataFixture Magento/Catalog/Model/ResourceModel/_files/product_simple.php
     * @magentoAppIsolation enabled
     * @dataProvider validateUrlKeysDataProvider
     * @param $importFile string
     * @param $expectedErrors array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testValidateUrlKeys($importFile, $expectedErrors)
    {
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Framework\Filesystem::class
        );
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);

        $source = $this->objectManager->create(
            \Magento\ImportExport\Model\Import\Source\Csv::class,
            [
                'file' => __DIR__ . '/_files/' . $importFile,
                'directory' => $directory
            ]
        );
        /** @var \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface $errors */
        $errors = $this->_model->setParameters(
            ['behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND, 'entity' => 'catalog_product']
        )->setSource(
            $source
        )->validateData();
        $this->assertEquals(
            $expectedErrors[RowValidatorInterface::ERROR_DUPLICATE_URL_KEY],
            count($errors->getErrorsByCode([RowValidatorInterface::ERROR_DUPLICATE_URL_KEY]))
        );
    }

    /**
     * @return array
     */
    public function validateUrlKeysDataProvider()
    {
        return [
            [
                'products_to_check_valid_url_keys.csv',
                 [
                     RowValidatorInterface::ERROR_DUPLICATE_URL_KEY => 0
                 ]
            ],
            [
                'products_to_check_duplicated_url_keys.csv',
                [
                    RowValidatorInterface::ERROR_DUPLICATE_URL_KEY => 2
                ]
            ],
            [
                'products_to_check_duplicated_names.csv' ,
                [
                    RowValidatorInterface::ERROR_DUPLICATE_URL_KEY => 1
                ]
            ]
        ];
    }

    /**
     * @magentoDataFixture Magento/Store/_files/website.php
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     * @magentoDataFixture Magento/Catalog/Model/ResourceModel/_files/product_simple.php
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testValidateUrlKeysMultipleStores()
    {
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Framework\Filesystem::class
        );
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);

        $source = $this->objectManager->create(
            \Magento\ImportExport\Model\Import\Source\Csv::class,
            [
                'file' => __DIR__ . '/_files/products_to_check_valid_url_keys_multiple_stores.csv',
                'directory' => $directory
            ]
        );
        $errors = $this->_model->setParameters(
            ['behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND, 'entity' => 'catalog_product']
        )->setSource(
            $source
        )->validateData();

        $this->assertTrue($errors->getErrorsCount() == 0);
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testProductWithLinks()
    {
        $linksData = [
            'upsell' => [
                'simple1' => '3',
                'simple3' => '1'
            ],
            'crosssell' => [
                'simple2' => '1',
                'simple3' => '2'
            ],
            'related' => [
                'simple1' => '2',
                'simple2' => '1'
            ]
        ];
        // import data from CSV file
        $pathToFile = __DIR__ . '/_files/products_to_import_with_product_links.csv';
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Framework\Filesystem::class
        );

        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = $this->objectManager->create(
            \Magento\ImportExport\Model\Import\Source\Csv::class,
            [
                'file' => $pathToFile,
                'directory' => $directory
            ]
        );
        $errors = $this->_model->setSource(
            $source
        )->setParameters(
            [
                'behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND,
                'entity' => 'catalog_product'
            ]
        )->validateData();

        $this->assertTrue($errors->getErrorsCount() == 0);
        $this->_model->importData();

        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $resource = $objectManager->get(\Magento\Catalog\Model\ResourceModel\Product::class);
        $productId = $resource->getIdBySku('simple4');
        /** @var \Magento\Catalog\Model\Product $product */
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Product::class
        );
        $product->load($productId);
        $productLinks = [
            'upsell' => $product->getUpSellProducts(),
            'crosssell' => $product->getCrossSellProducts(),
            'related' => $product->getRelatedProducts()
        ];
        $importedProductLinks = [];
        foreach ($productLinks as $linkType => $linkedProducts) {
            foreach ($linkedProducts as $linkedProductData) {
                $importedProductLinks[$linkType][$linkedProductData->getSku()] = $linkedProductData->getPosition();
            }
        }
        $this->assertEquals($linksData, $importedProductLinks);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple_with_url_key.php
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     */
    public function testExistingProductWithUrlKeys()
    {
        $products = [
            'simple1' => 'url-key1',
            'simple2' => 'url-key2',
            'simple3' => 'url-key3'
        ];
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Framework\Filesystem::class);
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = $this->objectManager->create(
            \Magento\ImportExport\Model\Import\Source\Csv::class,
            [
                'file' => __DIR__ . '/_files/products_to_import_with_valid_url_keys.csv',
                'directory' => $directory
            ]
        );

        $errors = $this->_model->setParameters(
            ['behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND, 'entity' => 'catalog_product']
        )->setSource(
            $source
        )->validateData();

        $this->assertTrue($errors->getErrorsCount() == 0);
        $this->_model->importData();

        $productRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Api\ProductRepositoryInterface::class
        );
        foreach ($products as $productSku => $productUrlKey) {
            $this->assertEquals($productUrlKey, $productRepository->get($productSku)->getUrlKey());
        }
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple_with_wrong_url_key.php
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     */
    public function testAddUpdateProductWithInvalidUrlKeys() : void
    {
        $products = [
            'simple1' => 'cuvée merlot-cabernet igp pays d\'oc frankrijk',
            'simple2' => 'normal-url',
            'simple3' => 'some!wrong\'url'
        ];
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Framework\Filesystem::class);
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = $this->objectManager->create(
            \Magento\ImportExport\Model\Import\Source\Csv::class,
            [
                'file' => __DIR__ . '/_files/products_to_import_with_invalid_url_keys.csv',
                'directory' => $directory
            ]
        );

        $errors = $this->_model->setParameters(
            ['behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE, 'entity' => 'catalog_product']
        )->setSource(
            $source
        )->validateData();

        $this->assertTrue($errors->getErrorsCount() == 0);
        $this->_model->importData();

        $productRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Api\ProductRepositoryInterface::class
        );
        foreach ($products as $productSku => $productUrlKey) {
            $this->assertEquals($productUrlKey, $productRepository->get($productSku)->getUrlKey());
        }
    }

    /**
     * Make sure the non existing image in the csv file won't erase the qty key of the existing products.
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testImportWithNonExistingImage()
    {
        $products = [
            'simple_new' => 100,
        ];

        $this->importFile('products_to_import_with_non_existing_image.csv');

        $productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        foreach ($products as $productSku => $productQty) {
            $product = $productRepository->get($productSku);
            $stockItem = $product->getExtensionAttributes()->getStockItem();
            $this->assertEquals($productQty, $stockItem->getQty());
        }
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple_with_url_key.php
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     */
    public function testImportWithoutUrlKeys()
    {
        $products = [
            'simple1' => 'simple-1',
            'simple2' => 'simple-2',
            'simple3' => 'simple-3'
        ];
        $filesystem = $this->objectManager->create(\Magento\Framework\Filesystem::class);
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = $this->objectManager->create(
            \Magento\ImportExport\Model\Import\Source\Csv::class,
            [
                'file' => __DIR__ . '/_files/products_to_import_without_url_keys.csv',
                'directory' => $directory
            ]
        );

        $errors = $this->_model->setParameters(
            ['behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND, 'entity' => 'catalog_product']
        )
            ->setSource($source)
            ->validateData();

        $this->assertTrue($errors->getErrorsCount() == 0);
        $this->_model->importData();

        $productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        foreach ($products as $productSku => $productUrlKey) {
            $this->assertEquals($productUrlKey, $productRepository->get($productSku)->getUrlKey());
        }
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple_with_non_latin_url_key.php
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testImportWithNonLatinUrlKeys()
    {
        $productsCreatedByFixture = [
            'ukrainian-with-url-key' => 'nove-im-ja-pislja-importu-scho-stane-url-key',
            'ukrainian-without-url-key' => 'новий url key після імпорту',
        ];
        $productsImportedByCsv = [
            'imported-ukrainian-with-url-key' => 'імпортований продукт',
            'imported-ukrainian-without-url-key' => 'importovanij-produkt-bez-url-key',
        ];
        $productSkuMap = array_merge($productsCreatedByFixture, $productsImportedByCsv);
        $this->importedProducts = array_keys($productsImportedByCsv);

        $filesystem = $this->objectManager->create(\Magento\Framework\Filesystem::class);
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = $this->objectManager->create(
            \Magento\ImportExport\Model\Import\Source\Csv::class,
            [
                'file' => __DIR__ . '/_files/products_to_import_with_non_latin_url_keys.csv',
                'directory' => $directory,
            ]
        );

        $errors = $this->_model->setParameters(
            ['behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE, 'entity' => 'catalog_product']
        )
            ->setSource($source)
            ->validateData();

        $this->assertEquals($errors->getErrorsCount(), 0);
        $this->_model->importData();

        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        foreach ($productSkuMap as $productSku => $productUrlKey) {
            $this->assertEquals($productUrlKey, $productRepository->get($productSku)->getUrlKey());
        }
    }

    /**
     * Make sure the absence of a url_key column in the csv file won't erase the url key of the existing products.
     * To reach the goal we need to not send the name column, as the url key is generated from it.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple_with_url_key.php
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     */
    public function testImportWithoutUrlKeysAndName()
    {
        $products = [
            'simple1' => 'url-key',
            'simple2' => 'url-key2',
        ];
        $filesystem = $this->objectManager->create(\Magento\Framework\Filesystem::class);
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = $this->objectManager->create(
            \Magento\ImportExport\Model\Import\Source\Csv::class,
            [
                'file' => __DIR__ . '/_files/products_to_import_without_url_keys_and_name.csv',
                'directory' => $directory
            ]
        );

        $errors = $this->_model->setParameters(
            ['behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND, 'entity' => 'catalog_product']
        )
            ->setSource($source)
            ->validateData();

        $this->assertTrue($errors->getErrorsCount() == 0);
        $this->_model->importData();

        $productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        foreach ($products as $productSku => $productUrlKey) {
            $this->assertEquals($productUrlKey, $productRepository->get($productSku)->getUrlKey());
        }
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testProductWithUseConfigSettings()
    {
        $products = [
            'simple1' => true,
            'simple2' => true,
            'simple3' => false
        ];
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Framework\Filesystem::class);
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = $this->objectManager->create(
            \Magento\ImportExport\Model\Import\Source\Csv::class,
            [
                'file' => __DIR__ . '/_files/products_to_import_with_use_config_settings.csv',
                'directory' => $directory
            ]
        );

        $errors = $this->_model->setParameters(
            ['behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND, 'entity' => 'catalog_product']
        )->setSource(
            $source
        )->validateData();

        $this->assertTrue($errors->getErrorsCount() == 0);
        $this->_model->importData();

        foreach ($products as $sku => $manageStockUseConfig) {
            /** @var \Magento\CatalogInventory\Model\StockRegistry $stockRegistry */
            $stockRegistry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
                \Magento\CatalogInventory\Model\StockRegistry::class
            );
            $stockItem = $stockRegistry->getStockItemBySku($sku);
            $this->assertEquals($manageStockUseConfig, $stockItem->getUseConfigManageStock());
        }
    }

    /**
     * @magentoDataFixture Magento/Store/_files/website.php
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     */
    public function testProductWithMultipleStoresInDifferentBunches()
    {
        $products = [
            'simple1',
            'simple2',
            'simple3'
        ];

        $importExportData = $this->getMockBuilder(\Magento\ImportExport\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $importExportData->expects($this->atLeastOnce())
            ->method('getBunchSize')
            ->willReturn(1);
        $this->_model = $this->objectManager->create(
            \Magento\CatalogImportExport\Model\Import\Product::class,
            ['importExportData' => $importExportData]
        );

        $filesystem = $this->objectManager->create(\Magento\Framework\Filesystem::class);
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = $this->objectManager->create(
            \Magento\ImportExport\Model\Import\Source\Csv::class,
            [
                'file' => __DIR__ . '/_files/products_to_import_with_multiple_store.csv',
                'directory' => $directory
            ]
        );
        $errors = $this->_model->setParameters(
            ['behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND, 'entity' => 'catalog_product']
        )->setSource(
            $source
        )->validateData();

        $this->assertTrue($errors->getErrorsCount() == 0);

        $this->_model->importData();
        $productCollection = $this->objectManager
            ->create(\Magento\Catalog\Model\ResourceModel\Product\Collection::class);
        $this->assertCount(3, $productCollection->getItems());
        $actualProductSkus = array_map(
            function (ProductInterface $item) {
                return $item->getSku();
            },
            $productCollection->getItems()
        );
        sort($products);
        $result = array_values($actualProductSkus);
        sort($result);
        $this->assertEquals(
            $products,
            $result
        );

        /** @var \Magento\Framework\Registry $registry */
        $registry = $this->objectManager->get(\Magento\Framework\Registry::class);

        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', true);

        $productSkuList = ['simple1', 'simple2', 'simple3'];
        foreach ($productSkuList as $sku) {
            try {
                $productRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
                    ->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
                $product = $productRepository->get($sku, true);
                if ($product->getId()) {
                    $productRepository->delete($product);
                }
                // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                //Product already removed
            }
        }

        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', false);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/multiselect_attribute_with_incorrect_values.php
     * @magentoDataFixture Magento/Catalog/_files/product_text_attribute.php
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testProductWithWrappedAdditionalAttributes()
    {
        $filesystem = $this->objectManager->create(\Magento\Framework\Filesystem::class);
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = $this->objectManager->create(
            \Magento\ImportExport\Model\Import\Source\Csv::class,
            [
                'file' => __DIR__ . '/_files/products_to_import_with_additional_attributes.csv',
                'directory' => $directory
            ]
        );
        $errors = $this->_model->setParameters(
            [
                'behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND,
                'entity' => 'catalog_product',
                \Magento\ImportExport\Model\Import::FIELDS_ENCLOSURE => 1
            ]
        )->setSource(
            $source
        )->validateData();

        $this->assertTrue($errors->getErrorsCount() == 0);

        $this->_model->importData();

        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
        $productRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Api\ProductRepositoryInterface::class
        );

        /** @var \Magento\Eav\Api\AttributeOptionManagementInterface $multiselectOptions */
        $multiselectOptions = $this->objectManager->get(\Magento\Eav\Api\AttributeOptionManagementInterface::class)
            ->getItems(\Magento\Catalog\Model\Product::ENTITY, 'multiselect_attribute');

        $product1 = $productRepository->get('simple1');
        $this->assertEquals('\'", =|', $product1->getData('text_attribute'));
        $this->assertEquals(
            implode(',', [$multiselectOptions[3]->getValue(), $multiselectOptions[2]->getValue()]),
            $product1->getData('multiselect_attribute')
        );

        $product2 = $productRepository->get('simple2');
        $this->assertEquals('', $product2->getData('text_attribute'));
        $this->assertEquals(
            implode(',', [$multiselectOptions[1]->getValue(), $multiselectOptions[2]->getValue()]),
            $product2->getData('multiselect_attribute')
        );
    }

    /**
     * Import and check data from file.
     *
     * @param string $fileName
     * @param int $expectedErrors
     * @return void
     */
    private function importDataForMediaTest(string $fileName, int $expectedErrors = 0)
    {
        $filesystem = $this->objectManager->create(\Magento\Framework\Filesystem::class);
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);

        $source = $this->objectManager->create(
            \Magento\ImportExport\Model\Import\Source\Csv::class,
            [
                'file' => __DIR__ . '/_files/' . $fileName,
                'directory' => $directory
            ]
        );
        $this->_model->setParameters(
            [
                'behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND,
                'entity' => 'catalog_product',
                'import_images_file_dir' => 'pub/media/import'
            ]
        );
        $appParams = \Magento\TestFramework\Helper\Bootstrap::getInstance()
            ->getBootstrap()
            ->getApplication()
            ->getInitParams()[Bootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS];
        $uploader = $this->_model->getUploader();

        $mediaPath = $appParams[DirectoryList::MEDIA][DirectoryList::PATH];
        $destDir = $directory->getRelativePath($mediaPath . '/catalog/product');
        $tmpDir = $directory->getRelativePath($mediaPath . '/import');

        $directory->create($destDir);
        $this->assertTrue($uploader->setDestDir($destDir));
        $this->assertTrue($uploader->setTmpDir($tmpDir));
        $errors = $this->_model->setSource(
            $source
        )->validateData();
        $this->assertTrue($errors->getErrorsCount() == 0);

        $this->_model->importData();
        $this->assertTrue($this->_model->getErrorAggregator()->getErrorsCount() == $expectedErrors);
    }

    /**
     * Load product by given product sku
     *
     * @param string $sku
     * @return \Magento\Catalog\Model\Product
     */
    private function getProductBySku($sku)
    {
        $resource = $this->objectManager->get(\Magento\Catalog\Model\ResourceModel\Product::class);
        $productId = $resource->getIdBySku($sku);
        $product = $this->objectManager->create(\Magento\Catalog\Model\Product::class);
        $product->load($productId);

        return $product;
    }

    /**
     * @param array $row
     * @param string|null $behavior
     * @param bool $expectedResult
     * @magentoAppArea adminhtml
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Catalog/Model/ResourceModel/_files/product_simple.php
     * @dataProvider validateRowDataProvider
     */
    public function testValidateRow(array $row, $behavior, $expectedResult)
    {
        $this->_model->setParameters(['behavior' => $behavior, 'entity' => 'catalog_product']);
        $this->assertSame($expectedResult, $this->_model->validateRow($row, 1));
    }

    /**
     * @return array
     */
    public function validateRowDataProvider()
    {
        return [
            [
                'row' => ['sku' => 'simple products'],
                'behavior' => null,
                'expectedResult' => true,
            ],
            [
                'row' => ['sku' => 'simple products absent'],
                'behavior' => null,
                'expectedResult' => false,
            ],
            [
                'row' => [
                    'sku' => 'simple products absent',
                    'name' => 'Test',
                    'product_type' => 'simple',
                    '_attribute_set' => 'Default',
                    'price' => 10.20,
                ],
                'behavior' => null,
                'expectedResult' => true,
            ],
            [
                'row' => ['sku' => 'simple products'],
                'behavior' => Import::BEHAVIOR_ADD_UPDATE,
                'expectedResult' => true,
            ],
            [
                'row' => ['sku' => 'simple products absent'],
                'behavior' => Import::BEHAVIOR_ADD_UPDATE,
                'expectedResult' => false,
            ],
            [
                'row' => [
                    'sku' => 'simple products absent',
                    'name' => 'Test',
                    'product_type' => 'simple',
                    '_attribute_set' => 'Default',
                    'price' => 10.20,
                ],
                'behavior' => Import::BEHAVIOR_ADD_UPDATE,
                'expectedResult' => true,
            ],
            [
                'row' => ['sku' => 'simple products'],
                'behavior' => Import::BEHAVIOR_DELETE,
                'expectedResult' => true,
            ],
            [
                'row' => ['sku' => 'simple products absent'],
                'behavior' => Import::BEHAVIOR_DELETE,
                'expectedResult' => false,
            ],
            [
                'row' => ['sku' => 'simple products'],
                'behavior' => Import::BEHAVIOR_REPLACE,
                'expectedResult' => false,
            ],
            [
                'row' => ['sku' => 'simple products absent'],
                'behavior' => Import::BEHAVIOR_REPLACE,
                'expectedResult' => false,
            ],
            [
                'row' => [
                    'sku' => 'simple products absent',
                    'name' => 'Test',
                    'product_type' => 'simple',
                    '_attribute_set' => 'Default',
                    'price' => 10.20,
                ],
                'behavior' => Import::BEHAVIOR_REPLACE,
                'expectedResult' => false,
            ],
            [
                'row' => [
                    'sku' => 'simple products',
                    'name' => 'Test',
                    'product_type' => 'simple',
                    '_attribute_set' => 'Default',
                    'price' => 10.20,
                ],
                'behavior' => Import::BEHAVIOR_REPLACE,
                'expectedResult' => true,
            ],
        ];
    }

    /**
     * @covers \Magento\ImportExport\Model\Import\Entity\AbstractEntity::_saveValidatedBunches
     */
    public function testValidateData()
    {
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Framework\Filesystem::class);
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);

        $source = $this->objectManager->create(
            \Magento\ImportExport\Model\Import\Source\Csv::class,
            [
                'file' => __DIR__ . '/_files/products_to_import.csv',
                'directory' => $directory
            ]
        );

        $errors = $this->_model->setParameters(
            ['behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND, 'entity' => 'catalog_product']
        )->setSource($source)->validateData();

        $this->assertTrue($errors->getErrorsCount() == 0);
    }

    /**
     * Tests situation when images for importing products are already present in filesystem.
     *
     * @magentoDataFixture Magento/CatalogImportExport/Model/Import/_files/import_with_filesystem_images.php
     * @magentoAppIsolation enabled
     */
    public function testImportWithFilesystemImages()
    {
        /** @var Filesystem $filesystem */
        $filesystem = ObjectManager::getInstance()->get(Filesystem::class);
        /** @var \Magento\Framework\Filesystem\Directory\WriteInterface $writeAdapter */
        $writeAdapter = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);

        if (!$writeAdapter->isWritable()) {
            $this->markTestSkipped('Due to unwritable media directory');
        }

        $this->importDataForMediaTest('import_media_existing_images.csv');
    }

    /**
     * Test if we can change attribute set for product via import.
     *
     * @magentoDataFixture Magento/Catalog/_files/attribute_set_with_renamed_group.php
     * @magentoDataFixture Magento/Catalog/_files/product_without_options.php
     * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
     *
     */
    public function testImportDataChangeAttributeSet()
    {
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Framework\Filesystem::class);

        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);

        $source = $this->objectManager->create(
            \Magento\ImportExport\Model\Import\Source\Csv::class,
            [
                'file' => __DIR__ . '/_files/products_to_import_with_new_attribute_set.csv',
                'directory' => $directory
            ]
        );
        $errors = $this->_model->setParameters(
            [
                'behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE,
                'entity' => \Magento\Catalog\Model\Product::ENTITY
            ]
        )->setSource(
            $source
        )->validateData();

        $this->assertTrue($errors->getErrorsCount() == 0);

        $this->_model->importData();

        /** @var \Magento\Catalog\Model\Product[] $products */
        $products[] = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Catalog\Model\ProductRepository::class)->get('simple');
        $products[] = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Catalog\Model\ProductRepository::class)->get('simple2');

        /** @var \Magento\Catalog\Model\Config $catalogConfig */
        $catalogConfig = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Catalog\Model\Config::class);

        /** @var \Magento\Eav\Model\Config $eavConfig */
        $eavConfig = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Eav\Model\Config::class);

        $entityTypeId = (int)$eavConfig->getEntityType(\Magento\Catalog\Model\Product::ENTITY)
            ->getId();

        foreach ($products as $product) {
            $attributeSetName = $catalogConfig->getAttributeSetName($entityTypeId, $product->getAttributeSetId());
            $this->assertEquals('attribute_set_test', $attributeSetName);
        }
    }

    /**
     * Test importing products with changed SKU letter case.
     */
    public function testImportWithDifferentSkuCase()
    {
        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
        $productRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Api\ProductRepositoryInterface::class
        );
        /** @var \Magento\Framework\Api\SearchCriteria $searchCriteria */
        $searchCriteria = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Framework\Api\SearchCriteria::class);

        $importedPrices = [
            'simple1' => 25,
            'simple2' => 34,
            'simple3' => 58,
        ];
        $updatedPrices = [
            'simple1' => 111,
            'simple2' => 222,
            'simple3' => 333,
        ];

        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Framework\Filesystem::class);

        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);

        $source = $this->objectManager->create(
            \Magento\ImportExport\Model\Import\Source\Csv::class,
            [
                'file' => __DIR__ . '/_files/products_to_import.csv',
                'directory' => $directory
            ]
        );
        $errors = $this->_model->setParameters(
            [
                'behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE,
                'entity' => \Magento\Catalog\Model\Product::ENTITY
            ]
        )->setSource($source)->validateData();

        $this->assertTrue($errors->getErrorsCount() == 0);

        $this->_model->importData();

        $this->assertEquals(
            3,
            count($productRepository->getList($searchCriteria)->getItems())
        );
        foreach ($importedPrices as $sku => $expectedPrice) {
            $this->assertEquals($expectedPrice, $productRepository->get($sku)->getPrice());
        }

        $source = $this->objectManager->create(
            \Magento\ImportExport\Model\Import\Source\Csv::class,
            [
                'file' => __DIR__ . '/_files/products_to_import_with_changed_sku_case.csv',
                'directory' => $directory
            ]
        );
        $errors = $this->_model->setParameters(
            [
                'behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE,
                'entity' => \Magento\Catalog\Model\Product::ENTITY
            ]
        )->setSource($source)->validateData();

        $this->assertTrue($errors->getErrorsCount() == 0);

        $this->_model->importData();

        $this->assertEquals(
            3,
            count($productRepository->getList($searchCriteria)->getItems()),
            'Ensures that new products were not created'
        );
        foreach ($updatedPrices as $sku => $expectedPrice) {
            $this->assertEquals(
                $expectedPrice,
                $productRepository->get($sku, false, null, true)->getPrice(),
                'Check that all products were updated'
            );
        }
    }

    /**
     * Test that product import with images for non-default store works properly.
     *
     * @magentoDataFixture mediaImportImageFixture
     * @magentoAppIsolation enabled
     */
    public function testImportImageForNonDefaultStore()
    {
        $this->importDataForMediaTest('import_media_two_stores.csv');
        $product = $this->getProductBySku('simple_with_images');
        $mediaGallery = $product->getData('media_gallery');
        foreach ($mediaGallery['images'] as $image) {
            $image['file'] === '/m/a/magento_image.jpg'
                ? self::assertSame('1', $image['disabled'])
                : self::assertSame('0', $image['disabled']);
        }
    }

    /**
     * Test import product into multistore system when media is disabled.
     *
     * @magentoDataFixture Magento/CatalogImportExport/Model/Import/_files/custom_category_store_media_disabled.php
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @return void
     */
    public function testProductsWithMultipleStoresWhenMediaIsDisabled(): void
    {
        $filesystem = $this->objectManager->create(\Magento\Framework\Filesystem::class);
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = $this->objectManager->create(
            \Magento\ImportExport\Model\Import\Source\Csv::class,
            [
                'file' => __DIR__ . '/_files/product_with_custom_store_media_disabled.csv',
                'directory' => $directory,
            ]
        );
        $errors = $this->_model->setParameters(
            [
                'behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND,
                'entity' => 'catalog_product',
            ]
        )->setSource(
            $source
        )->validateData();

        $this->assertTrue($errors->getErrorsCount() === 0);
        $this->assertTrue($this->_model->importData());
    }

    /**
     * Test that imported product stock status with backorders functionality enabled can be set to 'out of stock'.
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     *
     * @return void
     */
    public function testImportWithBackordersEnabled(): void
    {
        $this->importFile('products_to_import_with_backorders_enabled_and_0_qty.csv');
        $product = $this->getProductBySku('simple_new');
        $this->assertFalse($product->getDataByKey('quantity_and_stock_status')['is_in_stock']);
    }

    /**
     * Test that imported product stock status with stock quantity > 0 and backorders functionality disabled
     * can be set to 'out of stock'.
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testImportWithBackordersDisabled(): void
    {
        $this->importFile('products_to_import_with_backorders_disabled_and_not_0_qty.csv');
        $product = $this->getProductBySku('simple_new');
        $this->assertFalse($product->getDataByKey('quantity_and_stock_status')['is_in_stock']);
    }

    /**
     * Import file by providing import filename in parameters.
     *
     * @param string $fileName
     * @return void
     */
    private function importFile(string $fileName): void
    {
        $filesystem = $this->objectManager->create(\Magento\Framework\Filesystem::class);
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = $this->objectManager->create(
            \Magento\ImportExport\Model\Import\Source\Csv::class,
            [
                'file' => __DIR__ . '/_files/' . $fileName,
                'directory' => $directory,
            ]
        );
        $errors = $this->_model->setParameters(
            [
                'behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND,
                'entity' => 'catalog_product',
                \Magento\ImportExport\Model\Import::FIELDS_ENCLOSURE => 1,
            ]
        )
        ->setSource($source)
        ->validateData();

        $this->assertTrue($errors->getErrorsCount() == 0);

        $this->_model->importData();
    }
}
