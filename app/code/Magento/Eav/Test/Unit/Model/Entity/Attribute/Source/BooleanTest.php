<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Eav\Test\Unit\Model\Entity\Attribute\Source;

use Magento\Eav\Model\Entity\AbstractEntity;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BooleanTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var Boolean
     */
    protected $_model;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->_model = $objectManager->getObject(Boolean::class);
    }

    /**
     * @return void
     */
    public function testGetFlatColumns(): void
    {
        $abstractAttributeMock = $this->createPartialMock(
            AbstractAttribute::class,
            ['getAttributeCode', '__wakeup']
        );

        $abstractAttributeMock->expects($this->any())->method('getAttributeCode')->willReturn('code');

        $this->_model->setAttribute($abstractAttributeMock);

        $flatColumns = $this->_model->getFlatColumns();

        $this->assertIsArray($flatColumns, 'FlatColumns must be an array value');
        $this->assertNotEmpty($flatColumns, 'FlatColumns must be not empty');
        foreach ($flatColumns as $result) {
            $this->assertArrayHasKey('unsigned', $result, 'FlatColumns must have "unsigned" column');
            $this->assertArrayHasKey('default', $result, 'FlatColumns must have "default" column');
            $this->assertArrayHasKey('extra', $result, 'FlatColumns must have "extra" column');
            $this->assertArrayHasKey('type', $result, 'FlatColumns must have "type" column');
            $this->assertArrayHasKey('nullable', $result, 'FlatColumns must have "nullable" column');
            $this->assertArrayHasKey('comment', $result, 'FlatColumns must have "comment" column');
            $this->assertArrayHasKey('length', $result, 'FlatColumns must have "length" column');
        }
    }

    /**
     * @param string $direction
     * @param bool $isScopeGlobal
     * @param array $expectedJoinCondition
     * @param string $expectedOrder
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return void
     * @covers \Magento\Eav\Model\Entity\Attribute\Source\Boolean::addValueSortToCollection
     */
    #[DataProvider('addValueSortToCollectionDataProvider')]
    public function testAddValueSortToCollection(
        $direction,
        $isScopeGlobal,
        $expectedJoinCondition,
        $expectedOrder
    ): void {
        $attributeMock = $this->getAttributeMock();
        $attributeMock->expects($this->any())->method('isScopeGlobal')->willReturn($isScopeGlobal);

        $entity = $this->createPartialMock(
            AbstractEntity::class,
            ['getLinkField']
        );
        $entity->expects($this->once())->method('getLinkField')->willReturn('entity_id');
        $attributeMock->expects($this->once())->method('getEntity')->willReturn($entity);

        $selectMock = $this->createMock(Select::class);
        
        // Track joined tables to allow production code to build order expression
        $joinedTables = [];
        $selectMock
            ->method('joinLeft')
            ->willReturnCallback(function ($table, $condition, $cols) use ($selectMock, &$joinedTables) {
                $joinedTables = array_merge($joinedTables, array_keys($table));
                return $selectMock;
            });
        
        $selectMock
            ->method('getPart')
            ->with(Select::FROM)
            ->willReturnCallback(function () use (&$joinedTables) {
                $from = [];
                foreach ($joinedTables as $alias) {
                    $from[$alias] = ['joinType' => Select::LEFT_JOIN];
                }
                return $from;
            });

        $collectionMock = $this->getCollectionMock();
        $collectionMock->expects($this->any())->method('getSelect')->willReturn($selectMock);

        $selectMock->expects($this->once())->method('order')->with($expectedOrder);

        $this->_model->setAttribute($attributeMock);
        $this->_model->addValueSortToCollection($collectionMock, $direction);
    }

    /**
     * @return array
     */
    public static function addValueSortToCollectionDataProvider(): array
    {
        return  [
            [
                'direction' => 'ASC',
                'isScopeGlobal' => false,
                'expectedJoinCondition' => [
                    0 => [
                        'requisites' => ['code_t1' => "table"],
                        'condition' => "e.entity_id=code_t1.entity_id AND code_t1.attribute_id='123'"
                            . " AND code_t1.store_id='0'"
                    ],
                    1 => [
                        'requisites' => ['code_t2' => "table"],
                        'condition' => "e.entity_id=code_t2.entity_id AND code_t2.attribute_id='123'"
                            . " AND code_t2.store_id='12'"
                    ],
                ],
                'expectedOrder' => 'IF(code_t2.value_id > 0, code_t2.value, code_t1.value) ASC'
            ],
            [
                'direction' => 'DESC',
                'isScopeGlobal' => false,
                'expectedJoinCondition' => [
                    0 => [
                        'requisites' => ['code_t1' => "table"],
                        'condition' => "e.entity_id=code_t1.entity_id AND code_t1.attribute_id='123'"
                            . " AND code_t1.store_id='0'"
                    ],
                    1 => [
                        'requisites' => ['code_t2' => "table"],
                        'condition' => "e.entity_id=code_t2.entity_id AND code_t2.attribute_id='123'"
                            . " AND code_t2.store_id='12'"
                    ]
                ],
                'expectedOrder' => 'IF(code_t2.value_id > 0, code_t2.value, code_t1.value) DESC'
            ],
            [
                'direction' => 'DESC',
                'isScopeGlobal' => true,
                'expectedJoinCondition' => [
                    0 => [
                        'requisites' => ['code_t' => "table"],
                        'condition' => "e.entity_id=code_t.entity_id AND code_t.attribute_id='123'"
                            . " AND code_t.store_id='0'"
                    ]
                ],
                'expectedOrder' => 'code_t.value DESC'
            ],
            [
                'direction' => 'ASC',
                'isScopeGlobal' => true,
                'expectedJoinCondition' => [
                    0 => [
                        'requisites' => ['code_t' => "table"],
                        'condition' => "e.entity_id=code_t.entity_id AND code_t.attribute_id='123'"
                            . " AND code_t.store_id='0'"
                    ]
                ],
                'expectedOrder' => 'code_t.value ASC'
            ]
        ];
    }

    /**
     * @return MockObject
     */
    protected function getCollectionMock(): MockObject
    {
        $collectionMock = $this->createPartialMockWithReflection(
            AbstractCollection::class,
            ['getStoreId', 'getSelect', 'getConnection']
        );

        $connectionMock = $this->createMock(Mysql::class);
        
        // Configure getCheckSql to return the IF expression expected by production code
        $connectionMock->expects($this->any())
            ->method('getCheckSql')
            ->willReturnCallback(function ($condition, $trueValue, $falseValue) {
                return "IF($condition, $trueValue, $falseValue)";
            });

        $collectionMock->expects($this->any())->method('getConnection')->willReturn($connectionMock);
        $collectionMock->expects($this->any())->method('getStoreId')->willReturn('12');

        return $collectionMock;
    }

    /**
     * @return MockObject
     */
    protected function getAttributeMock(): MockObject
    {
        $attributeMock = $this->createPartialMockWithReflection(
            AbstractAttribute::class,
            ['isScopeGlobal', 'getAttributeCode', 'getId', 'getBackend', '__wakeup', 'getEntity']
        );
        $backendMock = $this->createMock(AbstractBackend::class);

        $attributeMock->expects($this->any())->method('getAttributeCode')->willReturn('code');
        $attributeMock->expects($this->any())->method('getId')->willReturn('123');
        $attributeMock->expects($this->any())->method('getBackend')->willReturn($backendMock);
        $backendMock->expects($this->any())->method('getTable')->willReturn('table');

        return $attributeMock;
    }
}
