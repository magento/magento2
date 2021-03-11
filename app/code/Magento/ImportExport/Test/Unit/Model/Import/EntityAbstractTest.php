<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\ImportExport\Model\Import\EntityAbstract
 *
 * @todo Fix tests in the scope of https://wiki.magento.com/display/MAGE2/Technical+Debt+%28Team-Donetsk-B%29
 */
namespace Magento\ImportExport\Test\Unit\Model\Import;

use Magento\ImportExport\Model\Import\AbstractEntity;

class EntityAbstractTest extends \Magento\ImportExport\Test\Unit\Model\Import\AbstractImportTestCase
{
    /**
     * Abstract import entity model
     *
     * @var AbstractEntity|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_model;

    /**
     * List of available behaviors
     *
     * @var array
     */
    protected $_availableBehaviors = [
        \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE,
        \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE,
        \Magento\ImportExport\Model\Import::BEHAVIOR_CUSTOM,
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->_model = $this->getMockBuilder(\Magento\ImportExport\Model\Import\AbstractEntity::class)
            ->setConstructorArgs($this->_getModelDependencies())
            ->setMethods(['_saveValidatedBunches'])
            ->getMockForAbstractClass();
    }

    protected function tearDown(): void
    {
        unset($this->_model);
    }

    /**
     * Create mocks for all $this->_model dependencies
     *
     * @return array
     */
    protected function _getModelDependencies()
    {
        $string = new \Magento\Framework\Stdlib\StringUtils();
        $scopeConfig = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $importFactory = $this->createMock(\Magento\ImportExport\Model\ImportFactory::class);
        $resourceHelper = $this->createMock(\Magento\ImportExport\Model\ResourceModel\Helper::class);
        $resource = $this->createMock(\Magento\Framework\App\ResourceConnection::class);

        $data = [
            'coreString' => $string,
            'scopeConfig' => $scopeConfig,
            'importFactory' => $importFactory,
            'resourceHelper' => $resourceHelper,
            'resource' => $resource,
            'errorAggregator' => $this->getErrorAggregatorObject(),
            'data' => [
                'data_source_model' => 'not_used',
                'connection' => 'not_used',
                'helpers' => [],
                'page_size' => 1,
                'max_data_size' => 1,
                'bunch_size' => 1,
                'collection_by_pages_iterator' => 'not_used',
            ]
        ];

        return $data;
    }

    /**
     * Test for method _prepareRowForDb()
     *
     * @covers \Magento\ImportExport\Model\Import\AbstractEntity::_prepareRowForDb
     */
    public function testPrepareRowForDb()
    {
        $expected = [
            'test1' => 100,
            'test2' => null,
            'test3' => '',
            'test4' => 0,
            'test5' => 'test',
            'test6' => [1, 2, 3],
            'test7' => [],
        ];

        $method = new \ReflectionMethod($this->_model, '_prepareRowForDb');
        $method->setAccessible(true);
        $actual = $method->invoke($this->_model, $expected);

        $expected['test3'] = null;
        $this->assertSame($expected, $actual);
    }

    /**
     * Test for method addRowError()
     */
    public function testAddRowError()
    {
        $errorCode = 'error_code';
        $errorColumnName = 'error_column';
        $this->_model->addRowError($errorCode . '%s', 0, $errorColumnName, $errorCode . ' %s');

        $this->assertGreaterThan(0, $this->_model->getErrorAggregator()->getErrorsCount());

        $errors = $this->_model->getErrorAggregator()->getRowsGroupedByErrorCode();
        $this->assertArrayHasKey($errorCode . ' ' . $errorColumnName, $errors);
    }

    /**
     * Test for method importData()
     */
    public function testImportData()
    {
        $this->_model->expects($this->once())->method('_importData');
        $this->_model->importData();
    }

    /**
     * Test for method isAttributeParticular()
     */
    public function testIsAttributeParticular()
    {
        $attributeCode = 'test';

        $property = new \ReflectionProperty($this->_model, '_specialAttributes');
        $property->setAccessible(true);
        $property->setValue($this->_model, [$attributeCode]);

        $this->assertTrue($this->_model->isAttributeParticular($attributeCode));
    }

    /**
     * Test for method _addMessageTemplate()
     */
    public function testAddMessageTemplate()
    {
        $errorCode = 'test';
        $message = 'This is test error message';
        $this->_model->addMessageTemplate($errorCode, $message);

        $this->_model->addRowError($errorCode, 0);
        $errors = $this->_model->getErrorAggregator()->getRowsGroupedByErrorCode();

        $this->assertArrayHasKey($message, $errors);
    }

