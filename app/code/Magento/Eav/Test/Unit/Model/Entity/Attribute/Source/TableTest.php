<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Test\Unit\Model\Entity\Attribute\Source;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class TableTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Eav\Model\Entity\Attribute\Source\Table
     */
    private $model;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory
     *      | \PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionFactory;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    private $attrOptionFactory;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->collectionFactory = $this->getMock(
            'Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory',
            [
                'create',
                'setPositionOrder',
                'setAttributeFilter',
                'addFieldToFilter',
                'setStoreFilter',
                'load',
                'toOptionArray'
            ],
            [],
            '',
            false
        );

        $this->attrOptionFactory = $this->getMockBuilder(
            'Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory'
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->model = $objectManager->getObject(
            'Magento\Eav\Model\Entity\Attribute\Source\Table',
            [
                'attrOptionCollectionFactory' => $this->collectionFactory,
                'attrOptionFactory' => $this->attrOptionFactory
            ]
        );
    }

    public function testGetFlatColumns()
    {
        $abstractFrontendMock = $this->getMock(
            'Magento\Eav\Model\Entity\Attribute\Frontend\AbstractFrontend',
            [],
            [],
            '',
            false
        );

        $abstractAttributeMock = $this->getMock(
            '\Magento\Eav\Model\Entity\Attribute\AbstractAttribute',
            ['getFrontend', 'getAttributeCode', '__wakeup'],
            [],
            '',
            false
        );

        $abstractAttributeMock->expects(
            $this->any()
        )->method(
            'getFrontend'
        )->will(
            $this->returnValue($abstractFrontendMock)
        );

        $abstractAttributeMock->expects($this->any())->method('getAttributeCode')->will($this->returnValue('code'));

        $this->model->setAttribute($abstractAttributeMock);

        $flatColumns = $this->model->getFlatColumns();

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
     * @dataProvider specificOptionsProvider
     * @param array $optionIds
     * @param bool $withEmpty
     */
    public function testGetSpecificOptions($optionIds, $withEmpty)
    {
        $attributeId = 1;
        $storeId = 5;
        $options = [['label' => 'The label', 'value' => 'A value']];

        $attribute = $this->getMock(
            'Magento\Eav\Model\Entity\Attribute\AbstractAttribute',
            ['getId', 'getStoreId', 'getIsRequired', '__wakeup'],
            [],
            '',
            false
        );
        $attribute->expects($this->once())
            ->method('getId')
            ->willReturn($attributeId);
        $attribute->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);
        $attribute->expects($this->any())
            ->method('getIsRequired')
            ->willReturn(false);

        $this->model->setAttribute($attribute);

        $this->collectionFactory->expects($this->once())
            ->method('create')
            ->willReturnSelf();
        $this->collectionFactory->expects($this->once())
            ->method('setPositionOrder')
            ->willReturnSelf();
        $this->collectionFactory->expects($this->once())
            ->method('setAttributeFilter')
            ->with($attributeId)
            ->willReturnSelf();
        $this->collectionFactory->expects($this->once())
            ->method('addFieldToFilter')
            ->with('main_table.option_id', ['in' => $optionIds])
            ->willReturnSelf();
        $this->collectionFactory->expects($this->once())
            ->method('setStoreFilter')
            ->with($storeId)
            ->willReturnSelf();
        $this->collectionFactory->expects($this->once())
            ->method('load')
            ->willReturnSelf();
        $this->collectionFactory->expects($this->once())
            ->method('toOptionArray')
            ->willReturn($options);

        if ($withEmpty) {
            array_unshift($options, ['label' => ' ', 'value' => '']);
        }

        $this->assertEquals($options, $this->model->getSpecificOptions($optionIds, $withEmpty));
    }

    public function specificOptionsProvider()
    {
        return [
            [['1', '2'], true],
            [[1, 2], false]
        ];
    }

    /**
     * @dataProvider getOptionTextProvider
     * @param array $optionsIds
     * @param array|string $value
     * @param array $options
     * @param array|string $expectedResult
     */
    public function testGetOptionText($optionsIds, $value, $options, $expectedResult)
    {
        $attributeId = 1;
        $storeId = 5;
        $attribute = $this->getMock(
            'Magento\Eav\Model\Entity\Attribute\AbstractAttribute',
            ['getId', 'getStoreId', '__wakeup'],
            [],
            '',
            false
        );
        $attribute->expects($this->once())
            ->method('getId')
            ->willReturn($attributeId);
        $attribute->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);

        $this->model->setAttribute($attribute);

        $this->collectionFactory->expects($this->once())
            ->method('create')
            ->willReturnSelf();
        $this->collectionFactory->expects($this->once())
            ->method('setPositionOrder')
            ->willReturnSelf();
        $this->collectionFactory->expects($this->once())
            ->method('setAttributeFilter')
            ->with($attributeId)
            ->willReturnSelf();
        $this->collectionFactory->expects($this->once())
            ->method('addFieldToFilter')
            ->with('main_table.option_id', ['in' => $optionsIds])
            ->willReturnSelf();
        $this->collectionFactory->expects($this->once())
            ->method('setStoreFilter')
            ->with($storeId)
            ->willReturnSelf();
        $this->collectionFactory->expects($this->once())
            ->method('load')
            ->willReturnSelf();
        $this->collectionFactory->expects($this->once())
            ->method('toOptionArray')
            ->willReturn($options);

        $this->assertEquals($expectedResult, $this->model->getOptionText($value));
    }

    public function getOptionTextProvider()
    {
        return [
            [
                ['1', '2'],
                '1,2',
                [['label' => 'test label 1', 'value' => '1'], ['label' => 'test label 2', 'value' => '1']],
                ['test label 1', 'test label 2'],
            ],
            ['1', '1', [['label' => 'test label', 'value' => '1']], 'test label'],
            ['5', '5', [['label' => 'test label', 'value' => '5']], 'test label']
        ];
    }

    public function testAddValueSortToCollection()
    {
        $attributeCode = 'attribute_code';
        $dir = \Magento\Framework\DB\Select::SQL_ASC;
        $collection = $this->getMockBuilder('Magento\Eav\Model\Entity\Collection\AbstractCollection')
            ->setMethods([ 'getSelect', 'getStoreId'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $attribute = $this->getMockBuilder('Magento\Eav\Model\Entity\Attribute\AbstractAttribute')
            ->setMethods(['getAttributeCode', 'getEntity', 'getBackend', 'getId'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $attribute->expects($this->any())->method('getAttributeCode')->willReturn($attributeCode);
        $entity = $this->getMockBuilder('Magento\Eav\Model\Entity\AbstractEntity')
            ->setMethods(['getLinkField'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $attribute->expects($this->once())->method('getEntity')->willReturn($entity);
        $entity->expects($this->once())->method('getLinkField')->willReturn('entity_id');
        $select = $this->getMockBuilder('Magento\Framework\DB\Select')
            ->setMethods(['joinLeft', 'getConnection', 'order'])
            ->disableOriginalConstructor()
            ->getMock();
        $collection->expects($this->any())->method('getSelect')->willReturn($select);
        $select->expects($this->any())->method('joinLeft')->willReturnSelf();
        $backend = $this->getMockBuilder('Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend')
            ->setMethods(['getTable'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $attribute->expects($this->any())->method('getBackend')->willReturn($backend);
        $backend->expects($this->any())->method('getTable')->willReturn('table_name');
        $attribute->expects($this->any())->method('getId')->willReturn(1);
        $collection->expects($this->once())->method('getStoreId')->willReturn(1);
        $connection = $this->getMockBuilder('Magento\Framework\DB\Adapter\AdapterInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $expr = $this->getMockBuilder('Zend_Db_Expr')
            ->disableOriginalConstructor()
            ->getMock();
        $connection->expects($this->once())->method('getCheckSql')->willReturn($expr);
        $select->expects($this->once())->method('getConnection')->willReturn($connection);
        $attrOption = $this->getMockBuilder('Magento\Eav\Model\ResourceModel\Entity\Attribute\Option')
            ->disableOriginalConstructor()
            ->getMock();
        $this->attrOptionFactory->expects($this->once())->method('create')->willReturn($attrOption);
        $attrOption->expects($this->once())->method('addOptionValueToCollection')->with($collection, $attribute, $expr)
            ->willReturnSelf();
        $select->expects($this->once())->method('order')->with("{$attributeCode} {$dir}");

        $this->model->setAttribute($attribute);
        $this->assertEquals($this->model, $this->model->addValueSortToCollection($collection, $dir));
    }
}
