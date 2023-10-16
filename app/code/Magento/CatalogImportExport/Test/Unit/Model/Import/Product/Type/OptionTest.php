<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogImportExport\Test\Unit\Model\Import\Product\Type;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Helper\Data;
use Magento\Catalog\Model\ResourceModel\Product\Option\Value\Collection;
use Magento\CatalogImportExport\Model\Import\Product;
use Magento\CatalogImportExport\Model\Import\Product\Option;
use Magento\CatalogImportExport\Model\Import\Product\SkuStorage;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityMetadata;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\ResourceModel\Helper;
use Magento\ImportExport\Test\Unit\Model\Import\AbstractImportTestCase;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

/**
 * Test class for import product options module
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class OptionTest extends AbstractImportTestCase
{
    /**
     * Path to csv file to import
     */
    public const PATH_TO_CSV_FILE = '/_files/product_with_custom_options.csv';

    /**
     * Parameters for Test stores.
     *
     * @var array
     */
    protected $_testStores = ['admin' => 0, 'new_store_view' => 1];

    /**
     * An array with tables to inject into model.
     *
     * @var array
     */
    protected $_tables = [
        'catalog_product_entity' => 'catalog_product_entity',
        'catalog_product_option' => 'catalog_product_option',
        'catalog_product_option_title' => 'catalog_product_option_title',
        'catalog_product_option_type_title' => 'catalog_product_option_type_title',
        'catalog_product_option_type_value' => 'catalog_product_option_type_value',
        'catalog_product_option_type_price' => 'catalog_product_option_type_price',
        'catalog_product_option_price' => 'catalog_product_option_price'
    ];

    /**
     * @var Option
     */
    protected $model;

    /**
     * @var Option
     */
    protected $modelMock;

    /**
     * Parent product.
     *
     * @var Product
     */
    protected $productEntity;

    /**
     * Array of expected (after import) option titles.
     *
     * @var array
     */
    protected $_expectedTitles = [
        ['option_id' => 2, 'store_id' => 0, 'title' => 'Test Field Title'],
        ['option_id' => 3, 'store_id' => 0, 'title' => 'Test Date and Time Title'],
        ['option_id' => 4, 'store_id' => 0, 'title' => 'Test Select'],
        ['option_id' => 5, 'store_id' => 0, 'title' => 'Test Radio']
    ];

    /**
     * Array of expected (after import) option prices.
     *
     * @var array
     */
    protected $_expectedPrices = [
        0 => ['option_id' => 2, 'store_id' => 0, 'price_type' => 'fixed', 'price' => 0],
        1 => ['option_id' => 3, 'store_id' => 0, 'price_type' => 'fixed', 'price' => 2]
    ];

    /**
     * Array of expected (after import) option type prices.
     *
     * @var array
     */
    protected $_expectedTypePrices = [
        ['price' => 3, 'price_type' => 'fixed', 'option_type_id' => 2, 'store_id' => 0],
        ['price' => 3, 'price_type' => 'fixed', 'option_type_id' => 3, 'store_id' => 0],
        ['price' => 3, 'price_type' => 'fixed', 'option_type_id' => 4, 'store_id' => 0],
        ['price' => 3, 'price_type' => 'fixed', 'option_type_id' => 5, 'store_id' => 0]
    ];

    /**
     * Array of expected (after import) option type titles.
     *
     * @var array
     */
    protected $_expectedTypeTitles = [
        ['option_type_id' => 2, 'store_id' => 0, 'title' => 'Option 1'],
        ['option_type_id' => 3, 'store_id' => 0, 'title' => 'Option 2'],
        ['option_type_id' => 4, 'store_id' => 0, 'title' => 'Option 1'],
        ['option_type_id' => 5, 'store_id' => 0, 'title' => 'Option 2']
    ];

    /**
     * Array of expected updates to catalog_product_entity table after custom options import.
     *
     * @var array
     */
    protected $_expectedUpdate = [1 => ['entity_id' => 1, 'has_options' => 1, 'required_options' => 1]];

    /**
     * Array of expected (after import) options.
     *
     * @var array
     */
    protected $_expectedOptions = [
        [
            'option_id' => 2,
            'sku' => '1-text',
            'max_characters' => '100',
            'file_extension' => null,
            'image_size_x' => 0,
            'image_size_y' => 0,
            'product_id' => 1,
            'type' => 'field',
            'is_require' => 1,
            'sort_order' => 1
        ],
        [
            'option_id' => 3,
            'sku' => '2-date',
            'max_characters' => 0,
            'file_extension' => null,
            'image_size_x' => 0,
            'image_size_y' => 0,
            'product_id' => 1,
            'type' => 'date_time',
            'is_require' => 1,
            'sort_order' => 2
        ],
        [
            'option_id' => 4,
            'sku' => '',
            'max_characters' => 0,
            'file_extension' => null,
            'image_size_x' => 0,
            'image_size_y' => 0,
            'product_id' => 1,
            'type' => 'drop_down',
            'is_require' => 1,
            'sort_order' => 3
        ],
        [
            'option_id' => 5,
            'sku' => '',
            'max_characters' => 0,
            'file_extension' => null,
            'image_size_x' => 0,
            'image_size_y' => 0,
            'product_id' => 1,
            'type' => 'radio',
            'is_require' => 1,
            'sort_order' => 4
        ]
    ];

    /**
     * Array of expected (after import) option type values.
     *
     * @var array
     */
    protected $_expectedTypeValues = [
        ['option_type_id' => 2, 'sort_order' => 0, 'sku' => '3-1-select', 'option_id' => 4],
        ['option_type_id' => 3, 'sort_order' => 1, 'sku' => '3-2-select', 'option_id' => 4],
        ['option_type_id' => 4, 'sort_order' => 0, 'sku' => '4-1-radio', 'option_id' => 5],
        ['option_type_id' => 5, 'sort_order' => 1, 'sku' => '4-2-radio', 'option_id' => 5]
    ];

    /**
     * "WHERE" which should be generate in case of deleting custom options.
     *
     * @var string
     */
    protected $_whereForOption = 'product_id IN (1)';

    /**
     * "WHERE" which should be generate in case of deleting custom option types.
     *
     * @var string
     */
    protected $_whereForType = 'option_id IN (4, 5)';

    /**
     * A Page Size for product option collection iterator.
     *
     * @var int
     */
    protected $_iteratorPageSize = 100;

    /**
     * @var ProcessingErrorAggregatorInterface
     */
    protected $errorAggregator;

    /**
     * @var MetadataPool
     */
    protected $metadataPoolMock;

    /**
     * @var SkuStorage
     */
    private $skuStorageMock;

    /**
     * Init entity adapter model
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        parent::setUp();

        $addExpectations = false;
        $deleteBehavior = false;
        $testName = $this->getName(true);
        if ($testName == 'testImportDataAppendBehavior' || $testName == 'testImportDataDeleteBehavior') {
            $addExpectations = true;
            $deleteBehavior = $this->getName() == 'testImportDataDeleteBehavior' ? true : false;
        }

        $doubleOptions = false;
        if ($testName == 'testValidateAmbiguousData with data set "ambiguity_several_db_rows"') {
            $doubleOptions = true;
        }

        $catalogDataMock = $this->createPartialMock(Data::class, ['__construct']);

        $scopeConfig = $this->getMockForAbstractClass(ScopeConfigInterface::class);

        $timezoneInterface = $this->getMockForAbstractClass(TimezoneInterface::class);
        $date = new \DateTime();
        $timezoneInterface->expects($this->any())->method('date')->willReturn($date);
        $this->metadataPoolMock = $this->createMock(MetadataPool::class);
        $entityMetadataMock = $this->createMock(EntityMetadata::class);
        $this->metadataPoolMock->expects($this->any())
            ->method('getMetadata')
            ->with(ProductInterface::class)
            ->willReturn($entityMetadataMock);
        $entityMetadataMock->expects($this->any())
            ->method('getLinkField')
            ->willReturn('entity_id');
        $optionValueCollectionFactoryMock = $this->createMock(
            \Magento\Catalog\Model\ResourceModel\Product\Option\Value\CollectionFactory::class
        );
        $optionValueCollectionMock = $this->createPartialMock(
            Collection::class,
            ['getIterator', 'addTitleToResult']
        );
        $optionValueCollectionMock->expects($this->any())->method('getIterator')
            ->willReturn($this->createMock(\Traversable::class));
        $optionValueCollectionFactoryMock->expects($this->any())
            ->method('create')->willReturn($optionValueCollectionMock);

        $this->skuStorageMock = $this->createMock(SkuStorage::class);

        $modelClassArgs = [
            $this->createMock(\Magento\ImportExport\Model\ResourceModel\Import\Data::class),
            $this->createMock(ResourceConnection::class),
            $this->createMock(Helper::class),
            $this->getMockForAbstractClass(StoreManagerInterface::class),
            $this->createMock(\Magento\Catalog\Model\ProductFactory::class),
            $this->createMock(\Magento\Catalog\Model\ResourceModel\Product\Option\CollectionFactory::class),
            $this->createMock(\Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory::class),
            $catalogDataMock,
            $scopeConfig,
            $timezoneInterface,
            $this->createMock(
                ProcessingErrorAggregatorInterface::class
            ),
            $this->_getModelDependencies($addExpectations, $deleteBehavior, $doubleOptions),
            $optionValueCollectionFactoryMock,
            $this->createMock(\Magento\Framework\Model\ResourceModel\Db\TransactionManagerInterface::class),
            $this->skuStorageMock
        ];

        $modelClassName = Option::class;
        $this->model = new $modelClassName(...array_values($modelClassArgs));
        // Create model mock with rewritten _getMultiRowFormat method to support test data with the old format.
        $this->modelMock = $this->getMockBuilder($modelClassName)
            ->setConstructorArgs($modelClassArgs)
            ->setMethods(['_getMultiRowFormat'])
            ->getMock();
        $reflection = new \ReflectionClass(Option::class);
        $reflectionProperty = $reflection->getProperty('metadataPool');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->modelMock, $this->metadataPoolMock);
    }

    /**
     * Unset entity adapter model.
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        unset($this->model);
        unset($this->productEntity);
    }

    /**
     * Create mocks for all $this->model dependencies.
     *
     * @param bool $addExpectations
     * @param bool $deleteBehavior
     * @param bool $doubleOptions
     *
     * @return array
     */
    protected function _getModelDependencies(
        bool $addExpectations = false,
        bool $deleteBehavior = false,
        bool $doubleOptions = false
    ): array {
        $connection = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        if ($addExpectations) {
            if ($deleteBehavior) {
                $connection->expects(
                    $this->exactly(1)
                )->method(
                    'quoteInto'
                )->willReturnCallback(
                    [$this, 'stubQuoteInto']
                );
                $connection->expects(
                    $this->exactly(1)
                )->method(
                    'delete'
                )->willReturnCallback(
                    [$this, 'verifyDelete']
                );
            } else {
                $connection->expects(
                    $this->exactly(7)
                )->method(
                    'insertOnDuplicate'
                )->willReturnCallback(
                    [$this, 'verifyInsertOnDuplicate']
                );
            }
        }

        $resourceHelper = $this->getMockBuilder(\stdClass::class)->addMethods(['getNextAutoincrement'])
            ->disableOriginalConstructor()
            ->getMock();
        if ($addExpectations) {
            $resourceHelper->expects($this->any())->method('getNextAutoincrement')->willReturn(2);
        }

        $data = [
            'connection' => $connection,
            'tables' => $this->_tables,
            'resource_helper' => $resourceHelper,
            'is_price_global' => true,
            'stores' => $this->_testStores,
            'metadata_pool' => $this->metadataPoolMock
        ];
        $sourceData = $this->_getSourceDataMocks($addExpectations, $doubleOptions);

        return array_merge($data, $sourceData);
    }

    /**
     * Get source data mocks.
     *
     * @param bool $addExpectations
     * @param bool $doubleOptions
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _getSourceDataMocks(bool $addExpectations, bool $doubleOptions): array
    {
        $csvData = $this->_loadCsvFile();

        $dataSourceModel = $this->getMockBuilder(\stdClass::class)->addMethods(['getNextUniqueBunch'])
            ->disableOriginalConstructor()
            ->getMock();
        if ($addExpectations) {
            $dataSourceModel
                ->method('getNextUniqueBunch')
                ->willReturnOnConsecutiveCalls($csvData['data'], null);
        }

        $products = [];
        $elementIndex = 0;
        foreach ($csvData['data'] as $rowIndex => $csvDataRow) {
            if (!empty($csvDataRow['sku']) && !array_key_exists($csvDataRow['sku'], $products)) {
                $elementIndex = $rowIndex + 1;
                $products[$csvDataRow['sku']] = [
                    'sku' => $csvDataRow['sku'],
                    'id' => $elementIndex,
                    'entity_id' => $elementIndex,
                    'product_id' => $elementIndex,
                    'type' => $csvDataRow[Product::COL_TYPE],
                    'title' => $csvDataRow[Product::COL_NAME]
                ];
            }
        }

        $this->productEntity = $this->createPartialMock(
            Product::class,
            ['getErrorAggregator']
        );
        $this->productEntity->method('getErrorAggregator')->willReturn($this->getErrorAggregatorObject());
        $reflection = new \ReflectionClass(Product::class);
        $reflectionProperty = $reflection->getProperty('metadataPool');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->productEntity, $this->metadataPoolMock);

        $productModelMock = $this->getMockBuilder(\stdClass::class)->addMethods(['getProductEntitiesInfo'])
            ->disableOriginalConstructor()
            ->getMock();
        $productModelMock->expects(
            $this->any()
        )->method(
            'getProductEntitiesInfo'
        )->willReturn(
            $products
        );

        $this->skuStorageMock->method('get')->willReturnCallback(function ($sku) use ($products) {
            $skuLowered = strtolower($sku);

            return $products[$skuLowered] ?? null;
        });

        $this->skuStorageMock->method('has')->willReturnCallback(function ($sku) use ($products) {
            $skuLowered = strtolower($sku);

            return isset($products[$skuLowered]);
        });

        $fetchStrategy = $this->getMockForAbstractClass(
            FetchStrategyInterface::class
        );
        $logger = $this->getMockForAbstractClass(LoggerInterface::class);
        $entityFactory = $this->createMock(EntityFactory::class);

        $optionCollection = $this->getMockBuilder(AbstractDb::class)->setConstructorArgs(
            [
                $entityFactory,
                $logger,
                $fetchStrategy
            ]
        )
            ->onlyMethods(['getSelect', 'getNewEmptyItem'])
            ->addMethods(['reset', 'addProductToFilter'])
            ->getMockForAbstractClass();

        $select = $this->createPartialMock(Select::class, ['join', 'where']);
        $select->expects($this->any())->method('join')->willReturnSelf();
        $select->expects($this->any())->method('where')->willReturnSelf();

        $optionCollection->expects(
            $this->any()
        )->method(
            'getNewEmptyItem'
        )->willReturnCallback(
            [$this, 'getNewOptionMock']
        );
        $optionCollection->expects($this->any())->method('reset')->willReturnSelf();
        $optionCollection->expects($this->any())->method('addProductToFilter')->willReturnSelf();
        $optionCollection->expects($this->any())->method('getSelect')->willReturn($select);

        $optionsData = array_values($products);
        if ($doubleOptions) {
            foreach ($products as $product) {
                $elementIndex++;
                $product['id'] = $elementIndex;
                $optionsData[] = $product;
            }
        }

        $fetchStrategy->expects($this->any())->method('fetchAll')->willReturn($optionsData);

        $collectionIterator = $this->getMockBuilder(\stdClass::class)->addMethods(['iterate'])
            ->disableOriginalConstructor()
            ->getMock();
        $collectionIterator->expects(
            $this->any()
        )->method(
            'iterate'
        )->willReturnCallback(
            [$this, 'iterate']
        );

        $data = [
            'data_source_model' => $dataSourceModel,
            'product_model' => $productModelMock,
            'product_entity' => $this->productEntity,
            'option_collection' => $optionCollection,
            'collection_by_pages_iterator' => $collectionIterator,
            'page_size' => $this->_iteratorPageSize
        ];
        return $data;
    }

    /**
     * Iterate stub.
     *
     * @param AbstractDb $collection
     * @param int $pageSize
     * @param array $callbacks
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function iterate(AbstractDb $collection, int $pageSize, array $callbacks): void
    {
        foreach ($collection as $option) {
            foreach ($callbacks as $callback) {
                call_user_func($callback, $option);
            }
        }
    }

    /**
     * Get new object mock for \Magento\Catalog\Model\Product\Option
     *
     * @return \Magento\Catalog\Model\Product\Option|MockObject
     */
    public function getNewOptionMock(): MockObject
    {
        return $this->createPartialMock(\Magento\Catalog\Model\Product\Option::class, ['__wakeup']);
    }

    /**
     * Stub method to emulate adapter quoteInfo() method and get data in needed for test format.
     *
     * @param string $text
     * @param array|int|float|string $value
     *
     * @return string
     */
    public function stubQuoteInto($text, $value): string
    {
        if (is_array($value)) {
            $value = implode(', ', $value);
        }
        return str_replace('?', $value, $text);
    }

    /**
     * Verify data, sent to $this->_connection->delete() method.
     *
     * @param string $table
     * @param string $where
     *
     * @return void
     */
    public function verifyDelete(string $table, string $where): void
    {
        if ($table == 'catalog_product_option') {
            $this->assertEquals($this->_tables['catalog_product_option'], $table);
            $this->assertEquals($this->_whereForOption, $where);
        } else {
            $this->assertEquals($this->_tables['catalog_product_option_type_value'], $table);
            $this->assertEquals($this->_whereForType, $where);
        }
    }

    /**
     * Verify data, sent to $this->_connection->insertMultiple() method.
     *
     * @param string $table
     * @param array $data
     *
     * @return void
     */
    public function verifyInsertMultiple(string $table, array $data): void
    {
        switch ($table) {
            case $this->_tables['catalog_product_option']:
                $this->assertEquals($this->_expectedOptions, $data);
                break;
            case $this->_tables['catalog_product_option_type_value']:
                $this->assertEquals($this->_expectedTypeValues, $data);
                break;
            default:
                break;
        }
    }

    /**
     * Verify data, sent to $this->_connection->insertOnDuplicate() method.
     *
     * @param string $table
     * @param array $data
     * @param array $fields
     *
     * @return void
     */
    public function verifyInsertOnDuplicate(string $table, array $data, array $fields = []): void
    {
        switch ($table) {
            case $this->_tables['catalog_product_option']:
                $this->assertEquals($this->_expectedOptions, $data);
                break;
            case $this->_tables['catalog_product_option_type_value']:
                $this->assertEquals($this->_expectedTypeValues, $data);
                break;
            case $this->_tables['catalog_product_option_title']:
                $this->assertEquals($this->_expectedTitles, $data);
                $this->assertEquals(['title'], $fields);
                break;
            case $this->_tables['catalog_product_option_price']:
                $this->assertEquals($this->_expectedPrices, $data);
                $this->assertEquals(['price', 'price_type'], $fields);
                break;
            case $this->_tables['catalog_product_option_type_price']:
                $this->assertEquals($this->_expectedTypePrices, $data);
                $this->assertEquals(['price', 'price_type'], $fields);
                break;
            case $this->_tables['catalog_product_option_type_title']:
                $this->assertEquals($this->_expectedTypeTitles, $data);
                $this->assertEquals(['title'], $fields);
                break;
            case $this->_tables['catalog_product_entity']:
                // there is no point in updated_at data verification which is just current time
                foreach ($data as &$row) {
                    $this->assertArrayHasKey('updated_at', $row);
                    unset($row['updated_at']);
                }
                $this->assertEquals($this->_expectedUpdate, $data);
                $this->assertEquals(['has_options', 'required_options', 'updated_at'], $fields);
                break;
            default:
                break;
        }
    }

    /**
     * @return void
     *
     * @covers \Magento\CatalogImportExport\Model\Import\Product\Option::getEntityTypeCode
     */
    public function testGetEntityTypeCode(): void
    {
        $this->assertEquals('product_options', $this->model->getEntityTypeCode());
    }

    /**
     * @return void
     *
     * @covers \Magento\CatalogImportExport\Model\Import\Product\Option::importData
     * @covers \Magento\CatalogImportExport\Model\Import\Product\Option::_importData
     * @covers \Magento\CatalogImportExport\Model\Import\Product\Option::_saveOptions
     * @covers \Magento\CatalogImportExport\Model\Import\Product\Option::_saveTitles
     * @covers \Magento\CatalogImportExport\Model\Import\Product\Option::_savePrices
     * @covers \Magento\CatalogImportExport\Model\Import\Product\Option::_saveSpecificTypeValues
     * @covers \Magento\CatalogImportExport\Model\Import\Product\Option::_saveSpecificTypePrices
     * @covers \Magento\CatalogImportExport\Model\Import\Product\Option::_saveSpecificTypeTitles
     * @covers \Magento\CatalogImportExport\Model\Import\Product\Option::_updateProducts
     */
    public function testImportDataAppendBehavior(): void
    {
        $this->model->importData();
    }

    /**
     * @return void
     *
     * @covers \Magento\CatalogImportExport\Model\Import\Product\Option::_importData
     * @covers \Magento\CatalogImportExport\Model\Import\Product\Option::_deleteEntities
     */
    public function testImportDataDeleteBehavior(): void
    {
        $this->model->setParameters(['behavior' => Import::BEHAVIOR_DELETE]);
        $this->model->importData();
    }

    /**
     * Load and return CSV source data.
     *
     * @return array
     */
    protected function _loadCsvFile(): array
    {
        $data = $this->_csvToArray(file_get_contents(__DIR__ . self::PATH_TO_CSV_FILE));

        return $data;
    }

    /**
     * Export CSV string to array.
     *
     * @param string $content
     * @param mixed $entityId
     *
     * @return array
     */
    protected function _csvToArray($content, $entityId = null): array
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
     * Set method _getMultiRowFormat for model mock
     * Make model bypass format converting, used to pass tests' with old data.
     * @todo should be refactored/removed when all old options are converted into the new format.
     *
     * @param array $rowData old format data
     *
     * @return void
     */
    private function _bypassModelMethodGetMultiRowFormat(array $rowData): void
    {
        $this->modelMock->expects($this->any())
            ->method('_getMultiRowFormat')
            ->willReturn([$rowData]);
    }

    /**
     * Test for validation of row without custom option.
     *
     * @return void
     * @covers \Magento\CatalogImportExport\Model\Import\Product\Option::_isRowWithCustomOption
     */
    public function testValidateRowNoCustomOption(): void
    {
        $rowData = include __DIR__ . '/_files/row_data_no_custom_option.php';
        $this->_bypassModelMethodGetMultiRowFormat($rowData);
        $this->assertTrue($this->modelMock->validateRow($rowData, 0));
    }

    /**
     * Test for simple cases of row validation (without existing related data).
     *
     * @param array $rowData
     * @param array $errors
     *
     * @return void
     * @covers \Magento\CatalogImportExport\Model\Import\Product\Option::validateRow
     * @covers \Magento\CatalogImportExport\Model\Import\Product\Option::_isRowWithCustomOption
     * @covers \Magento\CatalogImportExport\Model\Import\Product\Option::_isMainOptionRow
     * @covers \Magento\CatalogImportExport\Model\Import\Product\Option::_isSecondaryOptionRow
     * @covers \Magento\CatalogImportExport\Model\Import\Product\Option::_validateMainRow
     * @covers \Magento\CatalogImportExport\Model\Import\Product\Option::_validateMainRowAdditionalData
     * @covers \Magento\CatalogImportExport\Model\Import\Product\Option::_validateSecondaryRow
     * @covers \Magento\CatalogImportExport\Model\Import\Product\Option::_validateSpecificTypeParameters
     * @covers \Magento\CatalogImportExport\Model\Import\Product\Option::_validateSpecificParameterData
     * @dataProvider validateRowDataProvider
     */
    public function testValidateRow(array $rowData, array $errors): void
    {
        $this->_bypassModelMethodGetMultiRowFormat($rowData);
        if (empty($errors)) {
            $this->assertTrue($this->modelMock->validateRow($rowData, 0));
        } else {
            $this->assertFalse($this->modelMock->validateRow($rowData, 0));
        }
        $resultErrors = $this->productEntity->getErrorAggregator()->getRowsGroupedByErrorCode([], [], false);
        $this->assertEquals($errors, $resultErrors);
    }

    /**
     * Test for validation of ambiguous data.
     *
     * @param array $rowData
     * @param array $errors
     * @param string|null $behavior
     * @param int $numberOfValidations
     *
     * @return void
     * @covers \Magento\CatalogImportExport\Model\Import\Product\Option::validateAmbiguousData
     * @covers \Magento\CatalogImportExport\Model\Import\Product\Option::_findNewOptionsWithTheSameTitles
     * @covers \Magento\CatalogImportExport\Model\Import\Product\Option::_findOldOptionsWithTheSameTitles
     * @covers \Magento\CatalogImportExport\Model\Import\Product\Option::_findNewOldOptionsTypeMismatch
     * @covers \Magento\CatalogImportExport\Model\Import\Product\Option::_saveNewOptionData
     * @dataProvider validateAmbiguousDataDataProvider
     */
    public function testValidateAmbiguousData(
        array $rowData,
        array $errors,
        $behavior = null,
        $numberOfValidations = 1
    ): void {
        $this->_testStores = ['admin' => 0];
        $this->setUp();
        if ($behavior) {
            $this->modelMock->setParameters(['behavior' => Import::BEHAVIOR_APPEND]);
        }

        $this->_bypassModelMethodGetMultiRowFormat($rowData);

        for ($i = 0; $i < $numberOfValidations; $i++) {
            $this->modelMock->validateRow($rowData, $i);
        }

        if (empty($errors)) {
            $this->assertTrue($this->modelMock->validateAmbiguousData());
        } else {
            $this->assertFalse($this->modelMock->validateAmbiguousData());
        }
        $resultErrors = $this->productEntity->getErrorAggregator()->getRowsGroupedByErrorCode([], [], false);
        $this->assertEquals($errors, $resultErrors);
    }

    /**
     * Test for row without store view code field.
     *
     * @param array $rowData
     * @param array $responseData
     *
     * @return void
     * @covers \Magento\CatalogImportExport\Model\Import\Product\Option::_parseCustomOptions
     * @dataProvider validateRowStoreViewCodeFieldDataProvider
     */
    public function testValidateRowDataForStoreViewCodeField(array $rowData, array $responseData): void
    {
        $reflection = new \ReflectionClass(Option::class);
        $reflectionMethod = $reflection->getMethod('_parseCustomOptions');
        $reflectionMethod->setAccessible(true);
        $result = $reflectionMethod->invoke($this->model, $rowData);
        $this->assertEquals($responseData, $result);
    }

    /**
     * Data provider for test of method _parseCustomOptions.
     *
     * @return array
     */
    public function validateRowStoreViewCodeFieldDataProvider(): array
    {
        return [
            'with_store_view_code' => [
                '$rowData' => [
                    'store_view_code' => '',
                    'custom_options' => 'name=Test Field Title,type=field,required=1'
                        . ';sku=1-text,price=0,price_type=fixed'
                ],
                '$responseData' => [
                    'store_view_code' => '',
                    'custom_options' => [
                        'Test Field Title' => [
                            [
                                'name' => 'Test Field Title',
                                'type' => 'field',
                                'required' => '1',
                                'sku' => '1-text',
                                'price' => '0',
                                'price_type' => 'fixed',
                                '_custom_option_store' => ''
                            ]
                        ]
                    ]
                ]
            ],
            'without_store_view_code' => [
                '$rowData' => [
                    'custom_options' => 'name=Test Field Title,type=field,required=1'
                        . ';sku=1-text,price=0,price_type=fixed'
                ],
                '$responseData' => [
                    'custom_options' => [
                        'Test Field Title' => [
                            [
                                'name' => 'Test Field Title',
                                'type' => 'field',
                                'required' => '1',
                                'sku' => '1-text',
                                'price' => '0',
                                'price_type' => 'fixed'
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Data provider of row data and errors.
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function validateRowDataProvider(): array
    {
        return [
            'main_valid' => [
                '$rowData' => include __DIR__ . '/_files/row_data_main_valid.php',
                '$errors' => []
            ],
            'main_invalid_store' => [
                '$rowData' => include __DIR__ . '/_files/row_data_main_invalid_store.php',
                '$errors' => [
                    Option::ERROR_INVALID_STORE => [1]
                ]
            ],
            'main_incorrect_type' => [
                '$rowData' => include __DIR__ . '/_files/row_data_main_incorrect_type.php',
                '$errors' => [
                    Option::ERROR_INVALID_TYPE => [1]
                ]
            ],
            'main_no_title' => [
                '$rowData' => include __DIR__ . '/_files/row_data_main_no_title.php',
                '$errors' => [
                    Option::ERROR_EMPTY_TITLE => [1]
                ]
            ],
            'main_empty_title' => [
                '$rowData' => include __DIR__ . '/_files/row_data_main_empty_title.php',
                '$errors' => [
                    Option::ERROR_EMPTY_TITLE => [1]
                ]
            ],
            'main_invalid_price' => [
                '$rowData' => include __DIR__ . '/_files/row_data_main_invalid_price.php',
                '$errors' => [
                    Option::ERROR_INVALID_PRICE => [1]
                ]
            ],
            'main_invalid_max_characters' => [
                '$rowData' => include __DIR__ . '/_files/row_data_main_invalid_max_characters.php',
                '$errors' => [
                    Option::ERROR_INVALID_MAX_CHARACTERS => [1]
                ]
            ],
            'main_max_characters_less_zero' => [
                '$rowData' => include __DIR__ . '/_files/row_data_main_max_characters_less_zero.php',
                '$errors' => [
                    Option::ERROR_INVALID_MAX_CHARACTERS => [1]
                ]
            ],
            'main_invalid_sort_order' => [
                '$rowData' => include __DIR__ . '/_files/row_data_main_invalid_sort_order.php',
                '$errors' => [
                    Option::ERROR_INVALID_SORT_ORDER => [1]
                ]
            ],
            'main_sort_order_less_zero' => [
                '$rowData' => include __DIR__ . '/_files/row_data_main_sort_order_less_zero.php',
                '$errors' => [
                    Option::ERROR_INVALID_SORT_ORDER => [1]
                ]
            ],
            'secondary_valid' => [
                '$rowData' => include __DIR__ . '/_files/row_data_secondary_valid.php',
                '$errors' => []
            ],
            'secondary_invalid_store' => [
                '$rowData' => include __DIR__ . '/_files/row_data_secondary_invalid_store.php',
                '$errors' => [
                    Option::ERROR_INVALID_STORE => [1]
                ]
            ],
            'secondary_incorrect_price' => [
                '$rowData' => include __DIR__ . '/_files/row_data_secondary_incorrect_price.php',
                '$errors' => [
                    Option::ERROR_INVALID_ROW_PRICE => [1]
                ]
            ],
            'secondary_incorrect_row_sort' => [
                '$rowData' => include __DIR__ . '/_files/row_data_secondary_incorrect_row_sort.php',
                '$errors' => [
                    Option::ERROR_INVALID_ROW_SORT => [1]
                ]
            ],
            'secondary_row_sort_less_zero' => [
                '$rowData' => include __DIR__ . '/_files/row_data_secondary_row_sort_less_zero.php',
                '$errors' => [
                    Option::ERROR_INVALID_ROW_SORT => [1]
                ]
            ]
        ];
    }

    /**
     * Data provider for test of method validateAmbiguousData.
     *
     * @return array
     */
    public function validateAmbiguousDataDataProvider(): array
    {
        return [
            'ambiguity_several_input_rows' => [
                '$rowData' => include __DIR__ . '/_files/row_data_main_valid.php',
                '$errors' => [
                    Option::ERROR_AMBIGUOUS_NEW_NAMES => [2, 2]
                ],
                '$behavior' => null,
                '$numberOfValidations' => 2
            ],
            'ambiguity_different_type' => [
                '$rowData' => include __DIR__ . '/_files/row_data_ambiguity_different_type.php',
                '$errors' => [
                    Option::ERROR_AMBIGUOUS_TYPES => [1]
                ],
                '$behavior' => Import::BEHAVIOR_APPEND
            ],
            'ambiguity_several_db_rows' => [
                '$rowData' => include __DIR__ . '/_files/row_data_ambiguity_several_db_rows.php',
                '$errors' => [
                    Option::ERROR_AMBIGUOUS_OLD_NAMES => [1]
                ],
                '$behavior' => Import::BEHAVIOR_APPEND
            ]
        ];
    }

    /**
     * @return void
     */
    public function testParseRequiredData(): void
    {
        $modelData = $this->getMockBuilder(\stdClass::class)->addMethods(['getNextUniqueBunch'])
            ->disableOriginalConstructor()
            ->getMock();
        $modelData
            ->method('getNextUniqueBunch')
            ->willReturnOnConsecutiveCalls(
                [
                    [
                        'sku' => 'simple3',
                        '_custom_option_type' => 'field',
                        '_custom_option_title' => 'Title'
                    ]
                ],
                null
            );

        $productModel = $this->getMockBuilder(\stdClass::class)->addMethods(['getProductEntitiesInfo'])
            ->disableOriginalConstructor()
            ->getMock();
        $productModel->expects($this->any())->method('getProductEntitiesInfo')->willReturn([]);

        /** @var Product $productEntityMock */
        $productEntityMock = $this->createMock(Product::class);
        $reflection = new \ReflectionClass(Product::class);
        $reflectionProperty = $reflection->getProperty('metadataPool');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($productEntityMock, $this->metadataPoolMock);

        /** @var Option $model */
        $model = $this->objectManagerHelper->getObject(
            Option::class,
            [
                'data' => [
                    'data_source_model' => $modelData,
                    'product_model' => $productModel,
                    'option_collection' => $this->objectManagerHelper->getObject(\stdClass::class),
                    'product_entity' => $productEntityMock,
                    'collection_by_pages_iterator' => $this->objectManagerHelper->getObject(\stdClass::class),
                    'page_size' => 5000,
                    'stores' => [],
                    'metadata_pool' => $this->metadataPoolMock
                ]
            ]
        );
        $reflection = new \ReflectionClass(Option::class);
        $reflectionProperty = $reflection->getProperty('metadataPool');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($model, $this->metadataPoolMock);

        $this->assertTrue($model->importData());
    }

    /**
     * @return void
     */
    public function testClearProductsSkuToId(): void
    {
        $this->setPropertyValue($this->modelMock, '_productsSkuToId', 'value');

        $this->modelMock->clearProductsSkuToId();

        $productsSkuToId = $this->getPropertyValue($this->modelMock, '_productsSkuToId');

        $this->assertNull($productsSkuToId);
    }

    /**
     * Set object property.
     *
     * @param object $object
     * @param string $property
     * @param mixed $value
     *
     * @return object
     */
    protected function setPropertyValue(&$object, $property, $value)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $reflectionProperty = $reflection->getProperty($property);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, $value);

        return $object;
    }

    /**
     * Get object property.
     *
     * @param object $object
     * @param string $property
     * @return mixed
     */
    protected function getPropertyValue(&$object, $property)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $reflectionProperty = $reflection->getProperty($property);
        $reflectionProperty->setAccessible(true);

        return $reflectionProperty->getValue($object);
    }
}
