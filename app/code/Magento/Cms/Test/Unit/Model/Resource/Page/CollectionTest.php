<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Model\Resource\Page;

use \Magento\Cms\Test\Unit\Model\Resource\AbstractCollectionTest;

class CollectionTest extends AbstractCollectionTest
{
    /**
     * @var \Magento\Cms\Model\Resource\Page\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $collection;

    protected function setUp()
    {
        parent::setUp();

        $this->collection = $this->objectManager->getObject(
            'Magento\Cms\Model\Resource\Page\Collection',
            [
                'resource' => $this->resource,
                'connection' => $this->connection
            ]
        );
    }

    public function testAddFieldToFilterSore()
    {
        $storeId = 1;

        $this->assertEquals($this->collection, $this->collection->addFieldToFilter('store_id', $storeId));
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
            ->with(
                $this->equalTo($searchSql),
                $this->equalTo(null),
                $this->equalTo(\Magento\Framework\DB\Select::TYPE_CONDITION)
            );

        $this->assertEquals($this->collection, $this->collection->addFieldToFilter($field, $value));
    }
}
