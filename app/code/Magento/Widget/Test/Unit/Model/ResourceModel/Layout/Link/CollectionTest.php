<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
        $eventManager = $this->getMock('Magento\Framework\Event\ManagerInterface', [], [], '', false);

        return new \Magento\Widget\Model\ResourceModel\Layout\Link\Collection(
            $this->getMock('Magento\Framework\Data\Collection\EntityFactory', [], [], '', false),
            $this->getMock('Psr\Log\LoggerInterface'),
            $this->getMockForAbstractClass('Magento\Framework\Data\Collection\Db\FetchStrategyInterface'),
            $eventManager,
            $this->getMock('Magento\Framework\Stdlib\DateTime', null, [], '', true),
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
        $select = $this->getMock('Magento\Framework\DB\Select', [], ['where'], '', false);
        $select->expects($this->once())->method('where')->with(self::TEST_WHERE_CONDITION);

        $collection = $this->_getCollection($select);

        /** @var $connection \PHPUnit_Framework_MockObject_MockObject */
        $connection = $collection->getResource()->getConnection();
        $connection->expects(
            $this->any()
        )->method(
            'prepareSqlCondition'
        )->with(
            'main_table.is_temporary',
            $flag
        )->will(
            $this->returnValue(self::TEST_WHERE_CONDITION)
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
        $select = $this->getMock('Magento\Framework\DB\Select', [], [], '', false);
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

        /** @var $resource \PHPUnit_Framework_MockObject_MockObject */
        $resource = $collection->getResource();
        $resource->expects(
            $this->once()
        )->method(
            'getTable'
        )->with(
            self::TEST_TABLE
        )->will(
            $this->returnValue(self::TEST_TABLE)
        );

        $collection->addUpdatedDaysBeforeFilter(1)->addUpdatedDaysBeforeFilter(2);
    }
}
