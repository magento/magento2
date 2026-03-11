<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Eav\Test\Unit\Model\Entity\Attribute\Source;

use Magento\Eav\Model\Entity\AbstractEntity;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
use Magento\Eav\Model\Entity\Attribute\Frontend\AbstractFrontend;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Eav\Model\Entity\Attribute\Source\Table;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection as AttributeOptionCollection;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TableTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var Table
     */
    private $model;

    /**
     * @var CollectionFactory|MockObject
     */
    private $collectionFactory;

    /**
     * @var OptionFactory|MockObject
     */
    private $attrOptionFactory;

    /**
     * @var AbstractSource|MockObject
     */
    private $sourceMock;

    /**
     * @var AbstractAttribute|MockObject
     */
    private $abstractAttributeMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var StoreInterface|MockObject
     */
    private $storeMock;

    /**
     * @var AttributeOptionCollection|MockObject
     */
    private $attributeOptionCollectionMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->collectionFactory = $this->createPartialMockWithReflection(
            CollectionFactory::class,
            [
                'create',
                'setPositionOrder',
                'setAttributeFilter',
                'addFieldToFilter',
                'setStoreFilter',
                'load',
                'toOptionArray'
            ]
        );

        $this->attributeOptionCollectionMock = $this->createPartialMockWithReflection(
            AttributeOptionCollection::class,
            ['toOptionArray']
        );

        $this->attrOptionFactory = $this->createPartialMock(
            OptionFactory::class,
            ['create']
        );

        $this->sourceMock = $this->createMock(AbstractSource::class);

        $this->abstractAttributeMock = $this->createPartialMockWithReflection(
            AbstractAttribute::class,
            [
                'getStoreId',
                'getFrontend',
                'getAttributeCode',
                'getEntity',
                'getId',
                'getBackend',
                'getIsRequired',
                'setPositionOrder'
            ]
        );

        $this->model = $objectManager->getObject(
            Table::class,
            [
                'attrOptionCollectionFactory' => $this->collectionFactory,
                'attrOptionFactory' => $this->attrOptionFactory
            ]
        );
        $this->model->setAttribute($this->abstractAttributeMock);

        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->storeMock = $this->createMock(StoreInterface::class);

        $objectManager->setBackwardCompatibleProperty(
            $this->model,
            'storeManager',
            $this->storeManagerMock
        );
    }

    public function testGetFlatColumns()
    {
        $abstractFrontendMock = $this->createMock(AbstractFrontend::class);

        $this->abstractAttributeMock->expects($this->any())->method('getFrontend')->willReturn(($abstractFrontendMock));
        $this->abstractAttributeMock->expects($this->any())->method('getAttributeCode')->willReturn('code');

        $flatColumns = $this->model->getFlatColumns();

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
     * @param array $optionIds
     * @param bool $withEmpty
     */
    #[DataProvider('specificOptionsProvider')]
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

    /**
     * @return array
     */
    public static function specificOptionsProvider()
    {
        return [
            [['1', '2'], true],
            [[1, 2], false]
        ];
    }

    /**
     * @param array $optionsIds
     * @param array|string $value
     * @param array $options
     * @param array|string $expectedResult
     */
    #[DataProvider('getOptionTextProvider')]
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

    /**
     * @return array
     */
    public static function getOptionTextProvider()
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
        $dir = Select::SQL_ASC;
        $collection = $this->createPartialMockWithReflection(
            AbstractCollection::class,
            ['getStoreId', 'getSelect']
        );

        $this->abstractAttributeMock->expects($this->any())->method('getAttributeCode')->willReturn($attributeCode);
        $entity = $this->createPartialMockWithReflection(
            AbstractEntity::class,
            ['getLinkField']
        );
        $this->abstractAttributeMock->expects($this->once())->method('getEntity')->willReturn($entity);
        $entity->expects($this->once())->method('getLinkField')->willReturn('entity_id');
        $select = $this->createPartialMockWithReflection(
            Select::class,
            ['joinLeft', 'getConnection', 'order']
        );
        $collection->expects($this->any())->method('getSelect')->willReturn($select);
        $select->expects($this->any())->method('joinLeft')->willReturnSelf();
        $backend = $this->createPartialMockWithReflection(
            AbstractBackend::class,
            ['getTable']
        );
        $this->abstractAttributeMock->expects($this->any())->method('getBackend')->willReturn($backend);
        $backend->expects($this->any())->method('getTable')->willReturn('table_name');
        $this->abstractAttributeMock->expects($this->any())->method('getId')->willReturn(1);
        $collection->expects($this->once())->method('getStoreId')->willReturn(1);
        $connection = $this->createMock(AdapterInterface::class);
        $expr = $this->createMock(\Zend_Db_Expr::class);
        $connection->expects($this->once())->method('getCheckSql')->willReturn($expr);
        $select->expects($this->once())->method('getConnection')->willReturn($connection);
        $attrOption = $this->createPartialMock(
            Option::class,
            ['addOptionValueToCollection', 'addOptionToCollection']
        );
        $this->attrOptionFactory->expects($this->once())->method('create')->willReturn($attrOption);
        $attrOption->expects($this->once())->method('addOptionValueToCollection')
            ->with($collection, $this->abstractAttributeMock, $expr)
            ->willReturnSelf();
        $attrOption->expects($this->once())->method('addOptionToCollection')
            ->with($collection, $this->abstractAttributeMock, $expr)
            ->willReturnSelf();
        $select->expects($this->once())->method('order')->with("{$attributeCode}_order {$dir}");

        $this->assertEquals($this->model, $this->model->addValueSortToCollection($collection, $dir));
    }

    /**
     * @param bool $withEmpty
     * @param bool $defaultValues
     * @param array $options
     * @param array $optionsDefault
     * @param array $expectedResult
     */
    #[DataProvider('getAllOptionsDataProvider')]
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
    public static function getAllOptionsDataProvider()
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
            ],
            [
                true,
                true,
                [['value' => '16', 'label' => 'default sv black'], ['value' => '17', 'label' => 'default sv white']],
                [['value' => '16', 'label' => 'black'], ['value' => '17', 'label' => 'white']],
                [
                    ['label' => ' ', 'value' => ''],
                    ['value' => '16', 'label' => 'black'],
                    ['value' => '17', 'label' => 'white']
                ]
            ]
        ];
    }
}
