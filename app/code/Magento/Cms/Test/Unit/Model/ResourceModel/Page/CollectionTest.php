<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Model\ResourceModel\Page;

use Magento\Cms\Test\Unit\Model\ResourceModel\AbstractCollectionTest;
use Magento\Framework\DataObject;

class CollectionTest extends AbstractCollectionTest
{
    /**
     * @var \Magento\Cms\Model\ResourceModel\Page\Collection
     */
    protected $collection;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadataPoolMock;

    protected function setUp()
    {
        parent::setUp();

        $this->storeManagerMock  = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->getMockForAbstractClass();

        $this->metadataPoolMock  = $this->getMockBuilder(\Magento\Framework\EntityManager\MetadataPool::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->collection = $this->objectManager->getObject(
            \Magento\Cms\Model\ResourceModel\Page\Collection::class,
            [
                'resource' => $this->resource,
                'connection' => $this->connection,
                'storeManager' => $this->storeManagerMock,
                'metadataPool' => $this->metadataPoolMock,
            ]
        );
    }

    public function testAddFieldToFilterStore()
    {
        $storeId = 1;

        $expectedFilter = new DataObject(
            [
                'field' => 'store',
                'value' => ['in' => [1]],
                'type' => 'public'
            ]
        );

        $this->assertSame($this->collection, $this->collection->addFieldToFilter('store_id', $storeId));
        // addition call to make sure that correct value was set to filter
        $this->assertEquals($expectedFilter, $this->collection->getFilter('store'));
    }

    public function testAddFieldToFilter()
    {
        $field = 'title';
        $value = 'test_filter';
        $searchSql = 'sql query';

        $this->connection->expects($this->any())->method('quoteIdentifier')->willReturn($searchSql);
        $this->connection->expects($this->any())->method('prepareSqlCondition')->willReturn($searchSql);

        $this->select->expects($this->once())
            ->method('where')
            ->with($searchSql, null, \Magento\Framework\DB\Select::TYPE_CONDITION);

        $this->assertSame($this->collection, $this->collection->addFieldToFilter($field, $value));
    }

    /**
     * @param \Magento\Framework\DataObject $item
     * @param array $storesData
     * @dataProvider getItemsDataProvider
     * @throws \Exception
     */
    public function testAfterLoad($item, $storesData)
    {
        $linkField = 'row_id';

        $expectedResult = [];
        foreach ($storesData as $storeData) {
            $expectedResult[$storeData[$linkField]][] = $storeData['store_id'];
        }

        $entityMetadataMock = $this->getMockBuilder(\Magento\Framework\EntityManager\EntityMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();
        $entityMetadataMock->expects($this->any())->method('getLinkField')->willReturn($linkField);
        $this->metadataPoolMock->expects($this->any())->method('getMetadata')->willReturn($entityMetadataMock);

        $this->select->expects($this->any())->method('from')->willReturnSelf();
        $this->connection->expects($this->any())->method('fetchAll')->willReturn($storesData);

        $storeDataMock = $this->getMockBuilder(
            \Magento\Store\Api\Data\StoreInterface::class
        )->getMockForAbstractClass();
        $storeDataMock->expects($this->any())->method('getId')->willReturn(current($expectedResult[$item->getId()]));
        $storeDataMock->expects($this->any())->method('getCode')->willReturn('some_code');
        $this->storeManagerMock->expects($this->any())->method('getStores')->willReturn([$storeDataMock]);
        $this->storeManagerMock->expects($this->any())->method('getStore')->willReturn($storeDataMock);

        $this->collection->addItem($item);

        $this->assertEmpty($item->getStoreId());
        $this->collection->load();
        $this->assertEquals($expectedResult[$item->getId()], $item->getStoreId());
    }

    /**
     * @return array
     */
    public function getItemsDataProvider()
    {
        return [
            [
                new \Magento\Framework\DataObject(['id' => 1, 'row_id' => 1]),
                [
                    ['row_id' => 1, 'store_id' => \Magento\Store\Model\Store::DEFAULT_STORE_ID],
                ],
            ],
            [
                new \Magento\Framework\DataObject(['id' => 2, 'row_id' => 2]),
                [
                    ['row_id' => 2, 'store_id' => 1],
                    ['row_id' => 2, 'store_id' => 2],
                ],
            ],
        ];
    }
}
