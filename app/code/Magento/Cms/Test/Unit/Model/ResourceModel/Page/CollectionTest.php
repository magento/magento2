<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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

    protected function setUp()
    {
        parent::setUp();

        $this->collection = $this->objectManager->getObject(
            'Magento\Cms\Model\ResourceModel\Page\Collection',
            [
                'resource' => $this->resource,
                'connection' => $this->connection
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
}
