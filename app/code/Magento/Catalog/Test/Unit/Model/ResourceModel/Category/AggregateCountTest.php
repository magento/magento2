<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Category;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel\Category\AggregateCount;
use Magento\Catalog\Model\ResourceModel\Category as ResourceCategory;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Aggregate count model test
 */
class AggregateCountTest extends TestCase
{

    /**
     * @var AggregateCount
     */
    protected $aggregateCount;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var Category|MockObject
     */
    protected $categoryMock;

    /**
     * @var  ResourceCategory|MockObject
     */
    protected $resourceCategoryMock;

    /**
     * @var AdapterInterface|MockObject
     */
    protected $connectionMock;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $this->categoryMock = $this->createMock(Category::class);
        $this->resourceCategoryMock = $this->createMock(ResourceCategory::class);
        $this->connectionMock = $this->createMock(AdapterInterface::class);
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->aggregateCount = $this->objectManagerHelper->getObject(AggregateCount::class);
    }

    /**
     * @return void
     */
    public function testProcessDelete(): void
    {
        $parentIds = 3;
        $table = 'catalog_category_entity';

        $this->categoryMock->expects($this->once())
            ->method('getResource')
            ->willReturn($this->resourceCategoryMock);
        $this->categoryMock->expects($this->once())
            ->method('getParentIds')
            ->willReturn($parentIds);
        $this->resourceCategoryMock->method('getEntityTable')->willReturn($table);
        $this->resourceCategoryMock->expects($this->once())
            ->method('getConnection')
            ->willReturn($this->connectionMock);
        $this->connectionMock->expects($this->once())
            ->method('fetchOne')
            ->willReturn(1);
        $this->connectionMock->expects($this->once())
            ->method('update')
            ->with(
                $table,
                ['children_count' => new \Zend_Db_Expr('children_count - 1')],
                ['entity_id IN(?)' => $parentIds]
            );
        $this->aggregateCount->processDelete($this->categoryMock);
    }

    /**
     * Subcategory with multiple scheduled updates:
     * getCategoryRowCount() returns >1, so we decrement by that count.
     */
    public function testProcessDeleteWithMultipleScheduledUpdates(): void
    {
        $parentIds = [1, 2]; // e.g. root and default category as parents
        $table = 'catalog_category_entity';

        $this->categoryMock->expects($this->once())
            ->method('getResource')
            ->willReturn($this->resourceCategoryMock);
        $this->categoryMock->expects($this->once())
            ->method('getParentIds')
            ->willReturn($parentIds);
        $this->resourceCategoryMock->expects($this->any())
            ->method('getEntityTable')
            ->willReturn($table);
        $this->resourceCategoryMock->expects($this->once())
            ->method('getConnection')
            ->willReturn($this->connectionMock);

        // Case: base row + 2 scheduled updates = 3 rows → decrement by 3
        $this->connectionMock->expects($this->once())
            ->method('fetchOne')
            ->willReturn(3);

        $this->connectionMock->expects($this->once())
            ->method('update')
            ->with(
                $table,
                ['children_count' => new \Zend_Db_Expr('children_count - 3')],
                ['entity_id IN(?)' => $parentIds]
            );

        $this->aggregateCount->processDelete($this->categoryMock);
    }
}
