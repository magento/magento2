<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Backend\Block\Widget\Grid\Column
 */
namespace Magento\Backend\Block\Widget\Grid;

class ColumnTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Block\Widget\Grid\Column
     */
    protected $_block;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_layoutMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_blockMock;

    protected function setUp()
    {
        $this->_layoutMock = $this->getMock('Magento\Framework\View\Layout', [], [], '', false, false);
        $this->_blockMock = $this->getMock(
            'Magento\Framework\View\Element\Template',
            ['setColumn', 'getHtml'],
            [],
            '',
            false,
            false
        );

        $arguments = [
            'layout' => $this->_layoutMock,
            'urlBuilder' => $this->getMock('Magento\Backend\Model\Url', [], [], '', false),
        ];
        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_block = $objectManagerHelper->getObject('Magento\Backend\Block\Widget\Grid\Column', $arguments);
        $this->_block->setId('id');
    }

    public function testGetFilterWhenFilterSetFalse()
    {
        $this->_block->setData('filter', false);
        $this->assertFalse($this->_block->getFilter());
    }

    public function testGetFilterWhenFilterSetZero()
    {
        $this->_block->setData('filter', '0');
        $this->assertFalse($this->_block->getFilter());
    }

    /**
     * Check that default filter will be used if filter was not set
     */
    public function testGetFilterWhenFilterIsNotSet()
    {
        $this->_layoutMock->expects(
            $this->once()
        )->method(
            'createBlock'
        )->with(
            'Magento\Backend\Block\Widget\Grid\Column\Filter\Text'
        )->will(
            $this->returnValue($this->_blockMock)
        );

        $this->_block->getFilter();
    }

    public function testGetSortableNotSet()
    {
        $this->assertTrue($this->_block->getSortable());
    }

    /**
     * @dataProvider getSortableDataProvider
     */
    public function testGetSortable($value)
    {
        $this->_block->setData('sortable', $value);
        $this->assertFalse($this->_block->getSortable());
    }

    public function getSortableDataProvider()
    {
        return ['zero' => ['0'], 'false' => [false], 'null' => [null]];
    }

    /**
     * @covers \Magento\Backend\Block\Widget\Grid\Column::getFilter
     * @covers \Magento\Backend\Block\Widget\Grid\Column::setFilterType
     */
    public function testGetFilterWithSetEmptyCustomFilterType()
    {
        $this->_block->setData('type', 'custom_type');
        $this->_block->setFilterType('custom_type', false);
        $this->assertFalse($this->_block->getFilter());
    }

    /**
     * @covers \Magento\Backend\Block\Widget\Grid\Column::getFilter
     */
    public function testGetFilterWithInvalidFilterTypeWhenUseDefaultFilter()
    {
        $this->_block->setData('type', 'invalid_filter_type');

        $this->_layoutMock->expects(
            $this->once()
        )->method(
            'createBlock'
        )->with(
            'Magento\Backend\Block\Widget\Grid\Column\Filter\Text'
        )->will(
            $this->returnValue($this->_blockMock)
        );

        $this->_block->getFilter();
    }

    /**
     * @covers \Magento\Backend\Block\Widget\Grid\Column::getFilter
     */
    public function testGetFilterWhenUseCustomFilter()
    {
        $this->_block->setData('type', 'custom_type');
        $this->_block->setFilterType('custom_type', 'StdClass');

        $this->_layoutMock->expects(
            $this->once()
        )->method(
            'createBlock'
        )->with(
            'StdClass'
        )->will(
            $this->returnValue($this->_blockMock)
        );

        $this->_block->getFilter();
    }

    /**
     * @covers \Magento\Backend\Block\Widget\Grid\Column::getFilter
     * @covers \Magento\Backend\Block\Widget\Grid\Column::setFilter
     */
    public function testGetFilterWhenFilterWasSetPreviously()
    {
        $this->_layoutMock->expects(
            $this->once()
        )->method(
            'createBlock'
        )->with(
            'StdClass'
        )->will(
            $this->returnValue($this->_blockMock)
        );

        $this->_block->setFilter('StdClass');
        $this->assertNotEmpty($this->_block->getFilter());
    }

    public function testGetFilterHtmlWhenFilterExist()
    {
        $this->_blockMock->expects($this->once())->method('getHtml')->will($this->returnValue('test'));

        $this->_layoutMock->expects(
            $this->once()
        )->method(
            'createBlock'
        )->with(
            'StdClass'
        )->will(
            $this->returnValue($this->_blockMock)
        );

        $this->_block->setFilter('StdClass');
        $this->assertEquals('test', $this->_block->getFilterHtml());
    }

    public function testGetFilterHtmlWhenFilterNotExist()
    {
        $this->_block->setData('type', 'custom_type');
        $this->_block->setFilterType('custom_type', false);
        $this->assertEquals('&nbsp;', $this->_block->getFilterHtml());
    }

    public function testGetRendererWhenRendererIsSet()
    {
        $this->_block->setData('renderer', 'StdClass');

        $this->_blockMock->expects($this->once())->method('setColumn')->will($this->returnSelf());

        $this->_layoutMock->expects(
            $this->once()
        )->method(
            'createBlock'
        )->with(
            'StdClass'
        )->will(
            $this->returnValue($this->_blockMock)
        );

        $this->assertNotEmpty($this->_block->getRenderer());
    }

    /**
     * @covers \Magento\Backend\Block\Widget\Grid\Column::getRenderer
     */
    public function testGetRendererWheRendererSetFalse()
    {
        $this->_block->setData('renderer', false);

        $this->_blockMock->expects($this->once())->method('setColumn')->will($this->returnSelf());

        $this->_layoutMock->expects(
            $this->once()
        )->method(
            'createBlock'
        )->with(
            'Magento\Backend\Block\Widget\Grid\Column\Renderer\Text'
        )->will(
            $this->returnValue($this->_blockMock)
        );

        $this->assertEquals($this->_blockMock, $this->_block->getRenderer());
    }

    /**
     * @covers \Magento\Backend\Block\Widget\Grid\Column::getRenderer
     * @covers \Magento\Backend\Block\Widget\Grid\Column::setRendererType
     */
    public function testGetRendererWhenUseCustomRenderer()
    {
        $this->_block->setData('type', 'custom_type');
        $this->_block->setRendererType('custom_type', 'StdClass');

        $this->_blockMock->expects($this->once())->method('setColumn')->will($this->returnSelf());

        $this->_layoutMock->expects(
            $this->once()
        )->method(
            'createBlock'
        )->with(
            'StdClass'
        )->will(
            $this->returnValue($this->_blockMock)
        );

        $this->assertEquals($this->_blockMock, $this->_block->getRenderer());
    }

    /**
     * @covers \Magento\Backend\Block\Widget\Grid\Column::getRenderer
     * @covers \Magento\Backend\Block\Widget\Grid\Column::setRenderer
     */
    public function testGetRendererWhenRendererWasSetPreviously()
    {
        $this->_block->setRenderer($this->_blockMock);
        $this->assertEquals($this->_blockMock, $this->_block->getRenderer());
    }

    public function testGetExportHeaderWhenExportHeaderIsSet()
    {
        $this->_block->setData('header_export', 'test');
        $this->assertEquals('test', $this->_block->getExportHeader());
    }

    public function testGetExportHeaderWhenExportHeaderIsNotSetAndHeaderIsSet()
    {
        $this->_block->setData('header', 'test');
        $this->assertEquals('test', $this->_block->getExportHeader());
    }

    public function testGetHeaderHtmlPropertyWhenHeaderCssClassEmpty()
    {
        $this->assertEquals(' class=" col-id"', $this->_block->getHeaderHtmlProperty());
    }

    public function testGetHeaderHtmlPropertyWhenHeaderCssClassIsSet()
    {
        $this->_block->setData('header_css_class', 'test');
        $this->assertEquals(' class="test col-id"', $this->_block->getHeaderHtmlProperty());
    }

    public function testAddHeaderCssClassWhenHeaderCssClassEmpty()
    {
        $this->_block->addHeaderCssClass('test');
        $this->assertEquals(' class="test col-id"', $this->_block->getHeaderHtmlProperty());
    }

    public function testAddHeaderCssClassWhenHeaderCssClassIsSet()
    {
        $this->_block->setData('header_css_class', 'test1');
        $this->_block->addHeaderCssClass('test2');
        $this->assertEquals(' class="test1 test2 col-id"', $this->_block->getHeaderHtmlProperty());
    }

    public function testGetHeaderCssClassWhenNotSortable()
    {
        $this->_block->setData('header_css_class', 'test');
        $this->_block->setSortable(false);
        $this->assertEquals('test no-link col-id', $this->_block->getHeaderCssClass());
    }

    public function testGetHeaderCssClassWhenIsSortable()
    {
        $this->_block->setData('header_css_class', 'test');
        $this->_block->setSortable(true);
        $this->assertEquals('test col-id', $this->_block->getHeaderCssClass());
    }

    public function testGetCssClassWithAlignAndEditableAndWithoutColumnCssClass()
    {
        $this->_block->setAlign('left');
        $this->_block->setEditable(true);
        $this->assertEquals('a-left editable col-id', $this->_block->getCssClass());
    }

    public function testGetCssClassWithAlignAndEditableAndWithColumnCssClass()
    {
        $this->_block->setAlign('left');
        $this->_block->setEditable(true);
        $this->_block->setData('column_css_class', 'test');

        $this->assertEquals('a-left test editable col-id', $this->_block->getCssClass());
    }

    public function testGetCssClassWithoutAlignEditableAndColumnCssClass()
    {
        $this->assertEquals(' col-id', $this->_block->getCssClass());
    }

    public function testSetGetGrid()
    {
        /**
         * Check that getFilter will be executed
         */
        $this->_layoutMock->expects(
            $this->once()
        )->method(
            'createBlock'
        )->with(
            'StdClass'
        )->will(
            $this->returnValue($this->_blockMock)
        );

        $this->_block->setFilter('StdClass');

        $grid = new \StdClass();
        $this->_block->setGrid($grid);
        $this->assertEquals($grid, $this->_block->getGrid());
    }

    /**
     * @param $groupedData
     * @param $expected
     * @dataProvider columnGroupedDataProvider
     */
    public function testColumnIsGrouped($groupedData, $expected)
    {
        $arguments = [
            'layout' => $this->_layoutMock,
            'urlBuilder' => $this->getMock('Magento\Backend\Model\Url', [], [], '', false),
            'data' => $groupedData,
        ];

        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $block = $objectManagerHelper->getObject('Magento\Backend\Block\Widget\Grid\Column', $arguments);
        $this->assertEquals($expected, $block->isGrouped());
    }

    public function columnGroupedDataProvider()
    {
        return [[[], false], [['grouped' => 0], false], [['grouped' => 1], true]];
    }
}
