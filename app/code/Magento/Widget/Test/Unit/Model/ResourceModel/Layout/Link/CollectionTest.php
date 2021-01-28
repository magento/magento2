<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Test\Unit\Model\ResourceModel\Layout\Link;

class CollectionTest extends \Magento\Widget\Test\Unit\Model\ResourceModel\Layout\AbstractTestCase
{
    /**
     * Name of test table
     */
    const TEST_TABLE = 'layout_update';

    /**
     * Name of main table alias
     *
     * @var string
     */
    protected $_tableAlias = 'update';

    /**
     * @param \Magento\Framework\DB\Select $select
     * @return \Magento\Widget\Model\ResourceModel\Layout\Link\Collection
     */
    protected function _getCollection(\Magento\Framework\DB\Select $select)
    {
        $eventManager = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);

        return new \Magento\Widget\Model\ResourceModel\Layout\Link\Collection(
            $this->createMock(\Magento\Framework\Data\Collection\EntityFactory::class),
            $this->createMock(\Psr\Log\LoggerInterface::class),
            $this->getMockForAbstractClass(\Magento\Framework\Data\Collection\Db\FetchStrategyInterface::class),
            $eventManager,
            $this->createPartialMock(\Magento\Framework\Stdlib\DateTime::class, []),
            null,
            $this->_getResource($select)
        );
    }

    /**
     * @dataProvider filterFlagDataProvider
     * @param bool $flag
     */
    public function testAddTemporaryFilter($flag)
    {
        $select = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->setConstructorArgs(['where'])
            ->disableOriginalConstructor()
            ->getMock();
        $select->expects($this->once())->method('where')->with(self::TEST_WHERE_CONDITION);

        $collection = $this->_getCollection($select);

        /** @var $connection \PHPUnit\Framework\MockObject\MockObject */
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
    public function filterFlagDataProvider()
    {
        return [
            'Add temporary filter' => ['$flag' => true],
            'Disable temporary filter' => ['$flag' => false]
        ];
    }

    /**
     * @covers \Magento\Widget\Model\ResourceModel\Layout\Link\Collection::_joinWithUpdate
     */
    public function testJoinWithUpdate()
    {
        $select = $this->createMock(\Magento\Framework\DB\Select::class);
        $select->expects(
            $this->once()
        )->method(
            'join'
        )->with(
            ['update' => self::TEST_TABLE],
            'update.layout_update_id = main_table.layout_update_id',
            $this->isType('array')
        );

        $collection = $this->_getCollection($select);

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
