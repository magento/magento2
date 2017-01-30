<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Eav\Test\Unit\Model\Entity\Attribute\Source;

use Magento\Eav\Model\Entity\AbstractEntity;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class BooleanTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Eav\Model\Entity\Attribute\Source\Boolean
     */
    protected $_model;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->_model = $objectManager->getObject('Magento\Eav\Model\Entity\Attribute\Source\Boolean');
    }

    public function testGetFlatColumns()
    {
        $abstractAttributeMock = $this->getMock(
            '\Magento\Eav\Model\Entity\Attribute\AbstractAttribute',
            ['getAttributeCode', '__wakeup'],
            [],
            '',
            false
        );

        $abstractAttributeMock->expects($this->any())->method('getAttributeCode')->will($this->returnValue('code'));

        $this->_model->setAttribute($abstractAttributeMock);

        $flatColumns = $this->_model->getFlatColumns();

        $this->assertTrue(is_array($flatColumns), 'FlatColumns must be an array value');
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
        $direction, $isScopeGlobal, $expectedJoinCondition, $expectedOrder
    ) {
        $attributeMock = $this->getAttributeMock();
        $attributeMock->expects($this->any())->method('isScopeGlobal')->will($this->returnValue($isScopeGlobal));

        $entity = $this->getMockBuilder(AbstractEntity::class)
            ->disableOriginalConstructor()
            ->setMethods(['getLinkField'])
            ->getMockForAbstractClass();
        $entity->expects($this->once())->method('getLinkField')->willReturn('entity_id');
        $attributeMock->expects($this->once())->method('getEntity')->willReturn($entity);

        $selectMock = $this->getMock('\Magento\Framework\DB\Select', [], [], '', false);

        $collectionMock = $this->getCollectionMock();
        $collectionMock->expects($this->any())->method('getSelect')->will($this->returnValue($selectMock));

        foreach ($expectedJoinCondition as $step => $data) {
            $selectMock->expects($this->at($step))->method('joinLeft')
                ->with($data['requisites'], $data['condition'], [])->will($this->returnSelf());
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
                        'condition' =>
                            "e.entity_id=code_t1.entity_id AND code_t1.attribute_id='123' AND code_t1.store_id='0'",
                    ],
                    1 => [
                        'requisites' => ['code_t2' => "table"],
                        'condition' =>
                            "e.entity_id=code_t2.entity_id AND code_t2.attribute_id='123' AND code_t2.store_id='12'",
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
                        'condition' =>
                            "e.entity_id=code_t1.entity_id AND code_t1.attribute_id='123' AND code_t1.store_id='0'",
                    ],
                    1 => [
                        'requisites' => ['code_t2' => "table"],
                        'condition' =>
                            "e.entity_id=code_t2.entity_id AND code_t2.attribute_id='123' AND code_t2.store_id='12'",
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
                        'condition' =>
                            "e.entity_id=code_t.entity_id AND code_t.attribute_id='123' AND code_t.store_id='0'",
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
                        'condition' =>
                            "e.entity_id=code_t.entity_id AND code_t.attribute_id='123' AND code_t.store_id='0'",
                    ],
                ],
                'expectedOrder' => 'code_t.value ASC',
            ],
        ];
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getCollectionMock()
    {
        $collectionMethods = ['getSelect', 'getStoreId', 'getConnection'];
        $collectionMock = $this->getMock(
            '\Magento\Eav\Model\Entity\Collection\AbstractCollection', $collectionMethods, [], '', false
        );

        $connectionMock = $this->getMock('\Magento\Framework\DB\Adapter\Pdo\Mysql', ['method'], [], '', false);

        $collectionMock->expects($this->any())->method('getConnection')->will($this->returnValue($connectionMock));
        $collectionMock->expects($this->any())->method('getStoreId')->will($this->returnValue('12'));

        return $collectionMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getAttributeMock()
    {
        $attributeMockMethods = ['getAttributeCode', 'getId', 'getBackend', 'isScopeGlobal', '__wakeup' , 'getEntity'];
        $attributeMock = $this->getMock(
            '\Magento\Eav\Model\Entity\Attribute\AbstractAttribute', $attributeMockMethods, [], '', false
        );
        $backendMock = $this->getMock('\Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend', [], [], '', false);

        $attributeMock->expects($this->any())->method('getAttributeCode')->will($this->returnValue('code'));
        $attributeMock->expects($this->any())->method('getId')->will($this->returnValue('123'));
        $attributeMock->expects($this->any())->method('getBackend')->will($this->returnValue($backendMock));
        $backendMock->expects($this->any())->method('getTable')->will($this->returnValue('table'));

        return $attributeMock;
    }
}