    /**
     * Test for method isRowAllowedToImport()
     */
    public function testIsRowAllowedToImport()
    {
        $rows = 4;
        $skippedRows = [2 => true, 4 => true];
        $property = new \ReflectionProperty($this->_model, '_skippedRows');
        $property->setAccessible(true);
        $property->setValue($this->_model, $skippedRows);

        $modelForValidateRow = clone $this->_model;
        $modelForValidateRow->expects($this->any())->method('validateRow')->willReturn(false);

        for ($i = 1; $i <= $rows; $i++) {
            $this->assertFalse($modelForValidateRow->isRowAllowedToImport([], $i));
        }

        $modelForIsAllowed = clone $this->_model;
        $modelForIsAllowed->expects($this->any())->method('validateRow')->willReturn(true);

        for ($i = 1; $i <= $rows; $i++) {
            $expected = true;
            if (isset($skippedRows[$i])) {
                $expected = !$skippedRows[$i];
            }
            $this->assertSame($expected, $modelForIsAllowed->isRowAllowedToImport([], $i));
        }
    }

    /**
     * Test for method getBehavior() with $rowData argument = null
     *
     * @covers \Magento\ImportExport\Model\Import\AbstractEntity::getBehavior
     */
    public function testGetBehaviorWithoutRowData()
    {
        $property = new \ReflectionProperty($this->_model, '_availableBehaviors');
        $property->setAccessible(true);
        $property->setValue($this->_model, $this->_availableBehaviors);

        $default = AbstractEntity::getDefaultBehavior();

        foreach ($this->_availableBehaviors as $behavior) {
            $this->_model->setParameters(['behavior' => $behavior]);
            $this->assertSame($behavior, $this->_model->getBehavior());
        }

        $this->_model->setParameters(['behavior' => 'incorrect_string']);
        $this->assertSame($default, $this->_model->getBehavior());
    }

    /**
     * Different cases to cover all code parts in AbstractEntity::getBehavior()
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return array
     */
    public function dataProviderForTestGetBehaviorWithRowData()
    {
        return [
            "add/update behavior and row with delete in action column" => [
                '$inputBehavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE,
                '$rowData' => [
                    AbstractEntity::COLUMN_ACTION => AbstractEntity::COLUMN_ACTION_VALUE_DELETE,
                ],
                '$expectedBehavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE,
            ],
            "delete behavior and row with delete in action column" => [
                '$inputBehavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE,
                '$rowData' => [
                    AbstractEntity::COLUMN_ACTION => AbstractEntity::COLUMN_ACTION_VALUE_DELETE,
                ],
                '$expectedBehavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE,
            ],
            "custom behavior and row with delete in action column" => [
                '$inputBehavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_CUSTOM,
                '$rowData' => [
                    AbstractEntity::COLUMN_ACTION => AbstractEntity::COLUMN_ACTION_VALUE_DELETE,
                ],
                '$expectedBehavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE,
            ],
            "add/update behavior and row with update in action column" => [
                '$inputBehavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE,
                '$rowData' => [AbstractEntity::COLUMN_ACTION => 'update'],
                '$expectedBehavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE,
            ],
            "delete behavior and row with update in action column" => [
                '$inputBehavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE,
                '$rowData' => [AbstractEntity::COLUMN_ACTION => 'update'],
                '$expectedBehavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE,
            ],
            "custom behavior and row with update in action column" => [
                '$inputBehavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_CUSTOM,
                '$rowData' => [AbstractEntity::COLUMN_ACTION => 'update'],
                '$expectedBehavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE,
            ],
            "add/update behavior and row with bogus string in action column" => [
                '$inputBehavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE,
                '$rowData' => [
                    AbstractEntity::COLUMN_ACTION => microtime(true),
                ],
                '$expectedBehavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE,
            ],
            "delete behavior and row with bogus string in action column" => [
                '$inputBehavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE,
                '$rowData' => [
                    AbstractEntity::COLUMN_ACTION => microtime(true),
                ],
                '$expectedBehavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE,
            ],
            "custom behavior and row with bogus string in action column" => [
                '$inputBehavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_CUSTOM,
                '$rowData' => [
                    AbstractEntity::COLUMN_ACTION => microtime(true),
                ],
                '$expectedBehavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE,
            ],
            "add/update behavior and row with null in action column" => [
                '$inputBehavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE,
                '$rowData' => [AbstractEntity::COLUMN_ACTION => null],
                '$expectedBehavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE,
            ],
            "delete behavior and row with null in action column" => [
                '$inputBehavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE,
                '$rowData' => [AbstractEntity::COLUMN_ACTION => null],
                '$expectedBehavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE,
            ],
            "custom behavior and row with null in action column" => [
                '$inputBehavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_CUSTOM,
                '$rowData' => [AbstractEntity::COLUMN_ACTION => null],
                '$expectedBehavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE,
            ],
            "add/update behavior and empty row" => [
                '$inputBehavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE,
                '$rowData' => null,
                '$expectedBehavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE,
            ],
            "delete behavior and empty row" => [
                '$inputBehavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE,
                '$rowData' => null,
                '$expectedBehavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE,
            ],
            "custom behavior and empty row" => [
                '$inputBehavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_CUSTOM,
                '$rowData' => null,
                '$expectedBehavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_CUSTOM,
            ],
            "add/update behavior and row is empty array" => [
                '$inputBehavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE,
                '$rowData' => [],
                '$expectedBehavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE,
            ],
            "delete behavior and empty row is empty array" => [
                '$inputBehavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE,
                '$rowData' => [],
                '$expectedBehavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE,
            ],
            "custom behavior and empty row is empty array" => [
                '$inputBehavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_CUSTOM,
                '$rowData' => [],
                '$expectedBehavior' => AbstractEntity::getDefaultBehavior(),
            ],
            "custom behavior and row with delete in action column and empty available behaviors" => [
                '$inputBehavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_CUSTOM,
                '$rowData' => [
                    AbstractEntity::COLUMN_ACTION => AbstractEntity::COLUMN_ACTION_VALUE_DELETE,
                ],
                '$expectedBehavior' => AbstractEntity::getDefaultBehavior(),
                '$availableBehaviors' => [],
            ],
            "custom behavior and row with update in action column and empty available behaviors" => [
                '$inputBehavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_CUSTOM,
                '$rowData' => [AbstractEntity::COLUMN_ACTION => 'update'],
                '$expectedBehavior' => AbstractEntity::getDefaultBehavior(),
                '$availableBehaviors' => [],
            ],
            "custom behavior and row with bogus string in action column and empty available behaviors" => [
                '$inputBehavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_CUSTOM,
                '$rowData' => [
                    AbstractEntity::COLUMN_ACTION => microtime(true),
                ],
                '$expectedBehavior' => AbstractEntity::getDefaultBehavior(),
                '$availableBehaviors' => [],
            ],
            "custom behavior and row with null in action column and empty available behaviors" => [
                '$inputBehavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_CUSTOM,
                '$rowData' => [AbstractEntity::COLUMN_ACTION => null],
                '$expectedBehavior' => AbstractEntity::getDefaultBehavior(),
                '$availableBehaviors' => [],
            ]
        ];
    }

