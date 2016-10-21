<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Test\Unit\Model\Entity\Attribute\Source;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection as AttributeOptionCollection;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
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

    /**
     * @var AbstractSource | \PHPUnit_Framework_MockObject_MockObject
     */
    private $sourceMock;

    /**
     * @var AbstractAttribute | \PHPUnit_Framework_MockObject_MockObject
     */
    private $abstractAttributeMock;

    /**
     * @var StoreManagerInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var StoreInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeMock;

    /**
     * @var AttributeOptionCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeOptionCollectionMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->collectionFactory = $this->getMock(
            \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory::class,
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

        $this->attributeOptionCollectionMock = $this->getMockBuilder(AttributeOptionCollection::class)
            ->setMethods(['toOptionArray'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->attrOptionFactory = $this->getMockBuilder(
            \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory::class
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->sourceMock = $this->getMockBuilder(AbstractSource::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->abstractAttributeMock = $this->getMockBuilder(AbstractAttribute::class)
            ->setMethods(
                [
                    'getFrontend', 'getAttributeCode', '__wakeup', 'getStoreId',
                    'getId', 'getIsRequired', 'getEntity', 'getBackend'
                ]
            )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->model = $objectManager->getObject(
            \Magento\Eav\Model\Entity\Attribute\Source\Table::class,
            [
                'attrOptionCollectionFactory' => $this->collectionFactory,
                'attrOptionFactory' => $this->attrOptionFactory
            ]
        );
        $this->model->setAttribute($this->abstractAttributeMock);

        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->storeMock = $this->getMockForAbstractClass(StoreInterface::class);

        $objectManager->setBackwardCompatibleProperty(
            $this->model,
            'storeManager',
            $this->storeManagerMock
        );
    }

    public function testGetFlatColumns()
    {
        $abstractFrontendMock = $this->getMock(
            \Magento\Eav\Model\Entity\Attribute\Frontend\AbstractFrontend::class,
            [],
            [],
            '',
            false
        );

        $this->abstractAttributeMock->expects($this->any())->method('getFrontend')->willReturn(($abstractFrontendMock));
        $this->abstractAttributeMock->expects($this->any())->method('getAttributeCode')->willReturn('code');

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

        $this->abstractAttributeMock->expects($this->once())
            ->method('getId')
            ->willReturn($attributeId);
        $this->abstractAttributeMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);
        $this->abstractAttributeMock->expects($this->any())
            ->method('getIsRequired')
            ->willReturn(false);

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

        $this->abstractAttributeMock->expects($this->once())
            ->method('getId')
            ->willReturn($attributeId);
        $this->abstractAttributeMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);

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
        $collection = $this->getMockBuilder(\Magento\Eav\Model\Entity\Collection\AbstractCollection::class)
            ->setMethods([ 'getSelect', 'getStoreId'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->abstractAttributeMock->expects($this->any())->method('getAttributeCode')->willReturn($attributeCode);
        $entity = $this->getMockBuilder(\Magento\Eav\Model\Entity\AbstractEntity::class)
            ->setMethods(['getLinkField'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->abstractAttributeMock->expects($this->once())->method('getEntity')->willReturn($entity);
        $entity->expects($this->once())->method('getLinkField')->willReturn('entity_id');
        $select = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->setMethods(['joinLeft', 'getConnection', 'order'])
            ->disableOriginalConstructor()
            ->getMock();
        $collection->expects($this->any())->method('getSelect')->willReturn($select);
        $select->expects($this->any())->method('joinLeft')->willReturnSelf();
        $backend = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend::class)
            ->setMethods(['getTable'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->abstractAttributeMock->expects($this->any())->method('getBackend')->willReturn($backend);
        $backend->expects($this->any())->method('getTable')->willReturn('table_name');
        $this->abstractAttributeMock->expects($this->any())->method('getId')->willReturn(1);
        $collection->expects($this->once())->method('getStoreId')->willReturn(1);
        $connection = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $expr = $this->getMockBuilder(\Zend_Db_Expr::class)
            ->disableOriginalConstructor()
            ->getMock();
        $connection->expects($this->once())->method('getCheckSql')->willReturn($expr);
        $select->expects($this->once())->method('getConnection')->willReturn($connection);
        $attrOption = $this->getMockBuilder(\Magento\Eav\Model\ResourceModel\Entity\Attribute\Option::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->attrOptionFactory->expects($this->once())->method('create')->willReturn($attrOption);
        $attrOption->expects($this->once())->method('addOptionValueToCollection')
            ->with($collection, $this->abstractAttributeMock, $expr)
            ->willReturnSelf();
        $select->expects($this->once())->method('order')->with("{$attributeCode} {$dir}");

        $this->assertEquals($this->model, $this->model->addValueSortToCollection($collection, $dir));
    }

    /**
     * @param bool $withEmpty
     * @param bool $defaultValues
     * @param array $options
     * @param array $optionsDefault
     * @param array $expectedResult
     * @dataProvider getAllOptionsDataProvider
     */
    public function testGetAllOptions(
        $withEmpty,
        $defaultValues,
        array $options,
        array $optionsDefault,
        array $expectedResult
    ) {
        $storeId = '1';
        $attributeId = '42';

        $this->abstractAttributeMock->expects($this->once())->method('getStoreId')->willReturn(null);

        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturn($this->storeMock);
        $this->storeMock->expects($this->once())->method('getId')->willReturn($storeId);

        $this->abstractAttributeMock->expects($this->once())->method('getId')->willReturn($attributeId);

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
            ->method('setStoreFilter')
            ->with($storeId)
            ->willReturnSelf();
        $this->collectionFactory->expects($this->once())
            ->method('load')
            ->willReturn($this->attributeOptionCollectionMock);
        $this->attributeOptionCollectionMock->expects($this->any())
            ->method('toOptionArray')
            ->willReturnMap(
                [
                    ['value', $options],
                    ['default_value', $optionsDefault]
                ]
            );

        $this->assertEquals($expectedResult, $this->model->getAllOptions($withEmpty, $defaultValues));
    }

    /**
     * @return array
     */
    public function getAllOptionsDataProvider()
    {
        return [
            [
                false,
                false,
                [['value' => '16', 'label' => 'black'], ['value' => '17', 'label' => 'white']],
                [['value' => '16', 'label' => 'blck'], ['value' => '17', 'label' => 'wht']],
                [['value' => '16', 'label' => 'black'], ['value' => '17', 'label' => 'white']]
            ],
            [
                false,
                true,
                [['value' => '16', 'label' => 'black'], ['value' => '17', 'label' => 'white']],
                [['value' => '16', 'label' => 'blck'], ['value' => '17', 'label' => 'wht']],
                [['value' => '16', 'label' => 'blck'], ['value' => '17', 'label' => 'wht']]
            ],
            [
                true,
                false,
                [['value' => '16', 'label' => 'black'], ['value' => '17', 'label' => 'white']],
                [['value' => '16', 'label' => 'blck'], ['value' => '17', 'label' => 'wht']],
                [
                    ['label' => ' ', 'value' => ''],
                    ['value' => '16', 'label' => 'black'],
                    ['value' => '17', 'label' => 'white']
                ]
            ]
        ];
    }
}
