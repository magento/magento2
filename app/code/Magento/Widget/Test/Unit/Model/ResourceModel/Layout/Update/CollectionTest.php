<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Widget\Test\Unit\Model\ResourceModel\Layout\Update;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Widget\Model\ResourceModel\Layout\Update\Collection;
use Magento\Widget\Test\Unit\Model\ResourceModel\Layout\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

class CollectionTest extends AbstractTestCase
{
    /**
     * Retrieve layout update collection instance
     *
     * @param Select $select
     * @return Collection
     */
    protected function _getCollection(Select $select)
    {
        $eventManager = $this->getMockForAbstractClass(ManagerInterface::class);

        return new Collection(
            $this->createMock(EntityFactory::class),
            $this->getMockForAbstractClass(LoggerInterface::class),
            $this->getMockForAbstractClass(FetchStrategyInterface::class),
            $eventManager,
            $this->createPartialMock(DateTime::class, []),
            null,
            $this->_getResource($select)
        );
    }

    public function testAddThemeFilter()
    {
        $themeId = 1;
        $select = $this->createMock(Select::class);
        $select->expects($this->once())->method('where')->with('link.theme_id = ?', $themeId);

        $collection = $this->_getCollection($select);
        $collection->addThemeFilter($themeId);
    }

    public function testAddStoreFilter()
    {
        $storeId = 1;
        $select = $this->createMock(Select::class);
        $select->expects($this->once())->method('where')->with('link.store_id = ?', $storeId);

        $collection = $this->_getCollection($select);
        $collection->addStoreFilter($storeId);
    }

    /**
     * @covers \Magento\Widget\Model\ResourceModel\Layout\Update\Collection::_joinWithLink
     */
    public function testJoinWithLink()
    {
        $select = $this->createMock(Select::class);
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
        $select = $this->createMock(Select::class);
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

        /** @var MockObject $connection */
        $connection = $collection->getResource()->getConnection();
        $connection->expects(
            $this->once()
        )->method(
            'prepareSqlCondition'
        )->with(
            'link.layout_update_id',
            ['null' => true]
        )->willReturn(
            self::TEST_WHERE_CONDITION
        );

        $collection->addNoLinksFilter();
    }
}