    /**
     * Test for method getBehavior() with $rowData argument = null
     *
     * @covers \Magento\ImportExport\Model\Import\AbstractEntity::getBehavior
     *
     * @dataProvider dataProviderForTestGetBehaviorWithRowData
     * @param $inputBehavior
     * @param $rowData
     * @param $expectedBehavior
     * @param null $availableBehaviors
     */
    public function testGetBehaviorWithRowData($inputBehavior, $rowData, $expectedBehavior, $availableBehaviors = null)
    {
        $property = new \ReflectionProperty($this->_model, '_availableBehaviors');
        $property->setAccessible(true);

        if (isset($availableBehaviors)) {
            $property->setValue($this->_model, $availableBehaviors);
        } else {
            $property->setValue($this->_model, $this->_availableBehaviors);
        }
        $this->_model->setParameters(['behavior' => $inputBehavior]);
        $this->assertSame($expectedBehavior, $this->_model->getBehavior($rowData));
    }

    /**
     * Test for method isAttributeValid()
     *
     * @param array $data
     * @dataProvider attributeList
     */
    public function testIsAttributeValid(array $data)
    {
        $attributeCode = $data['code'];
        $attributeParams = [
            'type' => $data['type'],
            'options' => isset($data['options']) ? $data['options'] : null,
            'is_unique' => isset($data['is_unique']) ? $data['is_unique'] : null,
        ];

        $rowData = [$attributeCode => $data['valid_value']];
        $this->assertTrue($this->_model->isAttributeValid($attributeCode, $attributeParams, $rowData, 0));

        $rowData[$attributeCode] = $data['invalid_value'];
        $this->assertFalse($this->_model->isAttributeValid($attributeCode, $attributeParams, $rowData, 0));
        $this->assertEquals(1, $this->_model->getErrorAggregator()->getErrorsCount(), 'Wrong count of errors');
    }

