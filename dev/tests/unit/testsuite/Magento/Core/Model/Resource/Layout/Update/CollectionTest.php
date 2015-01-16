<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Core\Model\Resource\Layout\Update;

class CollectionTest extends \Magento\Core\Model\Resource\Layout\AbstractTestCase
{
    /**
     * Retrieve layout update collection instance
     *
     * @param \Zend_Db_Select $select
     * @return \Magento\Core\Model\Resource\Layout\Update\Collection
     */
    protected function _getCollection(\Zend_Db_Select $select)
    {
        $eventManager = $this->getMock('Magento\Framework\Event\ManagerInterface', [], [], '', false);

        return new \Magento\Core\Model\Resource\Layout\Update\Collection(
            $this->getMock('Magento\Core\Model\EntityFactory', [], [], '', false),
            $this->getMock('Psr\Log\LoggerInterface'),
            $this->getMockForAbstractClass('Magento\Framework\Data\Collection\Db\FetchStrategyInterface'),
            $eventManager,
            $this->getMock('Magento\Framework\Stdlib\DateTime', null, [], '', true),
            null,
            $this->_getResource($select)
        );
    }

    public function testAddThemeFilter()
    {
        $themeId = 1;
        $select = $this->getMock('Zend_Db_Select', [], [], '', false);
        $select->expects($this->once())->method('where')->with('link.theme_id = ?', $themeId);

        $collection = $this->_getCollection($select);
        $collection->addThemeFilter($themeId);
    }

    public function testAddStoreFilter()
    {
        $storeId = 1;
        $select = $this->getMock('Zend_Db_Select', [], [], '', false);
        $select->expects($this->once())->method('where')->with('link.store_id = ?', $storeId);

        $collection = $this->_getCollection($select);
        $collection->addStoreFilter($storeId);
    }

    /**
     * @covers \Magento\Core\Model\Resource\Layout\Update\Collection::_joinWithLink
     */
    public function testJoinWithLink()
    {
        $select = $this->getMock('Zend_Db_Select', [], [], '', false);
        $select->expects(
            $this->once()
        )->method(
            'join'
        )->with(
            ['link' => 'core_layout_link'],
            'link.layout_update_id = main_table.layout_update_id',
            $this->isType('array')
        );

        $collection = $this->_getCollection($select);
        $collection->addStoreFilter(1);
        $collection->addThemeFilter(1);
    }

    public function testAddNoLinksFilter()
    {
        $select = $this->getMock('Zend_Db_Select', [], [], '', false);
        $select->expects(
            $this->once()
        )->method(
            'joinLeft'
        )->with(
            ['link' => 'core_layout_link'],
            'link.layout_update_id = main_table.layout_update_id',
            [[]]
        );
        $select->expects($this->once())->method('where')->with(self::TEST_WHERE_CONDITION);

        $collection = $this->_getCollection($select);

        /** @var $connection \PHPUnit_Framework_MockObject_MockObject */
        $connection = $collection->getResource()->getReadConnection();
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
