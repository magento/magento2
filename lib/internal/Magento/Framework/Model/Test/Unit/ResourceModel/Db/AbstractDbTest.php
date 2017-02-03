<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Framework\Model\Test\Unit\ResourceModel\Db;

class AbstractDbTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     */
    protected $_model;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resourcesMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $transactionManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $relationProcessorMock;

    protected function setUp()
    {
        $this->_resourcesMock = $this->getMock(
            '\Magento\Framework\App\ResourceConnection',
            [],
            [],
            '',
            false
        );

        $this->relationProcessorMock = $this->getMock(
            '\Magento\Framework\Model\ResourceModel\Db\ObjectRelationProcessor',
            [],
            [],
            '',
            false
        );
        $this->transactionManagerMock = $this->getMock(
            '\Magento\Framework\Model\ResourceModel\Db\TransactionManagerInterface'
        );
        $contextMock = $this->getMock('\Magento\Framework\Model\ResourceModel\Db\Context', [], [], '', false);
        $contextMock->expects($this->once())->method('getResources')->willReturn($this->_resourcesMock);
        $contextMock->expects($this->once())
            ->method('getObjectRelationProcessor')
            ->willReturn($this->relationProcessorMock);
        $contextMock->expects($this->once())
            ->method('getTransactionManager')
            ->willReturn($this->transactionManagerMock);

        $this->_model = $this->getMockForAbstractClass(
            'Magento\Framework\Model\ResourceModel\Db\AbstractDb',
            [$contextMock],
            '',
            true,
            true,
            true,
            ['_prepareDataForTable']
        );
    }

    /**
     * @param $fieldNameType
     * @param $expectedResult
     * @dataProvider addUniqueFieldDataProvider
     */
    public function testAddUniqueField($fieldNameType, $expectedResult)
    {
        $this->_model->addUniqueField($fieldNameType);
        $this->assertEquals($expectedResult, $this->_model->getUniqueFields());
    }

    /**
     * @return array
     */
    public function addUniqueFieldDataProvider()
    {
        return [
            [
                'fieldNameString',
                ['fieldNameString'],
            ],
            [
                [
                    'fieldNameArray',
                    'FieldNameArraySecond',
                ],
                [
                    [
                        'fieldNameArray',
                        'FieldNameArraySecond',
                    ]
                ]
            ],
            [
                null,
                [null]
            ]
        ];
    }

    public function testAddUniqueFieldArray()
    {
        $this->assertInstanceOf(
            '\Magento\Framework\Model\ResourceModel\Db\AbstractDb',
            $this->_model->addUniqueField(['someField'])
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Empty identifier field name
     */
    public function testGetIdFieldNameException()
    {
        $this->_model->getIdFieldName();
    }

    public function testGetIdFieldname()
    {
        $data = 'MainTableName';
        $idFieldNameProperty = new \ReflectionProperty(
            'Magento\Framework\Model\ResourceModel\Db\AbstractDb', '_idFieldName'
        );
        $idFieldNameProperty->setAccessible(true);
        $idFieldNameProperty->setValue($this->_model, $data);
        $this->assertEquals($data, $this->_model->getIdFieldName());
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Empty main table name
     */
    public function testGetMainTableException()
    {
        $this->_model->getMainTable();
    }

    /**
     * @dataProvider getTableDataProvider
     * @param $tableName
     * @param $expectedResult
     */
    public function testGetMainTable($tableName, $expectedResult)
    {
        $mainTableProperty = new \ReflectionProperty('Magento\Framework\Model\ResourceModel\Db\AbstractDb', '_mainTable');
        $mainTableProperty->setAccessible(true);
        $mainTableProperty->setValue($this->_model, $tableName);
        $this->_resourcesMock->expects($this->once())
            ->method('getTableName')
            ->with($expectedResult)
            ->will($this->returnValue($expectedResult)
            );
        $this->assertEquals($expectedResult, $this->_model->getMainTable());
    }

    public function getTableDataProvider()
    {
        return [
            [
                'tableName',
                'tableName',
            ],
            [
                [
                    'tableName',
                    'entity_suffix',
                ],
                'tableName_entity_suffix'
            ]
        ];
    }

    public function testGetTable()
    {
        $data = 'tableName';
        $this->_resourcesMock->expects($this->once())->method('getTableName')->with($data)->will(
            $this->returnValue('tableName')
        );
        $tablesProperty = new \ReflectionProperty('Magento\Framework\Model\ResourceModel\Db\AbstractDb', '_tables');
        $tablesProperty->setAccessible(true);
        $tablesProperty->setValue($this->_model, [$data]);
        $this->assertEquals($data, $this->_model->getTable($data));
    }

    public function testGetChecksumNegative()
    {
        $this->assertEquals(false, $this->_model->getChecksum(null));
    }

    /**
     * @dataProvider getChecksumProvider
     * @param $checksum
     * @param $expected
     */
    public function testGetChecksum($checksum, $expected)
    {
        $connectionMock = $this->getMock('\Magento\Framework\DB\Adapter\AdapterInterface', [], [], '', false);
        $connectionMock->expects($this->once())->method('getTablesChecksum')->with($checksum)->will(
            $this->returnValue([$checksum => 'checksum'])
        );
        $this->_resourcesMock->expects($this->any())->method('getConnection')->will(
            $this->returnValue($connectionMock)
        );
        $this->assertEquals($expected, $this->_model->getChecksum($checksum));
    }

    public function getChecksumProvider()
    {
        return [
            [
                'checksum',
                'checksum',
            ],
            [
                14,
                'checksum'
            ]
        ];
    }

    public function testResetUniqueField()
    {
        $uniqueFields = new \ReflectionProperty('Magento\Framework\Model\ResourceModel\Db\AbstractDb', '_uniqueFields');
        $uniqueFields->setAccessible(true);
        $uniqueFields->setValue($this->_model, ['uniqueField1', 'uniqueField2']);
        $this->_model->resetUniqueField();
        $this->assertEquals([], $this->_model->getUniqueFields());
    }

    public function testGetUniqueFields()
    {
        $uniqueFieldsReflection = new \ReflectionProperty(
            'Magento\Framework\Model\ResourceModel\Db\AbstractDb',
            '_uniqueFields'
        );
        $uniqueFieldsReflection->setAccessible(true);
        $uniqueFieldsReflection->setValue($this->_model, null);
        $this->assertEquals([], $this->_model->getUniqueFields());
    }

    public function testGetValidationRulesBeforeSave()
    {
        $this->assertNull($this->_model->getValidationRulesBeforeSave());
    }

    public function testLoad()
    {
        $contextMock = $this->getMock('\Magento\Framework\Model\Context', [], [], '', false);
        $registryMock = $this->getMock('\Magento\Framework\Registry', [], [], '', false);
        $abstractModelMock = $this->getMockForAbstractClass(
            '\Magento\Framework\Model\AbstractModel',
            [$contextMock, $registryMock],
            '',
            false,
            true,
            true,
            ['__wakeup']
        );

        $value = 'some_value';
        $idFieldName = new \ReflectionProperty('Magento\Framework\Model\ResourceModel\Db\AbstractDb', '_idFieldName');
        $idFieldName->setAccessible(true);
        $idFieldName->setValue($this->_model, 'field_value');

        $this->assertInstanceOf(
            'Magento\Framework\Model\ResourceModel\Db\AbstractDb',
            $this->_model->load($abstractModelMock, $value, $idFieldName)
        );
    }

    public function testDelete()
    {
        $connectionInterfaceMock = $this->getMock('\Magento\Framework\DB\Adapter\AdapterInterface', [], [], '', false);
        $contextMock = $this->getMock('\Magento\Framework\Model\Context', [], [], '', false);
        $registryMock = $this->getMock('\Magento\Framework\Registry', [], [], '', false);
        $abstractModelMock = $this->getMockForAbstractClass(
            '\Magento\Framework\Model\AbstractModel',
            [$contextMock, $registryMock],
            '',
            false,
            true,
            true,
            ['__wakeup', 'getId', 'beforeDelete', 'afterDelete', 'afterDeleteCommit', 'getData']
        );
        $this->_resourcesMock->expects($this->any())
            ->method('getConnection')
            ->will($this->returnValue($connectionInterfaceMock)
        );

        $abstractModelMock->expects($this->once())->method('getData')->willReturn(['data' => 'value']);
        $connectionMock = $this->getMock('\Magento\Framework\DB\Adapter\AdapterInterface');
        $this->transactionManagerMock->expects($this->once())
            ->method('start')
            ->with($connectionInterfaceMock)
            ->willReturn($connectionMock);

        $this->relationProcessorMock->expects($this->once())
            ->method('delete')
            ->with(
                $this->transactionManagerMock,
                $connectionMock,
                'tableName',
                'idFieldName',
                ['data' => 'value']
            );

        $this->transactionManagerMock->expects($this->once())->method('commit');

        $data = 'tableName';
        $this->_resourcesMock->expects($this->any())->method('getTableName')->with($data)->will(
            $this->returnValue('tableName')
        );
        $mainTableReflection = new \ReflectionProperty(
            'Magento\Framework\Model\ResourceModel\Db\AbstractDb',
            '_mainTable'
        );
        $mainTableReflection->setAccessible(true);
        $mainTableReflection->setValue($this->_model, 'tableName');
        $idFieldNameReflection = new \ReflectionProperty(
            'Magento\Framework\Model\ResourceModel\Db\AbstractDb',
            '_idFieldName'
        );
        $idFieldNameReflection->setAccessible(true);
        $idFieldNameReflection->setValue($this->_model, 'idFieldName');
        $connectionInterfaceMock->expects($this->any())->method('delete')->with('tableName', 'idFieldName');
        $connectionInterfaceMock->expects($this->any())->method('quoteInto')->will($this->returnValue('idFieldName'));
        $abstractModelMock->expects($this->once())->method('beforeDelete');
        $abstractModelMock->expects($this->once())->method('afterDelete');
        $abstractModelMock->expects($this->once())->method('afterDeleteCommit');
        $this->assertInstanceOf(
            'Magento\Framework\Model\ResourceModel\Db\AbstractDb',
            $this->_model->delete($abstractModelMock)
        );
    }

    public function testHasDataChangedNegative()
    {
        $contextMock = $this->getMock('\Magento\Framework\Model\Context', [], [], '', false);
        $registryMock = $this->getMock('\Magento\Framework\Registry', [], [], '', false);
        $abstractModelMock = $this->getMockForAbstractClass(
            '\Magento\Framework\Model\AbstractModel',
            [$contextMock, $registryMock],
            '',
            false,
            true,
            true,
            ['__wakeup', 'getOrigData']
        );
        $abstractModelMock->expects($this->any())->method('getOrigData')->will($this->returnValue(false));
        $this->assertTrue($this->_model->hasDataChanged($abstractModelMock));
    }

    /**
     * @dataProvider hasDataChangedDataProvider
     * @param string $getOriginData
     * @param bool $expected
     */
    public function testGetDataChanged($getOriginData, $expected)
    {
        $connectionInterfaceMock = $this->getMock('\Magento\Framework\DB\Adapter\AdapterInterface', [], [], '', false);
        $this->_resourcesMock->expects($this->any())->method('getConnection')->will(
            $this->returnValue($connectionInterfaceMock)
        );
        $contextMock = $this->getMock('\Magento\Framework\Model\Context', [], [], '', false);
        $registryMock = $this->getMock('\Magento\Framework\Registry', [], [], '', false);
        $abstractModelMock = $this->getMockForAbstractClass(
            '\Magento\Framework\Model\AbstractModel',
            [$contextMock, $registryMock],
            '',
            false,
            true,
            true,
            ['__wakeup', 'getOrigData', 'getData']
        );
        $mainTableProperty = new \ReflectionProperty('Magento\Framework\Model\ResourceModel\Db\AbstractDb', '_mainTable');
        $mainTableProperty->setAccessible(true);
        $mainTableProperty->setValue($this->_model, 'table');

        $this->_resourcesMock->expects($this->once())
            ->method('getTableName')
            ->with('table')
            ->will($this->returnValue('tableName')
            );
        $abstractModelMock->expects($this->at(0))->method('getOrigData')->will($this->returnValue(true));
        $abstractModelMock->expects($this->at(1))->method('getOrigData')->will($this->returnValue($getOriginData));
        $connectionInterfaceMock->expects($this->any())->method('describeTable')->with('tableName')->will(
            $this->returnValue(['tableName'])
        );
        $this->assertEquals($expected, $this->_model->hasDataChanged($abstractModelMock));
    }

    public function hasDataChangedDataProvider()
    {
        return [
            [true, true],
            [null, false]
        ];
    }

    public function testPrepareDataForUpdate()
    {
        $connectionMock = $this->getMock('\Magento\Framework\DB\Adapter\AdapterInterface', [], [], '', false);
        $context = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))->getObject(
                'Magento\Framework\Model\Context'
        );
        $registryMock = $this->getMock('\Magento\Framework\Registry', [], [], '', false);
        $resourceMock = $this->getMock(
            'Magento\Framework\Model\ResourceModel\Db\AbstractDb',
            [
                '_construct',
                'getConnection',
                '__wakeup',
                'getIdFieldName'
            ],
            [],
            '',
            false
        );
        $connectionInterfaceMock = $this->getMock('Magento\Framework\DB\Adapter\AdapterInterface', [], [], '', false);
        $resourceMock->expects($this->any())
            ->method('getConnection')
            ->will($this->returnValue($connectionInterfaceMock));
        $resourceCollectionMock = $this->getMockBuilder('Magento\Framework\Data\Collection\AbstractDb')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractModelMock = $this->getMockForAbstractClass(
            'Magento\Framework\Model\AbstractModel',
            [$context, $registryMock, $resourceMock, $resourceCollectionMock]
        );
        $data = 'tableName';
        $this->_resourcesMock->expects($this->any())
            ->method('getConnection')
            ->will($this->returnValue($connectionMock)
        );
        $this->_resourcesMock->expects($this->any())->method('getTableName')->with($data)->will(
            $this->returnValue('tableName')
        );
        $mainTableReflection = new \ReflectionProperty(
            'Magento\Framework\Model\ResourceModel\Db\AbstractDb',
            '_mainTable'
        );
        $mainTableReflection->setAccessible(true);
        $mainTableReflection->setValue($this->_model, 'tableName');
        $idFieldNameReflection = new \ReflectionProperty(
            'Magento\Framework\Model\ResourceModel\Db\AbstractDb',
            '_idFieldName'
        );
        $idFieldNameReflection->setAccessible(true);
        $idFieldNameReflection->setValue($this->_model, 'idFieldName');
        $connectionMock->expects($this->any())->method('save')->with('tableName', 'idFieldName');
        $connectionMock->expects($this->any())->method('quoteInto')->will($this->returnValue('idFieldName'));

        $abstractModelMock->setIdFieldName('id');
        $abstractModelMock->setData(
            [
                'id'    => 12345,
                'name'  => 'Test Name',
                'value' => 'Test Value'
            ]
        );
        $abstractModelMock->afterLoad();
        $this->assertEquals($abstractModelMock->getData(), $abstractModelMock->getStoredData());
        $newData = ['value' => 'Test Value New'];
        $this->_model->expects($this->once())->method('_prepareDataForTable')->will($this->returnValue($newData));
        $abstractModelMock->addData($newData);
        $this->assertNotEquals($abstractModelMock->getData(), $abstractModelMock->getStoredData());
        $abstractModelMock->isObjectNew(false);
        $connectionMock->expects($this->once())
            ->method('update')
            ->with(
                'tableName',
                $newData,
                'idFieldName'
            );

        $this->_model->save($abstractModelMock);
    }

    /**
     * Test that we only set/override id on object if PK autoincrement is enabled
     * @param bool $pkIncrement
     * @dataProvider saveNewObjectDataProvider
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function testSaveNewObject($pkIncrement)
    {
        /**
         * Mock SUT so as not to test extraneous logic
         */
        $model = $this->getMockBuilder('Magento\Framework\Model\ResourceModel\Db\AbstractDb')
            ->disableOriginalConstructor()
            ->setMethods(['_prepareDataForSave', 'getIdFieldName', 'getConnection', 'getMainTable'])
            ->getMockForAbstractClass();
        /**
         * Only testing the logic in a protected method and property, must use reflection to avoid dealing with large
         * amounts of unrelated logic in save function
         *
         * make saveNewObject and _isPkAutoIncrement public
         */
        $reflectionMethod = new \ReflectionMethod($model, 'saveNewObject');
        $reflectionMethod->setAccessible(true);
        $reflectionProperty = new \ReflectionProperty($model, '_isPkAutoIncrement');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($model, $pkIncrement);

        // Mocked behavior
        $connectionMock = $this->getMockBuilder('\Magento\Framework\DB\Adapter\AdapterInterface')
            ->disableOriginalConstructor()
            ->setMethods(['lastInsertId'])
            ->getMockForAbstractClass();
        $getConnectionInvokedCount = $pkIncrement ? 2 : 1;
        $model->expects($this->exactly($getConnectionInvokedCount))
            ->method('getConnection')
            ->willReturn($connectionMock);

        $idFieldName = 'id_field_name';
        $model->expects($this->once())->method('_prepareDataForSave')->willReturn([$idFieldName => 'id',]);


        // Test expectations
        //      Only get object's id field name if not PK autoincrement
        $getIdFieldNameInvokedCount = $pkIncrement ? 1 : 0;
        $model->expects($this->exactly($getIdFieldNameInvokedCount))
            ->method('getIdFieldName')
            ->willReturn($idFieldName);

        //      Only set object id if not PK autoincrement
        $setIdInvokedCount = $pkIncrement ? 1 : 0;
        $inputObject = $this->getMockBuilder('\Magento\Framework\Model\AbstractModel')
            ->disableOriginalConstructor()
            ->getMock();
        $inputObject->expects($this->exactly($setIdInvokedCount))->method('setId');

        //      Only call lastInsertId if not PK autoincrement
        $lastInsertIdInvokedCount = $pkIncrement ? 1 : 0;
        $connectionMock->expects($this->exactly($lastInsertIdInvokedCount))->method('lastInsertId');

        $reflectionMethod->invokeArgs($model, [$inputObject]);
    }

    public function saveNewObjectDataProvider()
    {
        return [[true], [false]];
    }

}
