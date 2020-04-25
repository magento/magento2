<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\Test\Unit\ResourceModel\Db\Collection;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Select\SelectRenderer;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Flag\FlagResource;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AbstractCollectionTest extends TestCase
{
    const TABLE_NAME = 'some_table';

    /** @var Uut */
    protected $uut;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var EntityFactoryInterface|MockObject */
    protected $entityFactoryMock;

    /** @var LoggerInterface|MockObject */
    protected $loggerMock;

    /** @var FetchStrategyInterface|MockObject */
    protected $fetchStrategyMock;

    /** @var ManagerInterface|MockObject */
    protected $managerMock;

    /** @var AbstractDb|MockObject  */
    protected $resourceMock;

    /** @var Mysql|MockObject */
    protected $connectionMock;

    /** @var Select|MockObject  */
    protected $selectMock;

    /** @var \Magento\Framework\App\ObjectManager|MockObject */
    protected $objectManagerMock;

    /**
     * @var \Magento\Framework\App\ObjectManager
     */
    protected $objectManagerBackup;

    protected function setUp(): void
    {
        $this->entityFactoryMock = $this->createMock(EntityFactoryInterface::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->fetchStrategyMock =
            $this->createMock(FetchStrategyInterface::class);
        $this->managerMock = $this->createMock(ManagerInterface::class);
        $this->connectionMock = $this->createMock(Mysql::class);
        $renderer = $this->createMock(SelectRenderer::class);
        $this->resourceMock = $this->createMock(FlagResource::class);

        $this->resourceMock
            ->expects($this->any())
            ->method('getConnection')
            ->will($this->returnValue($this->connectionMock));

        $this->selectMock = $this->getMockBuilder(Select::class)
            ->setMethods(['getPart', 'setPart', 'from', 'columns'])
            ->setConstructorArgs([$this->connectionMock, $renderer])
            ->getMock();

        $this->connectionMock
            ->expects($this->any())
            ->method('select')
            ->will($this->returnValue($this->selectMock));

        $this->objectManagerMock = $this->createMock(\Magento\Framework\App\ObjectManager::class);

        \Magento\Framework\App\ObjectManager::setInstance($this->objectManagerMock);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->uut = $this->getUut();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        /** @var ObjectManagerInterface|MockObject $objectManagerMock*/
        $objectManagerMock = $this->createMock(ObjectManagerInterface::class);
        \Magento\Framework\App\ObjectManager::setInstance($objectManagerMock);
    }

    /**
     * @return object
     */
    protected function getUut()
    {
        return $this->objectManagerHelper->getObject(
            Uut::class,
            [
                'entityFactory' => $this->entityFactoryMock,
                'logger' => $this->loggerMock,
                'fetchStrategy' => $this->fetchStrategyMock,
                'eventManager' => $this->managerMock,
                'connection' => $this->connectionMock,
                // Magento\Framework\Flag\FlagResource extends Magento\Framework\Model\ResourceModel\Db\AbstractDb
                'resource' => $this->resourceMock,
            ]
        );
    }

    public function testSetMainTableNull()
    {
        $this->resourceMock
            ->expects($this->any())
            ->method('getMainTable')
            ->will($this->returnValue(null));

        $this->resourceMock
            ->expects($this->any())
            ->method('getTable')
            ->will($this->returnValue(null));

        $this->uut = $this->getUut();

        $this->assertTrue($this->uut->setMainTable('') instanceof Uut);
        $this->assertEquals(null, $this->uut->getMainTable());
    }

    public function testSetMainTableFirst()
    {
        $this->resourceMock
            ->expects($this->any())
            ->method('getTable')
            ->will($this->returnValue(self::TABLE_NAME));

        $this->selectMock->expects($this->never())->method('getPart');

        $this->assertTrue($this->uut->setMainTable('') instanceof Uut);
        $this->assertEquals(self::TABLE_NAME, $this->uut->getMainTable());
    }

    public function testSetMainTableNoSelect()
    {
        $this->connectionMock
            ->expects($this->any())
            ->method('select')
            ->will($this->returnValue(null));

        $this->uut = $this->getUut();

        $this->resourceMock
            ->expects($this->any())
            ->method('getTable')
            ->will($this->returnValue(self::TABLE_NAME));

        $this->uut->setMainTable('');
        $this->selectMock->expects($this->never())->method('getPart');

        $this->assertTrue($this->uut->setMainTable('') instanceof Uut);
        $this->assertEquals(self::TABLE_NAME, $this->uut->getMainTable());
    }

    public function testSetMainTable()
    {
        $anotherTableName = 'another_table';

        $this->selectMock
            ->expects($this->atLeastOnce())
            ->method('getPart')
            ->will($this->returnValue(['main_table' => []]));

        $this->selectMock->expects($this->atLeastOnce())->method('setPart');

        $this->resourceMock
            ->expects($this->any())
            ->method('getTable')
            ->will($this->returnValueMap([['', self::TABLE_NAME], [$anotherTableName, $anotherTableName]]));

        $this->uut = $this->getUut();

        $this->assertTrue($this->uut->setMainTable('') instanceof Uut);
        $this->assertTrue($this->uut->setMainTable($anotherTableName) instanceof Uut);
        $this->assertEquals($anotherTableName, $this->uut->getMainTable());
    }

    public function testGetSelectCached()
    {
        $this->selectMock
            ->expects($this->never())
            ->method('getPart');

        $this->assertTrue($this->uut->getSelect() instanceof Select);
    }

    /**
     * @dataProvider getSelectDataProvider
     */
    public function testGetSelect($idFieldNameRet, $getPartRet, $expected)
    {
        $this->resourceMock
            ->expects($this->any())
            ->method('getIdFieldName')
            ->will($this->returnValue($idFieldNameRet));

        $this->uut->removeAllFieldsFromSelect();

        $this->selectMock
            ->expects($this->any())
            ->method('getPart')
            ->will($this->returnValue($getPartRet));

        $this->selectMock
            ->expects($this->once())
            ->method('setPart')
            ->with(Select::COLUMNS, $expected);

        $this->assertTrue($this->uut->getSelect() instanceof Select);
    }

    /**
     * @return array
     */
    public function getSelectDataProvider()
    {
        $columnMock = $this->createPartialMock(\Zend_Db_Expr::class, ['__toString']);

        return [
            [
                ['column_alias' => $columnMock],
                [['correlation', $columnMock, 'alias']],
                [
                    ['main_table', ['column_alias' => $columnMock], null],
                    'alias' => ['correlation', $columnMock, 'alias']
                ],
            ]
        ];
    }

    /**
     * @dataProvider addFieldToSelectDataProvider
     */
    public function testAddFieldToSelect($field, $alias, $expectedFieldsToSelect)
    {
        $this->assertTrue($this->uut->addFieldToSelect($field, $alias) instanceof Uut);
        $this->assertEquals($expectedFieldsToSelect, $this->uut->getFieldsToSelect());
        $this->assertTrue($this->uut->wereFieldsToSelectChanged());
    }

    /**
     * @return array
     */
    public function addFieldToSelectDataProvider()
    {
        return [
            ['*', null, null],
            [['alias' => 'column', 1 => 'column2'], null, ['alias' => 'column', 'column2']],
            ['some_field', null, ['some_field']],
            ['some_field', 'alias', ['alias' => 'some_field']]
        ];
    }

    /**
     * @dataProvider addExpressionFieldToSelectDataProvider
     */
    public function testAddExpressionFieldToSelect($alias, $expression, $fields, $expected)
    {
        $this->selectMock->expects($this->once())->method('columns')->with($expected);
        $this->assertTrue($this->uut->addExpressionFieldToSelect($alias, $expression, $fields) instanceof Uut);
    }

    /**
     * @return array
     */
    public function addExpressionFieldToSelectDataProvider()
    {
        return [
            ['alias', '', 'some_field', ['alias' => '']],
            ['alias', 'SUM({{var}})', ['var' => 'some_field'], ['alias' => 'SUM(some_field)']]
        ];
    }

    /**
     * @dataProvider removeFieldFromSelectDataProvider
     */
    public function testRemoveFieldFromSelect(
        $field,
        $isAlias,
        $initialFieldsToSelect,
        $expectedFieldsToSelect,
        $expectedWereFieldsToSelectChanged
    ) {
        $this->uut->setFieldsToSelect($initialFieldsToSelect);
        $this->assertTrue($this->uut->removeFieldFromSelect($field, $isAlias) instanceof Uut);
        $this->assertEquals($expectedFieldsToSelect, $this->uut->getFieldsToSelect());
        $this->assertEquals($expectedWereFieldsToSelectChanged, $this->uut->wereFieldsToSelectChanged());
    }

    /**
     * @return array
     */
    public function removeFieldFromSelectDataProvider()
    {
        return [
            ['some_field', false, [], [], false],
            ['field_to_remove', false, ['field_to_remove' => 'field_name'], ['field_to_remove' => 'field_name'], false],
            ['field_to_remove', true, ['field_to_remove' => 'field_name'], [], true],
            ['r', false, ['a' => 'r', 'b' => 'c'], ['b' => 'c'], true]
        ];
    }

    public function testRemoveAllFieldsFromSelect()
    {
        $this->resourceMock
            ->expects($this->any())
            ->method('getIdFieldName')
            ->will($this->returnValue('id_field'));

        $this->uut->setFieldsToSelect(['alias' => 'field']);
        $this->assertTrue($this->uut->removeAllFieldsFromSelect() instanceof Uut);
        $this->assertTrue($this->uut->wereFieldsToSelectChanged());
        $this->assertEquals(['id_field'], $this->uut->getFieldsToSelect());
    }

    public function testSetModelNotString()
    {
        $this->assertTrue($this->uut->setModel(1) instanceof Uut);
        $this->assertEmpty($this->uut->getModelName());
    }

    public function testSetModelInvalidType()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Magento\Framework\DB\Select does not extend \Magento\Framework\DataObject');
        $this->uut->setModel(Select::class);
    }

    public function testSetModel()
    {
        $this->assertTrue($this->uut->setModel(DataObject::class) instanceof Uut);
    }

    public function testGetModelName()
    {
        $this->uut->setModel(DataObject::class);
        $this->assertEquals(DataObject::class, $this->uut->getModelName());
    }

    public function testGetResourceModelName()
    {
        $this->uut->setResourceModel('string');
        $this->assertEquals('string', $this->uut->getResourceModelName());
    }

    public function testGetResource()
    {
        $this->objectManagerMock->expects($this->once())->method('create');
        $this->uut->setResource(null);
        $this->uut->getResource();
    }

    public function testGetResourceCached()
    {
        $this->objectManagerMock->expects($this->never())->method('create');
        $this->uut->getResource();
    }

    public function testGetTable()
    {
        $this->resourceMock
            ->expects($this->any())
            ->method('getTable')
            ->will($this->returnValue(self::TABLE_NAME));

        $this->assertEquals(self::TABLE_NAME, $this->uut->getTable(''));
    }

    /**
     * @dataProvider joinDataProvider
     */
    public function testJoin($table, $cond, $cols, $expected)
    {
        $this->assertTrue($this->uut->join($table, $cond, $cols) instanceof Uut);
        $this->assertEquals($expected, $this->uut->getJoinedTables());
    }

    /**
     * @return array
     */
    public function joinDataProvider()
    {
        return [
            ['table', '', '*', ['table' => true]],
            [['alias' => 'table'], '', '*', ['alias' => true]]
        ];
    }

    public function testResetItemsDataChanged()
    {
        for ($i = 0; $i < 3; $i++) {
            /** @var AbstractModel $item */
            $item = $this->getMockForAbstractClass(AbstractModel::class, [], '', false);
            $this->uut->addItem($item->setDataChanges(true));
        }

        $this->assertTrue($this->uut->resetItemsDataChanged() instanceof Uut);

        foreach ($this->uut->getItems() as $item) {
            $this->assertFalse($item->hasDataChanges());
        }
    }

    public function testSave()
    {
        for ($i = 0; $i < 3; $i++) {
            /** @var DataObject|MockObject $item */
            $item = $this->createPartialMock(DataObject::class, ['save']);
            $item->expects($this->once())->method('save');
            $this->uut->addItem($item);
        }

        $this->assertTrue($this->uut->save() instanceof Uut);
    }
}
