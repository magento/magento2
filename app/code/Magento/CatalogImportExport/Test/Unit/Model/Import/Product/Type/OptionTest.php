<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Test\Unit\Model\Import\Product\Type;

/**
 * Test class for import product options module
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class OptionTest extends \Magento\ImportExport\Test\Unit\Model\Import\AbstractImportTestCase
{
    /**
     * Path to csv file to import
     */
    const PATH_TO_CSV_FILE = '/_files/product_with_custom_options.csv';

    /**
     * Test store parametes
     *
     * @var array
     */
    protected $_testStores = ['admin' => 0, 'new_store_view' => 1];

    /**
     * Tables array to inject into model
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
     * Test entity
     *
     * @var \Magento\CatalogImportExport\Model\Import\Product\Option
     */
    protected $model;

    /**
     * Test model mock
     *
     * @var \Magento\CatalogImportExport\Model\Import\Product\Option
     */
    protected $modelMock;

    /**
     * Parent product entity
     *
     * @var \Magento\CatalogImportExport\Model\Import\Product
     */
    protected $productEntity;

    /**
     * Array of expected (after import) option titles
     *
     * @var array
     */
    protected $_expectedTitles = [
        ['option_id' => 2, 'store_id' => 0, 'title' => 'Test Field Title'],
        ['option_id' => 3, 'store_id' => 0, 'title' => 'Test Date and Time Title'],
        ['option_id' => 4, 'store_id' => 0, 'title' => 'Test Select'],
        ['option_id' => 5, 'store_id' => 0, 'title' => 'Test Radio'],
    ];

    /**
     * Array of expected (after import) option prices
     *
     * @var array
     */
    protected $_expectedPrices = [
        2 => ['option_id' => 2, 'store_id' => 0, 'price_type' => 'fixed', 'price' => 0],
        3 => ['option_id' => 3, 'store_id' => 0, 'price_type' => 'fixed', 'price' => 2]
    ];

    /**
     * Array of expected (after import) option type prices
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
     * Array of expected (after import) option type titles
     *
     * @var array
     */
    protected $_expectedTypeTitles = [
        ['option_type_id' => 2, 'store_id' => 0, 'title' => 'Option 1'],
        ['option_type_id' => 3, 'store_id' => 0, 'title' => 'Option 2'],
        ['option_type_id' => 4, 'store_id' => 0, 'title' => 'Option 1'],
        ['option_type_id' => 5, 'store_id' => 0, 'title' => 'Option 2'],
    ];

    /**
     * Expected updates to catalog_product_entity table after custom options import
     *
     * @var array
     */
    protected $_expectedUpdate = [1 => ['entity_id' => 1, 'has_options' => 1, 'required_options' => 1]];

    /**
     * Array of expected (after import) options
     *
     * @var array
     */
    protected $_expectedOptions = [
        [
            'option_id' => 1,
            'sku' => '1-text',
            'max_characters' => '100',
            'file_extension' => null,
            'image_size_x' => 0,
            'image_size_y' => 0,
            'product_id' => 1,
            'type' => 'field',
            'is_require' => 1,
            'sort_order' => 0
        ],
        [
            'option_id' => 2,
            'sku' => '2-date',
            'max_characters' => 0,
            'file_extension' => null,
            'image_size_x' => 0,
            'image_size_y' => 0,
            'product_id' => 1,
            'type' => 'date_time',
            'is_require' => 1,
            'sort_order' => 0
        ],
        [
            'option_id' => 3,
            'sku' => '',
            'max_characters' => 0,
            'file_extension' => null,
            'image_size_x' => 0,
            'image_size_y' => 0,
            'product_id' => 1,
            'type' => 'drop_down',
            'is_require' => 1,
            'sort_order' => 0
        ],
        [
            'option_id' => 4,
            'sku' => '',
            'max_characters' => 0,
            'file_extension' => null,
            'image_size_x' => 0,
            'image_size_y' => 0,
            'product_id' => 1,
            'type' => 'radio',
            'is_require' => 1,
            'sort_order' => 0
        ]
    ];

    /**
     * Array of expected (after import) option type values
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
     * Where which should be generate in case of deleting custom options
     *
     * @var string
     */
    protected $_whereForOption = 'product_id IN (1)';

    /**
     * Where which should be generate in case of deleting custom option types
     *
     * @var string
     */
    protected $_whereForType = 'option_id IN (4, 5)';

    /**
     * Page size for product option collection iterator
     *
     * @var int
     */
    protected $_iteratorPageSize = 100;

    /**
     * @var \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface
     */
    protected $errorAggregator;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    protected $metadataPoolMock;

    /**
     * Init entity adapter model
     */
    protected function setUp()
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

        $catalogDataMock = $this->getMock(\Magento\Catalog\Helper\Data::class, ['__construct'], [], '', false);

        $scopeConfig = $this->getMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);

        $timezoneInterface = $this->getMock(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class);
        $date = new \DateTime();
        $timezoneInterface->expects($this->any())->method('date')->willReturn($date);
        $this->metadataPoolMock = $this->getMock(
            \Magento\Framework\EntityManager\MetadataPool::class,
            [],
            [],
            '',
            false
        );
        $entityMetadataMock = $this->getMock(\Magento\Framework\EntityManager\EntityMetadata::class, [], [], '', false);
        $this->metadataPoolMock->expects($this->any())
            ->method('getMetadata')
            ->with(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->willReturn($entityMetadataMock);
        $entityMetadataMock->expects($this->any())
            ->method('getLinkField')
            ->willReturn('entity_id');
        $modelClassArgs = [
            $this->getMock(\Magento\ImportExport\Model\ResourceModel\Import\Data::class, [], [], '', false),
            $this->getMock(\Magento\Framework\App\ResourceConnection::class, [], [], '', false),
            $this->getMock(\Magento\ImportExport\Model\ResourceModel\Helper::class, [], [], '', false),
            $this->getMock(\Magento\Store\Model\StoreManagerInterface::class, [], [], '', false),
            $this->getMock(\Magento\Catalog\Model\ProductFactory::class, [], [], '', false),
            $this->getMock(
                \Magento\Catalog\Model\ResourceModel\Product\Option\CollectionFactory::class,
                [],
                [],
                '',
                false
            ),
            $this->getMock(
                \Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory::class,
                [],
                [],
                '',
                false
            ),
            $catalogDataMock,
            $scopeConfig,
            $timezoneInterface,
            $this->getMock(
                \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface::class,
                [],
                [],
                '',
                false
            ),
            $this->_getModelDependencies($addExpectations, $deleteBehavior, $doubleOptions)
        ];

        $modelClassName = \Magento\CatalogImportExport\Model\Import\Product\Option::class;
        $this->model = new $modelClassName(...array_values($modelClassArgs));
        // Create model mock with rewritten _getMultiRowFormat method to support test data with the old format.
        $this->modelMock = $this->getMockBuilder($modelClassName)
            ->setConstructorArgs($modelClassArgs)
            ->setMethods(['_getMultiRowFormat'])
            ->getMock();
        $reflection = new \ReflectionClass(\Magento\CatalogImportExport\Model\Import\Product\Option::class);
        $reflectionProperty = $reflection->getProperty('metadataPool');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->modelMock, $this->metadataPoolMock);
    }

    /**
     * Unset entity adapter model
     */
    protected function tearDown()
    {
        unset($this->model);
        unset($this->productEntity);
    }

    /**
     * Create mocks for all $this->model dependencies
     *
     * @param bool $addExpectations
     * @param bool $deleteBehavior
     * @param bool $doubleOptions
     * @return array
     */
    protected function _getModelDependencies($addExpectations = false, $deleteBehavior = false, $doubleOptions = false)
    {
        $connection = $this->getMock(\stdClass::class, ['delete', 'quoteInto', 'insertMultiple', 'insertOnDuplicate']);
        if ($addExpectations) {
            if ($deleteBehavior) {
                $connection->expects(
                    $this->exactly(2)
                )->method(
                    'quoteInto'
                )->will(
                    $this->returnCallback([$this, 'stubQuoteInto'])
                );
                $connection->expects(
                    $this->exactly(2)
                )->method(
                    'delete'
                )->will(
                    $this->returnCallback([$this, 'verifyDelete'])
                );
            } else {
                $connection->expects(
                    $this->once()
                )->method(
                    'insertMultiple'
                )->will(
                    $this->returnCallback([$this, 'verifyInsertMultiple'])
                );
                $connection->expects(
                    $this->exactly(6)
                )->method(
                    'insertOnDuplicate'
                )->will(
                    $this->returnCallback([$this, 'verifyInsertOnDuplicate'])
                );
            }
        }

        $resourceHelper = $this->getMock(\stdClass::class, ['getNextAutoincrement']);
        if ($addExpectations) {
            $resourceHelper->expects($this->any())->method('getNextAutoincrement')->will($this->returnValue(2));
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
     * Get source data mocks
     *
     * @param bool $addExpectations
     * @param bool $doubleOptions
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _getSourceDataMocks($addExpectations, $doubleOptions)
    {
        $csvData = $this->_loadCsvFile();

        $dataSourceModel = $this->getMock(\stdClass::class, ['getNextBunch']);
        if ($addExpectations) {
            $dataSourceModel->expects(
                $this->at(0)
            )->method(
                'getNextBunch'
            )->will(
                $this->returnValue($csvData['data'])
            );
            $dataSourceModel->expects($this->at(1))->method('getNextBunch')->will($this->returnValue(null));
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
                    'type' => $csvDataRow[\Magento\CatalogImportExport\Model\Import\Product::COL_TYPE],
                    'title' => $csvDataRow[\Magento\CatalogImportExport\Model\Import\Product::COL_NAME]
                ];
            }
        }

        $this->productEntity = $this->getMock(
            \Magento\CatalogImportExport\Model\Import\Product::class,
            ['getErrorAggregator'],
            [],
            '',
            false
        );
        $this->productEntity->method('getErrorAggregator')->willReturn($this->getErrorAggregatorObject());
        $reflection = new \ReflectionClass(\Magento\CatalogImportExport\Model\Import\Product::class);
        $reflectionProperty = $reflection->getProperty('metadataPool');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->productEntity, $this->metadataPoolMock);

        $productModelMock = $this->getMock(\stdClass::class, ['getProductEntitiesInfo']);
        $productModelMock->expects(
            $this->any()
        )->method(
            'getProductEntitiesInfo'
        )->will(
            $this->returnValue($products)
        );

        $fetchStrategy = $this->getMockForAbstractClass(
            \Magento\Framework\Data\Collection\Db\FetchStrategyInterface::class,
            ['fetchAll']
        );
        $logger = $this->getMock(\Psr\Log\LoggerInterface::class);
        $entityFactory = $this->getMock(\Magento\Framework\Data\Collection\EntityFactory::class, [], [], '', false);

        $optionCollection = $this->getMockBuilder(\Magento\Framework\Data\Collection\AbstractDb::class)
            ->setConstructorArgs([$entityFactory, $logger, $fetchStrategy])
            ->setMethods(['reset', 'addProductToFilter', 'getSelect', 'getNewEmptyItem'])
            ->getMockForAbstractClass();

        $select = $this->getMock(\Magento\Framework\DB\Select::class, ['join', 'where'], [], '', false);
        $select->expects($this->any())->method('join')->will($this->returnSelf());
        $select->expects($this->any())->method('where')->will($this->returnSelf());

        $optionCollection->expects(
            $this->any()
        )->method(
            'getNewEmptyItem'
        )->will(
            $this->returnCallback([$this, 'getNewOptionMock'])
        );
        $optionCollection->expects($this->any())->method('reset')->will($this->returnSelf());
        $optionCollection->expects($this->any())->method('addProductToFilter')->will($this->returnSelf());
        $optionCollection->expects($this->any())->method('getSelect')->will($this->returnValue($select));

        $optionsData = array_values($products);
        if ($doubleOptions) {
            foreach ($products as $product) {
                $elementIndex++;
                $product['id'] = $elementIndex;
                $optionsData[] = $product;
            }
        }

        $fetchStrategy->expects($this->any())->method('fetchAll')->will($this->returnValue($optionsData));

        $collectionIterator = $this->getMock(\stdClass::class, ['iterate']);
        $collectionIterator->expects(
            $this->any()
        )->method(
            'iterate'
        )->will(
            $this->returnCallback([$this, 'iterate'])
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
     * Iterate stub
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param \Magento\Framework\Data\Collection\AbstractDb $collection
     * @param int $pageSize
     * @param array $callbacks
     */
    public function iterate(\Magento\Framework\Data\Collection\AbstractDb $collection, $pageSize, array $callbacks)
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
     * @return \Magento\Catalog\Model\Product\Option|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getNewOptionMock()
    {
        return $this->getMock(\Magento\Catalog\Model\Product\Option::class, ['__wakeup'], [], '', false);
    }

    /**
     * Stub method to emulate adapter quoteInfo() method and get data in needed for test format
     *
     * @param string $text
     * @param array|int|float|string $value
     * @return mixed
     */
    public function stubQuoteInto($text, $value)
    {
        if (is_array($value)) {
            $value = implode(', ', $value);
        }
        return str_replace('?', $value, $text);
    }

    /**
     * Verify data, sent to $this->_connection->delete() method
     *
     * @param string $table
     * @param string $where
     */
    public function verifyDelete($table, $where)
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
     * Verify data, sent to $this->_connection->insertMultiple() method
     *
     * @param string $table
     * @param array $data
     */
    public function verifyInsertMultiple($table, array $data)
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
     * Verify data, sent to $this->_connection->insertOnDuplicate() method
     *
     * @param string $table
     * @param array $data
     * @param array $fields
     */
    public function verifyInsertOnDuplicate($table, array $data, array $fields = [])
    {
        switch ($table) {
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
     * @covers \Magento\CatalogImportExport\Model\Import\Product\Option::getEntityTypeCode
     */
    public function testGetEntityTypeCode()
    {
        $this->assertEquals('product_options', $this->model->getEntityTypeCode());
    }

    /**
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
    public function testImportDataAppendBehavior()
    {
        $this->model->importData();
    }

    /**
     * @covers \Magento\CatalogImportExport\Model\Import\Product\Option::_importData
     * @covers \Magento\CatalogImportExport\Model\Import\Product\Option::_deleteEntities
     */
    public function testImportDataDeleteBehavior()
    {
        $this->model->setParameters(['behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE]);
        $this->model->importData();
    }

    /**
     * Load and return CSV source data
     *
     * @return array
     */
    protected function _loadCsvFile()
    {
        $data = $this->_csvToArray(file_get_contents(__DIR__ . self::PATH_TO_CSV_FILE));
        return $data;
    }

    /**
     * Export CSV string to array
     *
     * @param string $content
     * @param mixed $entityId
     * @return array
     */
    protected function _csvToArray($content, $entityId = null)
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
     * @param array $rowData
     *  old format data
     * @return void
     */
    private function _bypassModelMethodGetMultiRowFormat($rowData)
    {
        $this->modelMock->expects($this->any())
                        ->method('_getMultiRowFormat')
                        ->will($this->returnValue([$rowData]));
    }

    /**
     * Test for validation of row without custom option
     *
     * @covers \Magento\CatalogImportExport\Model\Import\Product\Option::_isRowWithCustomOption
     */
    public function testValidateRowNoCustomOption()
    {
        $rowData = include __DIR__ . '/_files/row_data_no_custom_option.php';
        $this->_bypassModelMethodGetMultiRowFormat($rowData);
        $this->assertFalse($this->modelMock->validateRow($rowData, 0));
    }

    /**
     * Test for simple cases of row validation (without existing related data)
     *
     * @param array $rowData
     * @param array $errors
     *
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
    public function testValidateRow(array $rowData, array $errors)
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
     * Test for validation of ambiguous data
     *
     * @param array $rowData
     * @param array $errors
     * @param string|null $behavior
     * @param int $numberOfValidations
     *
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
    ) {
        $this->_testStores = ['admin' => 0];
        $this->setUp();
        if ($behavior) {
            $this->modelMock->setParameters(['behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND]);
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
     * Data provider of row data and errors
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function validateRowDataProvider()
    {
        return [
            'main_valid' => [
                '$rowData' => include __DIR__ . '/_files/row_data_main_valid.php',
                '$errors' => []
            ],
            'main_invalid_store' => [
                '$rowData' => include __DIR__ . '/_files/row_data_main_invalid_store.php',
                '$errors' => [
                    \Magento\CatalogImportExport\Model\Import\Product\Option::ERROR_INVALID_STORE => [1]
                ]
            ],
            'main_incorrect_type' => [
                '$rowData' => include __DIR__ . '/_files/row_data_main_incorrect_type.php',
                '$errors' => [
                    \Magento\CatalogImportExport\Model\Import\Product\Option::ERROR_INVALID_TYPE => [1]
                ]
            ],
            'main_no_title' => [
                '$rowData' => include __DIR__ . '/_files/row_data_main_no_title.php',
                '$errors' => [
                    \Magento\CatalogImportExport\Model\Import\Product\Option::ERROR_EMPTY_TITLE => [1]
                ]
            ],
            'main_empty_title' => [
                '$rowData' => include __DIR__ . '/_files/row_data_main_empty_title.php',
                '$errors' => [
                    \Magento\CatalogImportExport\Model\Import\Product\Option::ERROR_EMPTY_TITLE => [1]
                ]
            ],
            'main_invalid_price' => [
                '$rowData' => include __DIR__ . '/_files/row_data_main_invalid_price.php',
                '$errors' => [
                    \Magento\CatalogImportExport\Model\Import\Product\Option::ERROR_INVALID_PRICE => [1]
                ]
            ],
            'main_invalid_max_characters' => [
                '$rowData' => include __DIR__ . '/_files/row_data_main_invalid_max_characters.php',
                '$errors' => [
                    \Magento\CatalogImportExport\Model\Import\Product\Option::ERROR_INVALID_MAX_CHARACTERS => [1]
                ]
            ],
            'main_max_characters_less_zero' => [
                '$rowData' => include __DIR__ . '/_files/row_data_main_max_characters_less_zero.php',
                '$errors' => [
                    \Magento\CatalogImportExport\Model\Import\Product\Option::ERROR_INVALID_MAX_CHARACTERS => [1]
                ]
            ],
            'main_invalid_sort_order' => [
                '$rowData' => include __DIR__ . '/_files/row_data_main_invalid_sort_order.php',
                '$errors' => [
                    \Magento\CatalogImportExport\Model\Import\Product\Option::ERROR_INVALID_SORT_ORDER => [1]
                ]
            ],
            'main_sort_order_less_zero' => [
                '$rowData' => include __DIR__ . '/_files/row_data_main_sort_order_less_zero.php',
                '$errors' => [
                    \Magento\CatalogImportExport\Model\Import\Product\Option::ERROR_INVALID_SORT_ORDER => [1]
                ]
            ],
            'secondary_valid' => [
                '$rowData' => include __DIR__ . '/_files/row_data_secondary_valid.php',
                '$errors' => []
            ],
            'secondary_invalid_store' => [
                '$rowData' => include __DIR__ . '/_files/row_data_secondary_invalid_store.php',
                '$errors' => [
                    \Magento\CatalogImportExport\Model\Import\Product\Option::ERROR_INVALID_STORE => [1]
                ]
            ],
            'secondary_incorrect_price' => [
                '$rowData' => include __DIR__ . '/_files/row_data_secondary_incorrect_price.php',
                '$errors' => [
                    \Magento\CatalogImportExport\Model\Import\Product\Option::ERROR_INVALID_ROW_PRICE => [1]
                ]
            ],
            'secondary_incorrect_row_sort' => [
                '$rowData' => include __DIR__ . '/_files/row_data_secondary_incorrect_row_sort.php',
                '$errors' => [
                    \Magento\CatalogImportExport\Model\Import\Product\Option::ERROR_INVALID_ROW_SORT => [1]
                ]
            ],
            'secondary_row_sort_less_zero' => [
                '$rowData' => include __DIR__ . '/_files/row_data_secondary_row_sort_less_zero.php',
                '$errors' => [
                    \Magento\CatalogImportExport\Model\Import\Product\Option::ERROR_INVALID_ROW_SORT => [1]
                ]
            ]
        ];
    }

    /**
     * Data provider for test of method validateAmbiguousData
     *
     * @return array
     */
    public function validateAmbiguousDataDataProvider()
    {
        return [
            'ambiguity_several_input_rows' => [
                '$rowData' => include __DIR__ . '/_files/row_data_main_valid.php',
                '$errors' => [
                    \Magento\CatalogImportExport\Model\Import\Product\Option::ERROR_AMBIGUOUS_NEW_NAMES => [2, 2]
                ],
                '$behavior' => null,
                '$numberOfValidations' => 2
            ],
            'ambiguity_different_type' => [
                '$rowData' => include __DIR__ . '/_files/row_data_ambiguity_different_type.php',
                '$errors' => [
                    \Magento\CatalogImportExport\Model\Import\Product\Option::ERROR_AMBIGUOUS_TYPES => [1]
                ],
                '$behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND
            ],
            'ambiguity_several_db_rows' => [
                '$rowData' => include __DIR__ . '/_files/row_data_ambiguity_several_db_rows.php',
                '$errors' => [
                    \Magento\CatalogImportExport\Model\Import\Product\Option::ERROR_AMBIGUOUS_OLD_NAMES => [1]
                ],
                '$behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND
            ]
        ];
    }

    public function testParseRequiredData()
    {
        $modelData = $this->getMock(\stdClass::class, ['getNextBunch']);
        $modelData->expects(
            $this->at(0)
        )->method(
            'getNextBunch'
        )->will(
            $this->returnValue(
                [['sku' => 'simple3', '_custom_option_type' => 'field', '_custom_option_title' => 'Title']]
            )
        );
        $modelData->expects($this->at(1))->method('getNextBunch')->will($this->returnValue(null));

        $productModel = $this->getMock(\stdClass::class, ['getProductEntitiesInfo']);
        $productModel->expects($this->any())->method('getProductEntitiesInfo')->will($this->returnValue([]));

        /** @var \Magento\CatalogImportExport\Model\Import\Product $productEntityMock */
        $productEntityMock = $this->getMock(
            \Magento\CatalogImportExport\Model\Import\Product::class,
            [],
            [],
            '',
            false
        );
        $reflection = new \ReflectionClass(\Magento\CatalogImportExport\Model\Import\Product::class);
        $reflectionProperty = $reflection->getProperty('metadataPool');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($productEntityMock, $this->metadataPoolMock);

        /** @var \Magento\CatalogImportExport\Model\Import\Product\Option $model */
        $model = $this->objectManagerHelper->getObject(
            \Magento\CatalogImportExport\Model\Import\Product\Option::class,
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
        $reflection = new \ReflectionClass(\Magento\CatalogImportExport\Model\Import\Product\Option::class);
        $reflectionProperty = $reflection->getProperty('metadataPool');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($model, $this->metadataPoolMock);

        $this->assertTrue($model->importData());
    }

    public function testClearProductsSkuToId()
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