    /**
     * Data provide which retrieve data for test attributes
     *
     * @return array
     */
    public function attributeList()
    {
        $longString = str_pad('', AbstractEntity::DB_MAX_TEXT_LENGTH, 'x');

        return [
            [$this->_getDataSet('test1', 'decimal', 1.5, 'test')],
            [
                $this->_getDataSet(
                    'test2',
                    'varchar',
                    'test string',
                    substr($longString, 0, AbstractEntity::DB_MAX_VARCHAR_LENGTH)
                )
            ],
            [
                $this->_getDataSet(
                    'test3',
                    'select',
                    'test2',
                    'custom',
                    null,
                    ['test1' => 1, 'test2' => 2, 'test3' => 3]
                )
            ],
            [
                $this->_getDataSet(
                    'test4',
                    'multiselect',
                    'test2',
                    'custom',
                    null,
                    ['test1' => 1, 'test2' => 2, 'test3' => 3]
                )
            ],
            [$this->_getDataSet('test5', 'int', 100, 'custom')],
            [$this->_getDataSet('test6', 'datetime', '2012-06-15 15:50', '2012-30-30')],
            [$this->_getDataSet('test7', 'text', 'test string', $longString)],
            [$this->_getDataSet('test8', 'int', 1, 1, true)],
            [$this->_getDataSet('test9', 'datetime', '2012-02-29', '02/29/2012 11:12:67')],
            [$this->_getDataSet('test10', 'datetime', '29.02.2012', '11.02.4 11:12:59')],
            [$this->_getDataSet('test11', 'datetime', '02/29/2012', '2012-13-29 21:12:59')],
            [$this->_getDataSet('test12', 'datetime', '02/29/2012 11:12:59', '32.12.2012')],
            [
                [
                    'code' => 'test7',
                    'type' => 'datetime',
                    'valid_value' => '2012-02-29',
                    'invalid_value' => '02/29/2012 11:12:67',
                ]
            ],
            [
                [
                    'code' => 'test7',
                    'type' => 'datetime',
                    'valid_value' => '29.02.2012',
                    'invalid_value' => '11.02.4 11:12:59',
                ]
            ],
            [
                [
                    'code' => 'test7',
                    'type' => 'datetime',
                    'valid_value' => '02/29/2012',
                    'invalid_value' => '2012-13-29 21:12:59',
                ]
            ]
        ];
    }

    /**
     * @param string $code
     * @param string $type
     * @param int|string $validValue
     * @param $invalidValue
     * @param null $isUnique
     * @param null $options
     * @return array
     */
    protected function _getDataSet($code, $type, $validValue, $invalidValue, $isUnique = null, $options = null)
    {
        $dataSet = [
            'code' => $code,
            'type' => $type,
            'valid_value' => $validValue,
            'invalid_value' => $invalidValue,
        ];
        if ($isUnique !== null) {
            $dataSet['is_unique'] = $isUnique;
        }
        if ($options !== null) {
            $dataSet['options'] = $options;
        }
        return $dataSet;
    }

    /**
     * Test for method validateData()
     *
     * @covers \Magento\ImportExport\Model\Import\AbstractEntity::validateData
     */
    public function testValidateDataPermanentAttributes()
    {
        $columns = ['test1', 'test2'];
        $this->_createSourceAdapterMock($columns);

        $permanentAttributes = ['test2', 'test3'];
        $property = new \ReflectionProperty($this->_model, '_permanentAttributes');
        $property->setAccessible(true);
        $property->setValue($this->_model, $permanentAttributes);

        $errorAggregator = $this->_model->validateData();
        $this->assertEquals(1, $errorAggregator->getErrorsCount());
    }

    /**
     * Test for method validateData()
     *
     * @covers \Magento\ImportExport\Model\Import\AbstractEntity::validateData
     */
    public function testValidateDataEmptyColumnName()
    {
        $this->_createSourceAdapterMock(['']);
        $errorAggregator = $this->_model->validateData();
        $this->assertEquals(1, $errorAggregator->getErrorsCount());
    }

    /**
     * Test for method validateData()
     *
     * @covers \Magento\ImportExport\Model\Import\AbstractEntity::validateData
     */
    public function testValidateDataColumnNameWithWhitespaces()
    {
        $this->_createSourceAdapterMock(['  ']);
        $this->_model->validateData();
        $errorAggregator = $this->_model->validateData();
        $this->assertEquals(1, $errorAggregator->getErrorsCount());
    }

    /**
     * Test for method validateData()
     *
     * @covers \Magento\ImportExport\Model\Import\AbstractEntity::validateData
     */
    public function testValidateDataAttributeNames()
    {
        $this->_createSourceAdapterMock(['_test1']);
        $errorAggregator = $this->_model->validateData();
        $this->assertEquals(1, $errorAggregator->getErrorsCount());
    }

    /**
     * Create source adapter mock and set it into model object which tested in this class
     *
     * @param array $columns value which will be returned by method getColNames()
     * @return \Magento\ImportExport\Model\Import\AbstractSource|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function _createSourceAdapterMock(array $columns)
    {
        /** @var $source \Magento\ImportExport\Model\Import\AbstractSource|\PHPUnit\Framework\MockObject\MockObject */
        $source = $this->getMockForAbstractClass(
            \Magento\ImportExport\Model\Import\AbstractSource::class,
            [],
            '',
            false,
            true,
            true,
            ['getColNames']
        );
        $source->expects($this->any())->method('getColNames')->willReturn($columns);
        $this->_model->setSource($source);

        return $source;
    }
}
