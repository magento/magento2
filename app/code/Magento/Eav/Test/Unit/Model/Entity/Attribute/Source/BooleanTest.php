<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Test\Unit\Model\Entity\Attribute\Source;

use Magento\Eav\Model\Entity\AbstractEntity;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class BooleanTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Eav\Model\Entity\Attribute\Source\Boolean
     */
    protected $_model;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->_model = $objectManager->getObject(\Magento\Eav\Model\Entity\Attribute\Source\Boolean::class);
    }

    public function testGetFlatColumns()
    {
        $abstractAttributeMock = $this->createPartialMock(
            \Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class,
            ['getAttributeCode', '__wakeup']
        );

        $abstractAttributeMock->expects($this->any())->method('getAttributeCode')->willReturn('code');

        $this->_model->setAttribute($abstractAttributeMock);

        $flatColumns = $this->_model->getFlatColumns();

        $this->assertIsArray($flatColumns, 'FlatColumns must be an array value');
        $this->assertTrue(!empty($flatColumns), 'FlatColumns must be not empty');
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
     * @covers \Magento\Eav\Model\Entity\Attribute\Source\Boolean::addValueSortToCollection
     *
     * @dataProvider addValueSortToCollectionDataProvider
     * @param string $direction
     * @param bool $isScopeGlobal
     * @param array $expectedJoinCondition
     * @param string $expectedOrder
     */
    public function testAddValueSortToCollection(
        $direction,
        $isScopeGlobal,
        $expectedJoinCondition,
        $expectedOrder
    ) {
        $attributeMock = $this->getAttributeMock();
        $attributeMock->expects($this->any())->method('isScopeGlobal')->willReturn($isScopeGlobal);

        $entity = $this->getMockBuilder(AbstractEntity::class)
            ->disableOriginalConstructor()
            ->setMethods(['getLinkField'])
            ->getMockForAbstractClass();
        $entity->expects($this->once())->method('getLinkField')->willReturn('entity_id');
        $attributeMock->expects($this->once())->method('getEntity')->willReturn($entity);

        $selectMock = $this->createMock(\Magento\Framework\DB\Select::class);

        $collectionMock = $this->getCollectionMock();
        $collectionMock->expects($this->any())->method('getSelect')->willReturn($selectMock);

        foreach ($expectedJoinCondition as $step => $data) {
            $selectMock->expects($this->at($step))->method('joinLeft')
                ->with($data['requisites'], $data['condition'], [])->willReturnSelf();
        }

        $selectMock->expects($this->once())->method('order')->with($expectedOrder);

        $this->_model->setAttribute($attributeMock);
        $this->_model->addValueSortToCollection($collectionMock, $direction);
    }

    /**
     * @return array
     */
    public function addValueSortToCollectionDataProvider()
    {
        return  [
            [
                'direction' => 'ASC',
                'isScopeGlobal' => false,
                'expectedJoinCondition' => [
                    0 => [
                        'requisites' => ['code_t1' => "table"],
                        'condition' => "e.entity_id=code_t1.entity_id AND code_t1.attribute_id='123'"
                            . " AND code_t1.store_id='0'",
                    ],
                    1 => [
                        'requisites' => ['code_t2' => "table"],
                        'condition' => "e.entity_id=code_t2.entity_id AND code_t2.attribute_id='123'"
                            . " AND code_t2.store_id='12'",
                    ],
                ],
                'expectedOrder' => 'IF(code_t2.value_id > 0, code_t2.value, code_t1.value) ASC',
            ],
            [
                'direction' => 'DESC',
                'isScopeGlobal' => false,
                'expectedJoinCondition' => [
                    0 => [
                        'requisites' => ['code_t1' => "table"],
                        'condition' => "e.entity_id=code_t1.entity_id AND code_t1.attribute_id='123'"
                            . " AND code_t1.store_id='0'",
                    ],
                    1 => [
                        'requisites' => ['code_t2' => "table"],
                        'condition' => "e.entity_id=code_t2.entity_id AND code_t2.attribute_id='123'"
                            . " AND code_t2.store_id='12'",
                    ],
                ],
                'expectedOrder' => 'IF(code_t2.value_id > 0, code_t2.value, code_t1.value) DESC',
            ],
            [
                'direction' => 'DESC',
                'isScopeGlobal' => true,
                'expectedJoinCondition' => [
                    0 => [
                        'requisites' => ['code_t' => "table"],
                        'condition' => "e.entity_id=code_t.entity_id AND code_t.attribute_id='123'"
                            . " AND code_t.store_id='0'",
                    ],
                ],
                'expectedOrder' => 'code_t.value DESC',
            ],
            [
                'direction' => 'ASC',
                'isScopeGlobal' => true,
                'expectedJoinCondition' => [
                    0 => [
                        'requisites' => ['code_t' => "table"],
                        'condition' => "e.entity_id=code_t.entity_id AND code_t.attribute_id='123'"
                            . " AND code_t.store_id='0'",
                    ],
                ],
                'expectedOrder' => 'code_t.value ASC',
            ],
        ];
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function getCollectionMock()
    {
        $collectionMethods = ['getSelect', 'getStoreId', 'getConnection'];
        $collectionMock = $this->createPartialMock(
            \Magento\Eav\Model\Entity\Collection\AbstractCollection::class,
            $collectionMethods
        );

        $connectionMock = $this->createPartialMock(\Magento\Framework\DB\Adapter\Pdo\Mysql::class, ['method']);

        $collectionMock->expects($this->any())->method('getConnection')->willReturn($connectionMock);
        $collectionMock->expects($this->any())->method('getStoreId')->willReturn('12');

        return $collectionMock;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function getAttributeMock()
    {
        $attributeMockMethods = ['getAttributeCode', 'getId', 'getBackend', 'isScopeGlobal', '__wakeup' , 'getEntity'];
        $attributeMock = $this->createPartialMock(
            \Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class,
            $attributeMockMethods
        );
        $backendMock = $this->createMock(\Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend::class);

        $attributeMock->expects($this->any())->method('getAttributeCode')->willReturn('code');
        $attributeMock->expects($this->any())->method('getId')->willReturn('123');
        $attributeMock->expects($this->any())->method('getBackend')->willReturn($backendMock);
        $backendMock->expects($this->any())->method('getTable')->willReturn('table');

        return $attributeMock;
    }
}
