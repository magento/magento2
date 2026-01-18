<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Widget\Test\Unit\Model\ResourceModel\Layout\Link;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Widget\Model\ResourceModel\Layout\Link\Collection;
use Magento\Widget\Test\Unit\Model\ResourceModel\Layout\AbstractTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

class CollectionTest extends AbstractTestCase
{
    /**
     * Name of test table
     */
    private const TEST_TABLE = 'layout_update';

    /**
     * Name of main table alias
     *
     * @var string
     */
    protected $tableAlias = 'update';

    /**
     * @param  Select $select
     * @return Collection
     */
    protected function getCollection(Select $select)
    {
        $eventManager = $this->createMock(ManagerInterface::class);

        return new Collection(
            $this->createMock(EntityFactory::class),
            $this->createMock(LoggerInterface::class),
            $this->createMock(FetchStrategyInterface::class),
            $eventManager,
            $this->createPartialMock(DateTime::class, []),
            null,
            $this->getResource($select)
        );
    }

    /**
     * @param bool $flag
     */
    #[DataProvider('filterFlagDataProvider')]
    public function testAddTemporaryFilter($flag)
    {
        $select = $this->createMock(Select::class);
        $select->expects($this->once())->method('where')->with(self::TEST_WHERE_CONDITION);

        $collection = $this->getCollection($select);

        /** @var MockObject $connection */
        $connection = $collection->getResource()->getConnection();
        $connection->expects(
            $this->any()
        )->method(
            'prepareSqlCondition'
        )->with(
            'main_table.is_temporary',
            $flag
        )->willReturn(
            self::TEST_WHERE_CONDITION
        );

        $collection->addTemporaryFilter($flag);
    }

    /**
     * @return array
     */
    public static function filterFlagDataProvider()
    {
        return [
            'Add temporary filter' => ['flag' => true],
            'Disable temporary filter' => ['flag' => false]
        ];
    }

    /**
     * @covers \Magento\Widget\Model\ResourceModel\Layout\Link\Collection::_joinWithUpdate
     */
    public function testJoinWithUpdate()
    {
        $select = $this->createMock(Select::class);
        $select->expects(
            $this->once()
        )->method(
            'join'
        )->with(
            ['update' => self::TEST_TABLE],
            'update.layout_update_id = main_table.layout_update_id',
            $this->callback('is_array')
        );

        $collection = $this->getCollection($select);

        /** @var $resource \PHPUnit\Framework\MockObject\MockObject */
        $resource = $collection->getResource();
        $resource->expects(
            $this->once()
        )->method(
            'getTable'
        )->with(
            self::TEST_TABLE
        )->willReturn(
            self::TEST_TABLE
        );

        $collection->addUpdatedDaysBeforeFilter(1)->addUpdatedDaysBeforeFilter(2);
    }
}
