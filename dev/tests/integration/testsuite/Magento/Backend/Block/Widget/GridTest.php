<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget;

/**
 * @magentoAppArea adminhtml
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GridTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Backend\Block\Widget\Grid\ColumnSet
     */
    protected $_block;

    /**
     * @var \Magento\Framework\View\LayoutInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_layoutMock;

    /**
     * @var \Magento\Backend\Block\Widget\Grid\ColumnSet|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_columnSetMock;

    protected function setUp()
    {
        $this->_layoutMock = $this->createPartialMock(
            \Magento\Framework\View\Layout::class,
            ['getChildName', 'getBlock', 'createBlock', 'helper', 'renameElement', 'unsetChild', 'setChild']
        );
        $this->_columnSetMock = $this->_getColumnSetMock();

        $returnValueMap = [
            ['grid', 'grid.columnSet', 'grid.columnSet'],
            ['grid', 'reset_filter_button', 'reset_filter_button'],
            ['grid', 'search_button', 'search_button'],
        ];
        $this->_layoutMock->expects(
            $this->any()
        )->method(
            'getChildName'
        )->will(
            $this->returnValueMap($returnValueMap)
        );
        $this->_layoutMock->expects(
            $this->any()
        )->method(
            'getBlock'
        )->with(
            'grid.columnSet'
        )->will(
            $this->returnValue($this->_columnSetMock)
        );
        $this->_layoutMock->expects(
            $this->any()
        )->method(
            'createBlock'
        )->with(
            \Magento\Backend\Block\Widget\Button::class
        )->will(
            $this->returnValue(
                \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                    \Magento\Framework\View\LayoutInterface::class
                )->createBlock(
                    \Magento\Backend\Block\Widget\Button::class
                )
            )
        );
        $this->_layoutMock->expects(
            $this->any()
        )->method(
            'helper'
        )->with(
            \Magento\Framework\Json\Helper\Data::class
        )->will(
            $this->returnValue(
                \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                    \Magento\Framework\Json\Helper\Data::class
                )
            )
        );

        $this->_block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\View\LayoutInterface::class
        )->createBlock(
            \Magento\Backend\Block\Widget\Grid::class
        );
        $this->_block->setLayout($this->_layoutMock);
        $this->_block->setNameInLayout('grid');
    }

    /**
     * Retrieve the mocked column set block instance
     *
     * @return \Magento\Backend\Block\Widget\Grid\ColumnSet|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getColumnSetMock()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $directoryList = $objectManager->create(
            \Magento\Framework\App\Filesystem\DirectoryList::class,
            ['root' => __DIR__]
        );
        return $this->getMockBuilder(\Magento\Backend\Block\Widget\Grid\ColumnSet::class)
            ->setConstructorArgs(
                [
                    $objectManager->create(
                        \Magento\Framework\View\Element\Template\Context::class,
                        [
                            'filesystem' => $objectManager->create(
                                \Magento\Framework\Filesystem::class,
                                ['directoryList' => $directoryList]
                            )
                        ]
                    ),
                    $objectManager->create(\Magento\Backend\Model\Widget\Grid\Row\UrlGeneratorFactory::class),
                    $objectManager->create(\Magento\Backend\Model\Widget\Grid\SubTotals::class),
                    $objectManager->create(\Magento\Backend\Model\Widget\Grid\Totals::class)
                ]
            )
            ->getMock();
    }

    public function testToHtmlPreparesColumns()
    {
        $this->_columnSetMock->expects($this->once())->method('setRendererType');
        $this->_columnSetMock->expects($this->once())->method('setFilterType');
        $this->_columnSetMock->expects($this->once())->method('setSortable');
        $this->_block->setColumnRenderers(['filter' => 'Filter_Class']);
        $this->_block->setColumnFilters(['filter' => 'Filter_Class']);
        $this->_block->setSortable(false);
        $this->_block->toHtml();
    }

    public function testGetMainButtonsHtmlReturnsEmptyStringIfFiltersArentVisible()
    {
        $this->_columnSetMock->expects($this->once())->method('isFilterVisible')->will($this->returnValue(false));
        $this->_block->getMainButtonsHtml();
    }

    public function testGetMassactionBlock()
    {
        /** @var $layout \Magento\Framework\View\Layout */
        $layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\View\LayoutInterface::class
        );
        /** @var $block \Magento\Backend\Block\Widget\Grid */
        $block = $layout->createBlock(\Magento\Backend\Block\Widget\Grid\Extended::class, 'block');
        $child = $layout->addBlock(\Magento\Framework\View\Element\Template::class, 'massaction', 'block');
        $this->assertSame($child, $block->getMassactionBlock());
    }
}
