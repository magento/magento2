<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Unit\Model\ResourceModel\Report\Collection;

class AbstractCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tested collection
     *
     * @var \Magento\Reports\Model\ResourceModel\Report\Collection\AbstractCollection
     */
    protected $collection;

    protected function setUp()
    {
        $entityFactory = $this->getMock(\Magento\Framework\Data\Collection\EntityFactory::class, [], [], '', false);
        $logger = $this->getMock(\Psr\Log\LoggerInterface::class, [], [], '', false);
        $fetchStrategy = $this->getMock(
            \Magento\Framework\Data\Collection\Db\FetchStrategy\Query::class,
            [],
            [],
            '',
            false
        );
        $eventManager = $this->getMock(\Magento\Framework\Event\Manager::class, [], [], '', false);
        $connection = $this->getMock(\Magento\Framework\DB\Adapter\Pdo\Mysql::class, [], [], '', false);

        $resource = $this->getMockBuilder(\Magento\Framework\Model\ResourceModel\Db\AbstractDb::class)
            ->disableOriginalConstructor()
            ->setMethods(['getConnection'])
            ->getMockForAbstractClass();
        $resource->method('getConnection')->willReturn($connection);

        $this->collection = new \Magento\Reports\Model\ResourceModel\Report\Collection\AbstractCollection(
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
