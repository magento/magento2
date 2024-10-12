<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

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
        $this->entityFactoryMock = $this->getMockForAbstractClass(EntityFactoryInterface::class);
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->fetchStrategyMock =
            $this->getMockForAbstractClass(FetchStrategyInterface::class);
        $this->managerMock = $this->getMockForAbstractClass(ManagerInterface::class);
        $this->connectionMock = $this->createMock(Mysql::class);
        $renderer = $this->createMock(SelectRenderer::class);
        $this->resourceMock = $this->createMock(FlagResource::class);

        $this->resourceMock
            ->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connectionMock);

        $this->selectMock = $this->getMockBuilder(Select::class)
            ->onlyMethods(['getPart', 'setPart', 'from', 'columns'])
            ->setConstructorArgs([$this->connectionMock, $renderer])
            ->getMock();

        $this->connectionMock
            ->expects($this->any())
            ->method('select')
            ->willReturn($this->selectMock);

        $this->objectManagerMock = $this->createMock(\Magento\Framework\App\ObjectManager::class);

        \Magento\Framework\App\ObjectManager::setInstance($this->objectManagerMock);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->uut = $this->getUut();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        /** @var ObjectManagerInterface|MockObject $objectManagerMock*/
        $objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
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
            ->willReturn(null);

        $this->resourceMock
            ->expects($this->any())
            ->method('getTable')
            ->willReturn(null);

        $this->uut = $this->getUut();

        $this->assertInstanceOf(Uut::class, $this->uut->setMainTable(''));
        $this->assertNull($this->uut->getMainTable());
    }

    public function testSetMainTableFirst()
    {
        $this->resourceMock
            ->expects($this->any())
            ->method('getTable')
            ->willReturn(self::TABLE_NAME);

        $this->selectMock->expects($this->never())->method('getPart');

        $this->assertInstanceOf(Uut::class, $this->uut->setMainTable(''));
        $this->assertEquals(self::TABLE_NAME, $this->uut->getMainTable());
    }

    public function testSetMainTableNoSelect()
    {
        $this->connectionMock
            ->expects($this->any())
            ->method('select')
            ->willReturn(null);

        $this->uut = $this->getUut();

        $this->resourceMock
            ->expects($this->any())
            ->method('getTable')
            ->willReturn(self::TABLE_NAME);

        $this->uut->setMainTable('');
        $this->selectMock->expects($this->never())->method('getPart');

        $this->assertInstanceOf(Uut::class, $this->uut->setMainTable(''));
        $this->assertEquals(self::TABLE_NAME, $this->uut->getMainTable());
    }

    public function testSetMainTable()
    {
        $anotherTableName = 'another_table';

        $this->selectMock
            ->expects($this->atLeastOnce())
            ->method('getPart')
            ->willReturn(['main_table' => []]);

        $this->selectMock->expects($this->atLeastOnce())->method('setPart');

        $this->resourceMock
            ->expects($this->any())
            ->method('getTable')
            ->willReturnMap([['', self::TABLE_NAME], [$anotherTableName, $anotherTableName]]);

        $this->uut = $this->getUut();

        $this->assertInstanceOf(Uut::class, $this->uut->setMainTable(''));
        $this->assertInstanceOf(Uut::class, $this->uut->setMainTable($anotherTableName));
        $this->assertEquals($anotherTableName, $this->uut->getMainTable());
    }

    public function testGetSelectCached()
    {
        $this->selectMock
            ->expects($this->never())
            ->method('getPart');

        $this->assertInstanceOf(Select::class, $this->uut->getSelect());
    }

    /**
     * @dataProvider getSelectDataProvider
     */
    public function testGetSelect($idFieldNameRet, $getPartRet, $expected)
    {
        if (is_callable($idFieldNameRet['column_alias'])) {
            $idFieldNameRet['column_alias'] = $idFieldNameRet['column_alias']($this);
        }
        if (is_callable($getPartRet[0][1])) {
            $getPartRet[0][1] = $getPartRet[0][1]($this);
        }
        if (is_callable($expected[0][1]['column_alias'])) {
            $expected[0][1]['column_alias'] = $expected[0][1]['column_alias']($this);
        }
        if (is_callable($expected['alias'][1])) {
            $expected['alias'][1] = $expected['alias'][1]($this);
        }

        $this->resourceMock
            ->expects($this->any())
            ->method('getIdFieldName')
            ->willReturn($idFieldNameRet);

        $this->uut->removeAllFieldsFromSelect();

        $this->selectMock
            ->expects($this->any())
            ->method('getPart')
            ->willReturn($getPartRet);

        $this->selectMock
            ->expects($this->once())
            ->method('setPart')
            ->with(Select::COLUMNS, $expected);

        $this->assertInstanceOf(Select::class, $this->uut->getSelect());
    }

    /**
     * @return array
     */
    public static function getSelectDataProvider(): array
    {
        $columnMock = static fn (self $testCase) => $testCase->getZendDbExprPartialMock();

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

    public function getZendDbExprPartialMock()
    {
        return $this->createPartialMock(\Zend_Db_Expr::class, ['__toString']);
    }

    /**
     * @dataProvider addFieldToSelectDataProvider
     */
    public function testAddFieldToSelect($field, $alias, $expectedFieldsToSelect)
    {
        $this->assertInstanceOf(Uut::class, $this->uut->addFieldToSelect($field, $alias));
        $this->assertEquals($expectedFieldsToSelect, $this->uut->getFieldsToSelect());
        $this->assertTrue($this->uut->wereFieldsToSelectChanged());
    }

    /**
     * @return array
     */
    public static function addFieldToSelectDataProvider()
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
        $this->assertInstanceOf(Uut::class, $this->uut->addExpressionFieldToSelect($alias, $expression, $fields));
    }

    /**
     * @return array
     */
    public static function addExpressionFieldToSelectDataProvider()
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
        $this->assertInstanceOf(Uut::class, $this->uut->removeFieldFromSelect($field, $isAlias));
        $this->assertEquals($expectedFieldsToSelect, $this->uut->getFieldsToSelect());
        $this->assertEquals($expectedWereFieldsToSelectChanged, $this->uut->wereFieldsToSelectChanged());
    }

    /**
     * @return array
     */
    public static function removeFieldFromSelectDataProvider()
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
            ->willReturn('id_field');

        $this->uut->setFieldsToSelect(['alias' => 'field']);
        $this->assertInstanceOf(Uut::class, $this->uut->removeAllFieldsFromSelect());
        $this->assertTrue($this->uut->wereFieldsToSelectChanged());
        $this->assertEquals(['id_field'], $this->uut->getFieldsToSelect());
    }

    public function testSetModelNotString()
    {
        $this->assertInstanceOf(Uut::class, $this->uut->setModel(1));
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
        $this->assertInstanceOf(Uut::class, $this->uut->setModel(DataObject::class));
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
            ->willReturn(self::TABLE_NAME);

        $this->assertEquals(self::TABLE_NAME, $this->uut->getTable(''));
    }

    /**
     * @dataProvider joinDataProvider
     */
    public function testJoin($table, $cond, $cols, $expected)
    {
        $this->assertInstanceOf(Uut::class, $this->uut->join($table, $cond, $cols));
        $this->assertEquals($expected, $this->uut->getJoinedTables());
    }

    /**
     * @return array
     */
    public static function joinDataProvider()
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

        $this->assertInstanceOf(Uut::class, $this->uut->resetItemsDataChanged());

        foreach ($this->uut->getItems() as $item) {
            $this->assertFalse($item->hasDataChanges());
        }
    }

    public function testSave()
    {
        for ($i = 0; $i < 3; $i++) {
            /** @var DataObject|MockObject $item */
            $item = $this->getMockBuilder(DataObject::class)
                ->addMethods(['save'])
                ->disableOriginalConstructor()
                ->getMock();
            $item->expects($this->once())->method('save');
            $this->uut->addItem($item);
        }

        $this->assertInstanceOf(Uut::class, $this->uut->save());
    }
}
