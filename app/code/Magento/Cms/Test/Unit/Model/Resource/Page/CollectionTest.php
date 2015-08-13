<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Model\Resource\Page;

use Magento\Cms\Test\Unit\Model\Resource\AbstractCollectionTest;

class CollectionTest extends AbstractCollectionTest
{
    /**
     * @var \Magento\Cms\Model\Resource\Page\Collection
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

    public function testAddFieldToFilterStore()
    {
        $storeId = 1;

        $this->assertSame($this->collection, $this->collection->addFieldToFilter('store_id', $storeId));
    }

    public function testFieldToFilterStoreAdded()
    {
        $field = 'store';
        $value = ['in' => ['1']];
        $type = 'public';

        $dataObject = new \Magento\Framework\DataObject();
        $dataObject->setData('field', $field);
        $dataObject->setData('value', $value);
        $dataObject->setData('type', $type);

        $this->collection->addFilter($field, $value, $type);

        $this->assertEquals($dataObject, $this->collection->getFilter($field));
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

    public function testFieldToFilterAdded()
    {
        $field = 'is_active';
        $value = ['eq' => ['1']];
        $type = 'public';

        $dataObject = new \Magento\Framework\DataObject();
        $dataObject->setData('field', $field);
        $dataObject->setData('value', $value);
        $dataObject->setData('type', $type);

        $this->collection->addFilter($field, $value, $type);

        $this->assertEquals($dataObject, $this->collection->getFilter($field));
    }
}
