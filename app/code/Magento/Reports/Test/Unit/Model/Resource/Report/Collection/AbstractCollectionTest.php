<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Unit\Model\Resource\Report\Collection;

class AbstractCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tested collection
     *
     * @var \Magento\Reports\Model\Resource\Report\Collection\AbstractCollection
     */
    protected $collection;

    protected function setUp()
    {
        $entityFactory = $this->getMock('\Magento\Framework\Data\Collection\EntityFactory', [], [], '', false);
        $logger = $this->getMock('\Psr\Log\LoggerInterface', [], [], '', false);
        $fetchStrategy = $this->getMock('\Magento\Framework\Data\Collection\Db\FetchStrategy\Query', [], [], '', false);
        $eventManager = $this->getMock('\Magento\Framework\Event\Manager', [], [], '', false);
        $connection = $this->getMock('\Magento\Framework\DB\Adapter\Pdo\Mysql', [], [], '', false);

        $resource = $this->getMockBuilder('\Magento\Framework\Model\Resource\Db\AbstractDb')
            ->disableOriginalConstructor()
            ->setMethods(['getReadConnection'])
            ->getMockForAbstractClass();
        $resource->method('getReadConnection')->willReturn($connection);

        $this->collection = new \Magento\Reports\Model\Resource\Report\Collection\AbstractCollection(
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
