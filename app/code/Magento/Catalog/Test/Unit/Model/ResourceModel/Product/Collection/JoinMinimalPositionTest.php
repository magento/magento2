<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Product\Collection;

use Magento\Catalog\Model\Indexer\Category\Product\TableMaintainer;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\Collection\JoinMinimalPosition;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for JoinMinimalPosition
 */
class JoinMinimalPositionTest extends TestCase
{
    /**
     * @var TableMaintainer|MockObject
     */
    private $tableMaintainer;

    /**
     * @var JoinMinimalPosition
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->tableMaintainer = $this->createMock(TableMaintainer::class);
        $this->model = new JoinMinimalPosition(
            $this->tableMaintainer
        );
    }

    /**
     * Test that correct SQL is generated
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Db_Select_Exception
     */
    public function testExecute(): void
    {
        $expectedColumns = [
            [
                'e',
                '*',
                null
            ],
            [
                'at_status',
                'value_id',
                'status'
            ],
            [
                'e',
                'visibility',
                null
            ],
            [
                'e',
                new \Zend_Db_Expr('LEAST(IFNULL(cat_index_3.position, ~0), IFNULL(cat_index_5.position, ~0))'),
                'cat_index_position'
            ],
        ];
        $expectedFromParts = [
            'e' => [
                'joinType' => 'from',
                'schema' => null,
                'tableName' => 'catalog_product_entity',
                'joinCondition' => null,
            ],
            'cat_index_3' => [
                'joinType' => 'left join',
                'schema' => null,
                'tableName' => null,
                'joinCondition' => 'cat_index_3.product_id=e.entity_id' .
                    ' AND cat_index_3.store_id=1' .
                    ' AND cat_index_3.category_id=3',
            ],
            'cat_index_5' => [
                'joinType' => 'left join',
                'schema' => null,
                'tableName' => null,
                'joinCondition' => 'cat_index_5.product_id=e.entity_id' .
                    ' AND cat_index_5.store_id=1' .
                    ' AND cat_index_5.category_id=5',
            ]
        ];
        $categoryIds = [3, 5];
        $collection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getConnection', 'getSelect', 'getStoreId'])
            ->getMockForAbstractClass();
        $connection = $this->getMockBuilder(Mysql::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['_connect'])
            ->getMockForAbstractClass();
        $select = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $select->reset();
        $select->from(['e' => 'catalog_product_entity']);
        $select->columns(['cat_index_position' => 'position']);
        $select->columns(['status' => 'at_status.value_id']);
        $select->columns(['visibility']);

        $collection->addStaticField('entity_id');
        $collection->method('getConnection')
            ->willReturn($connection);
        $collection->method('getSelect')
            ->willReturn($select);
        $collection->method('getStoreId')
            ->willReturn(1);
        $this->model->execute($collection, $categoryIds);
        $this->assertEquals(
            $expectedFromParts,
            $select->getPart(Select::FROM)
        );
        $this->assertEquals(
            $expectedColumns,
            $select->getPart(Select::COLUMNS)
        );
    }
}
