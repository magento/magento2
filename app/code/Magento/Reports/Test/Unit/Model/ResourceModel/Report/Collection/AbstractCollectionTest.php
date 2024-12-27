<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Reports\Test\Unit\Model\ResourceModel\Report\Collection;

use Magento\Framework\Data\Collection\Db\FetchStrategy\Query;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\Event\Manager;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Reports\Model\ResourceModel\Report\Collection\AbstractCollection;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class AbstractCollectionTest extends TestCase
{
    /**
     * Tested collection
     *
     * @var AbstractCollection
     */
    protected $collection;

    protected function setUp(): void
    {
        $entityFactory = $this->createMock(EntityFactory::class);
        $logger = $this->getMockForAbstractClass(LoggerInterface::class);
        $fetchStrategy = $this->createMock(Query::class);
        $eventManager = $this->createMock(Manager::class);
        $connection = $this->createMock(Mysql::class);

        $resource = $this->getMockBuilder(AbstractDb::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getConnection'])
            ->getMockForAbstractClass();
        $resource->method('getConnection')->willReturn($connection);

        $this->collection = new AbstractCollection(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );
    }

    public function testIsSubtotalsGetDefault()
    {
        $this->assertFalse($this->collection->isSubTotals());
    }

    public function testSetIsSubtotals()
    {
        $this->collection->setIsSubTotals(true);
        $this->assertTrue($this->collection->isSubTotals());

        $this->collection->setIsSubTotals(false);
        $this->assertFalse($this->collection->isSubTotals());
    }
}
