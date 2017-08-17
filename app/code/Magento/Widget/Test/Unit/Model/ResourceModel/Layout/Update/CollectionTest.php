<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Test\Unit\Model\ResourceModel\Layout\Update;

class CollectionTest extends \Magento\Widget\Test\Unit\Model\ResourceModel\Layout\AbstractTestCase
{
    /**
     * Retrieve layout update collection instance
     *
     * @param \Magento\Framework\DB\Select $select
     * @return \Magento\Widget\Model\ResourceModel\Layout\Update\Collection
     */
    protected function _getCollection(\Magento\Framework\DB\Select $select)
    {
        $eventManager = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);

        return new \Magento\Widget\Model\ResourceModel\Layout\Update\Collection(
            $this->createMock(\Magento\Framework\Data\Collection\EntityFactory::class),
            $this->createMock(\Psr\Log\LoggerInterface::class),
            $this->getMockForAbstractClass(\Magento\Framework\Data\Collection\Db\FetchStrategyInterface::class),
            $eventManager,
            $this->createMock(\Magento\Framework\Stdlib\DateTime::class),
            null,
            $this->_getResource($select)
        );
    }

    public function testAddThemeFilter()
    {
        $themeId = 1;
        $select = $this->createMock(\Magento\Framework\DB\Select::class);
        $select->expects($this->once())->method('where')->with('link.theme_id = ?', $themeId);

        $collection = $this->_getCollection($select);
        $collection->addThemeFilter($themeId);
    }

    public function testAddStoreFilter()
    {
        $storeId = 1;
        $select = $this->createMock(\Magento\Framework\DB\Select::class);
        $select->expects($this->once())->method('where')->with('link.store_id = ?', $storeId);

        $collection = $this->_getCollection($select);
        $collection->addStoreFilter($storeId);
    }

    /**
     * @covers \Magento\Widget\Model\ResourceModel\Layout\Update\Collection::_joinWithLink
     */
    public function testJoinWithLink()
    {
        $select = $this->createMock(\Magento\Framework\DB\Select::class);
        $select->expects(
            $this->once()
        )->method(
            'join'
        )->with(
            ['link' => 'layout_link'],
            'link.layout_update_id = main_table.layout_update_id',
            $this->isType('array')
        );

        $collection = $this->_getCollection($select);
        $collection->addStoreFilter(1);
        $collection->addThemeFilter(1);
    }

    public function testAddNoLinksFilter()
    {
        $select = $this->createMock(\Magento\Framework\DB\Select::class);
        $select->expects(
            $this->once()
        )->method(
            'joinLeft'
        )->with(
            ['link' => 'layout_link'],
            'link.layout_update_id = main_table.layout_update_id',
            [[]]
        );
        $select->expects($this->once())->method('where')->with(self::TEST_WHERE_CONDITION);

        $collection = $this->_getCollection($select);

        /** @var $connection \PHPUnit_Framework_MockObject_MockObject */
        $connection = $collection->getResource()->getConnection();
        $connection->expects(
            $this->once()
        )->method(
            'prepareSqlCondition'
        )->with(
            'link.layout_update_id',
            ['null' => true]
        )->will(
            $this->returnValue(self::TEST_WHERE_CONDITION)
        );

        $collection->addNoLinksFilter();
    }
}
