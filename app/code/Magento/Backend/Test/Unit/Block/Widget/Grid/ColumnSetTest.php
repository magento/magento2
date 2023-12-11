<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Block\Widget\Grid;

use Magento\Backend\Block\Widget\Grid;
use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Grid\ColumnSet;
use Magento\Backend\Model\Widget\Grid\Row\UrlGenerator;
use Magento\Backend\Model\Widget\Grid\Row\UrlGeneratorFactory;
use Magento\Backend\Model\Widget\Grid\SubTotals;
use Magento\Backend\Model\Widget\Grid\Totals;
use Magento\Framework\Data\Collection;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Layout;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ColumnSetTest extends TestCase
{
    /**
     * @var ColumnSet
     */
    protected $_block;

    /**
     * @var MockObject
     */
    protected $_layoutMock;

    /**
     * @var MockObject
     */
    protected $_columnMock;

    /**
     * @var MockObject
     */
    protected $_factoryMock;

    /**
     * @var MockObject
     */
    protected $_subtotalsMock;

    /**
     * @var MockObject
     */
    protected $_totalsMock;

    /**
     * @var MockObject
     */
    protected $_gridMock;

    protected function setUp(): void
    {
        $this->_columnMock = $this->createPartialMock(
            Column::class,
            ['setSortable', 'setRendererType', 'setFilterType']
        );
        $this->_layoutMock = $this->createMock(Layout::class);
        $this->_layoutMock->expects(
            $this->any()
        )->method(
            'getChildBlocks'
        )->willReturn(
            ['column' => $this->_columnMock]
        );
        $this->_factoryMock = $this->createMock(UrlGeneratorFactory::class);

        $this->_subtotalsMock = $this->createMock(SubTotals::class);

        $this->_totalsMock = $this->createMock(Totals::class);

        $arguments = [
            'layout' => $this->_layoutMock,
            'generatorFactory' => $this->_factoryMock,
            'totals' => $this->_totalsMock,
            'subtotals' => $this->_subtotalsMock,
        ];

        $objectManagerHelper = new ObjectManager($this);
        $this->_block = $objectManagerHelper->getObject(
            ColumnSet::class,
            $arguments
        );
        $this->_block->setNameInLayout('grid.columnSet');
    }

    protected function tearDown(): void
    {
        unset($this->_block);
        unset($this->_layoutMock);
        unset($this->_columnMock);
        unset($this->_factoryMock);
        unset($this->_totalsMock);
        unset($this->_subtotalsMock);
    }

    public function testSetSortablePropagatesSortabilityToChildren()
    {
        $this->_columnMock->expects($this->once())->method('setSortable')->with(false);
        $this->_block->setSortable(false);
    }

    public function testSetSortablePropagatesSortabilityToChildrenOnlyIfSortabilityIsFalse()
    {
        $this->_columnMock->expects($this->never())->method('setSortable');
        $this->_block->setSortable(true);
    }

    public function testSetRendererTypePropagatesRendererTypeToColumns()
    {
        $this->_columnMock->expects($this->once())->method('setRendererType')->with('renderer', 'Renderer_Class');
        $this->_block->setRendererType('renderer', 'Renderer_Class');
    }

    public function testSetFilterTypePropagatesFilterTypeToColumns()
    {
        $this->_columnMock->expects($this->once())->method('setFilterType')->with('filter', 'Filter_Class');
        $this->_block->setFilterType('filter', 'Filter_Class');
    }

    public function testGetRowUrlIfUrlPathNotSet()
    {
        $this->assertEquals('#', $this->_block->getRowUrl(new \stdClass()));
    }

    public function testGetRowUrl()
    {
        $generatorClass = UrlGenerator::class;

        $itemMock = $this->createMock(DataObject::class);

        $rowUrlGenerator =
            $this->createPartialMock(UrlGenerator::class, ['getUrl']);
        $rowUrlGenerator->expects(
            $this->once()
        )->method(
            'getUrl'
        )->with(
            $itemMock
        )->willReturn(
            'http://localhost/mng/item/edit'
        );

        $factoryMock = $this->createPartialMock(
            UrlGeneratorFactory::class,
            ['createUrlGenerator']
        );
        $factoryMock->expects(
            $this->once()
        )->method(
            'createUrlGenerator'
        )->with(
            $generatorClass,
            ['args' => ['generatorClass' => $generatorClass]]
        )->willReturn(
            $rowUrlGenerator
        );

        $arguments = [
            'layout' => $this->_layoutMock,
            'generatorFactory' => $factoryMock,
            'data' => ['rowUrl' => ['generatorClass' => $generatorClass]],
            'totals' => $this->_totalsMock,
            'subtotals' => $this->_subtotalsMock,
        ];

        $objectManagerHelper = new ObjectManager($this);
        /** @var \Magento\Backend\Block\Widget\Grid\ColumnSet $model */
        $model = $objectManagerHelper->getObject(ColumnSet::class, $arguments);

        $url = $model->getRowUrl($itemMock);
        $this->assertEquals('http://localhost/mng/item/edit', $url);
    }

    public function testItemHasMultipleRows()
    {
        $item = new DataObject();
        // prepare sub-collection
        $subCollection = new Collection(
            $this->createMock(EntityFactory::class)
        );
        $subCollection->addItem(new DataObject(['test4' => '1', 'test5' => '2']));
        $subCollection->addItem(new DataObject(['test4' => '2', 'test5' => '2']));
        $item->setChildren($subCollection);

        $this->assertTrue($this->_block->hasMultipleRows($item));
    }

    public function testShouldRenderTotalWithNotEmptyCollection()
    {
        $this->_prepareLayoutWithGrid($this->_prepareGridMock($this->_getTestCollection()));

        $this->_block->setCountTotals(true);
        $this->assertTrue($this->_block->shouldRenderTotal());
    }

    public function testShouldRenderTotalWithEmptyCollection()
    {
        $this->_prepareLayoutWithGrid(
            $this->_prepareGridMock(
                new Collection(
                    $this->createMock(EntityFactory::class)
                )
            )
        );

        $this->_block->setCountTotals(true);
        $this->assertFalse($this->_block->shouldRenderTotal());
    }

    public function testShouldRenderTotalWithFlagFalse()
    {
        $this->_block->setCountTotals(false);
        $this->assertFalse($this->_block->shouldRenderTotal());
    }

    public function testShouldRenderSubtotalWithFlagFalse()
    {
        $this->_block->setCountSubTotals(false);
        $this->assertFalse($this->_block->shouldRenderSubTotal(new DataObject()));
    }

    public function testShouldRenderSubtotalWithEmptySubData()
    {
        $this->_block->setCountSubTotals(true);
        $this->assertFalse($this->_block->shouldRenderSubTotal(new DataObject()));
    }

    public function testShouldRenderSubtotalWithNotEmptySubData()
    {
        $item = new DataObject();
        // prepare sub-collection
        $subCollection = new Collection(
            $this->createMock(EntityFactory::class)
        );
        $subCollection->addItem(new DataObject(['test4' => '1', 'test5' => '2']));
        $subCollection->addItem(new DataObject(['test4' => '2', 'test5' => '2']));
        $item->setChildren($subCollection);

        $this->_block->setCountSubTotals(true);
        $this->assertTrue($this->_block->shouldRenderSubTotal($item));
    }

    public function testUpdateItemByFirstMultiRow()
    {
        $item = new DataObject(['test1' => '1']);
        // prepare sub-collection
        $subCollection = new Collection(
            $this->createMock(EntityFactory::class)
        );
        $subCollection->addItem(new DataObject(['test4' => '1', 'test5' => '2']));
        $subCollection->addItem(new DataObject(['test4' => '2', 'test5' => '2']));
        $item->setChildren($subCollection);

        $expectedItem = new DataObject(['test1' => '1']);
        $expectedItem->addData(['test4' => '1', 'test5' => '2']);
        $expectedItem->setChildren($subCollection);

        $this->_block->updateItemByFirstMultiRow($item);
        $this->assertEquals($expectedItem, $item);
    }

    public function testGetSubTotals()
    {
        // prepare sub-collection
        $subCollection = new Collection(
            $this->createMock(EntityFactory::class)
        );
        $subCollection->addItem(new DataObject(['column' => '1']));
        $subCollection->addItem(new DataObject(['column' => '1']));

        $this->_subtotalsMock->expects(
            $this->once()
        )->method(
            'countTotals'
        )->with(
            $subCollection
        )->willReturn(
            new DataObject(['column' => '2'])
        );

        // prepare item
        $item = new DataObject(['test1' => '1']);
        $item->setChildren($subCollection);

        $this->assertEquals(new DataObject(['column' => '2']), $this->_block->getSubTotals($item));
    }

    public function testGetTotals()
    {
        $collection = $this->_getTestCollection();
        $this->_prepareLayoutWithGrid($this->_prepareGridMock($collection));

        $this->_totalsMock->expects(
            $this->once()
        )->method(
            'countTotals'
        )->with(
            $collection
        )->willReturn(
            new DataObject(['test1' => '3', 'test2' => '2'])
        );

        $this->assertEquals(
            new DataObject(['test1' => '3', 'test2' => '2']),
            $this->_block->getTotals()
        );
    }

    /**
     * Retrieve prepared mock for \Magento\Backend\Model\Widget\Grid with collection
     *
     * @param \Magento\Framework\Data\Collection $collection
     * @return MockObject
     */
    protected function _prepareGridMock($collection)
    {
        // prepare block grid
        $gridMock = $this->createPartialMock(Grid::class, ['getCollection']);
        $gridMock->expects($this->any())->method('getCollection')->willReturn($collection);

        return $gridMock;
    }

    /**
     * Retrieve test collection
     *
     * @return \Magento\Framework\Data\Collection
     */
    protected function _getTestCollection()
    {
        $collection = new Collection(
            $this->createMock(EntityFactory::class)
        );
        $items = [
            new DataObject(['test1' => '1', 'test2' => '2']),
            new DataObject(['test1' => '1', 'test2' => '2']),
            new DataObject(['test1' => '1', 'test2' => '2']),
        ];
        foreach ($items as $item) {
            $collection->addItem($item);
        }

        return $collection;
    }

    /**
     * Prepare layout for receiving grid block
     *
     * @param MockObject $gridMock
     */
    protected function _prepareLayoutWithGrid($gridMock)
    {
        $this->_layoutMock->expects(
            $this->any()
        )->method(
            'getParentName'
        )->with(
            'grid.columnSet'
        )->willReturn(
            'grid'
        );
        $this->_layoutMock->expects(
            $this->any()
        )->method(
            'getBlock'
        )->with(
            'grid'
        )->willReturn(
            $gridMock
        );
    }
}
