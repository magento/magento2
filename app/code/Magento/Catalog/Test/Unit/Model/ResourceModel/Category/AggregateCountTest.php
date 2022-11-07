<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
        $this->connectionMock = $this->getMockBuilder(AdapterInterface::class)
            ->getMockForAbstractClass();
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
        $this->resourceCategoryMock->expects($this->any())
            ->method('getEntityTable')
            ->willReturn($table);
        $this->resourceCategoryMock->expects($this->once())
            ->method('getConnection')
            ->willReturn($this->connectionMock);
        $this->connectionMock->expects($this->once())
            ->method('update')
            ->with(
                $table,
                ['children_count' => new \Zend_Db_Expr('children_count - 1')],
                ['entity_id IN(?)' => $parentIds]
            );
        $this->aggregateCount->processDelete($this->categoryMock);
    }
}
