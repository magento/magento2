<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Unit\Block\Widget\Grid;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ColumnTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Backend\Block\Widget\Grid\Column
     */
    protected $_block;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_layoutMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_blockMock;

    protected function setUp(): void
    {
        $this->_layoutMock = $this->createMock(\Magento\Framework\View\Layout::class);
        $this->_blockMock = $this->createPartialMock(
            \Magento\Framework\View\Element\Template::class,
            ['setColumn', 'getHtml']
        );

        $arguments = [
            'layout' => $this->_layoutMock,
            'urlBuilder' => $this->createMock(\Magento\Backend\Model\Url::class),
        ];
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_block = $objectManagerHelper->getObject(\Magento\Backend\Block\Widget\Grid\Column::class, $arguments);
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
            \Magento\Backend\Block\Widget\Grid\Column\Filter\Text::class
        )->willReturn(
            $this->_blockMock
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

    /**
     * @return array
     */
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
            \Magento\Backend\Block\Widget\Grid\Column\Filter\Text::class
        )->willReturn(
            $this->_blockMock
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
        )->willReturn(
            $this->_blockMock
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
        )->willReturn(
            $this->_blockMock
        );

        $this->_block->setFilter('StdClass');
        $this->assertNotEmpty($this->_block->getFilter());
    }

    public function testGetFilterHtmlWhenFilterExist()
    {
        $this->_blockMock->expects($this->once())->method('getHtml')->willReturn('test');

        $this->_layoutMock->expects(
            $this->once()
        )->method(
            'createBlock'
        )->with(
            'StdClass'
        )->willReturn(
            $this->_blockMock
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

        $this->_blockMock->expects($this->once())->method('setColumn')->willReturnSelf();

        $this->_layoutMock->expects(
            $this->once()
        )->method(
            'createBlock'
        )->with(
            'StdClass'
        )->willReturn(
            $this->_blockMock
        );

        $this->assertNotEmpty($this->_block->getRenderer());
    }

    /**
     * @covers \Magento\Backend\Block\Widget\Grid\Column::getRenderer
     */
    public function testGetRendererWheRendererSetFalse()
    {
        $this->_block->setData('renderer', false);

        $this->_blockMock->expects($this->once())->method('setColumn')->willReturnSelf();

        $this->_layoutMock->expects(
            $this->once()
        )->method(
            'createBlock'
        )->with(
            \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text::class
        )->willReturn(
            $this->_blockMock
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

        $this->_blockMock->expects($this->once())->method('setColumn')->willReturnSelf();

        $this->_layoutMock->expects(
            $this->once()
        )->method(
            'createBlock'
        )->with(
            'StdClass'
        )->willReturn(
            $this->_blockMock
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
        )->willReturn(
            $this->_blockMock
        );

        $this->_block->setFilter('StdClass');

        $grid = new \stdClass();
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
            'urlBuilder' => $this->createMock(\Magento\Backend\Model\Url::class),
            'data' => $groupedData,
        ];

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $block = $objectManagerHelper->getObject(\Magento\Backend\Block\Widget\Grid\Column::class, $arguments);
        $this->assertEquals($expected, $block->isGrouped());
    }

    /**
     * @return array
     */
    public function columnGroupedDataProvider()
    {
        return [[[], false], [['grouped' => 0], false], [['grouped' => 1], true]];
    }

    /**
     * Testing row field export with valid frame callback
     */
    public function testGetRowFieldAndExportWithFrameCallback()
    {
        $row = new DataObject(['id' => '2', 'title' => 'some item']);
        /** @var  $rendererMock */
        $rendererMock = $this->getMockBuilder(AbstractRenderer::class)
            ->disableOriginalConstructor()
            ->setMethods(['renderExport', 'render'])
            ->getMock();

        $rendererMock->expects($this->any())->method('renderExport')->willReturnCallback(
            function (DataObject $row) {
                return $row->getData('title');
            }
        );

        $rendererMock->expects($this->any())->method('render')->willReturnCallback(
            function (DataObject $row) {
                return $row->getData('title');
            }
        );

        $frameCallbackHostObject = $this->getMockBuilder(\Magento\Backend\Block\Widget::class)
            ->disableOriginalConstructor()
            ->setMethods(['decorate'])
            ->getMock();

        $frameCallbackHostObject->expects($this->any())
            ->method('decorate')
            ->willReturnCallback(
                function ($renderValue) {
                    return '__callback_decorated_' . $renderValue;
                }
            );

        $this->_block->setRenderer($rendererMock);
        $this->_block->setFrameCallback([$frameCallbackHostObject, 'decorate']);
        $renderResult = $this->_block->getRowField($row);
        $exportResult = $this->_block->getRowFieldExport($row);
        $this->assertEquals('__callback_decorated_some item', $exportResult);
        $this->assertEquals('__callback_decorated_some item', $renderResult);
    }

    /**
     */
    public function testGetRowFieldExportWithInvalidCallback()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Frame callback host must be instance of Magento\\Backend\\Block\\Widget');

        $row = new DataObject(['id' => '2', 'title' => 'some item']);
        /** @var  $rendererMock */
        $rendererMock = $this->getMockBuilder(AbstractRenderer::class)
            ->disableOriginalConstructor()
            ->setMethods(['renderExport', 'render'])
            ->getMock();

        $rendererMock->expects($this->any())->method('renderExport')->willReturnCallback(
            function (DataObject $row) {
                return $row->getData('title');
            }
        );

        $this->_block->setRenderer($rendererMock);
        $this->_block->setFrameCallback([$this, 'testGetRowFieldExportWithFrameCallback']);
        $this->_block->getRowFieldExport($row);
    }

    /**
     */
    public function testGetRowFieldWithInvalidCallback()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Frame callback host must be instance of Magento\\Backend\\Block\\Widget');

        $row = new DataObject(['id' => '2', 'title' => 'some item']);
        /** @var  $rendererMock */
        $rendererMock = $this->getMockBuilder(AbstractRenderer::class)
            ->disableOriginalConstructor()
            ->setMethods(['render'])
            ->getMock();

        $rendererMock->expects($this->any())->method('render')->willReturnCallback(
            function (DataObject $row) {
                return $row->getData('title');
            }
        );

        $this->_block->setRenderer($rendererMock);
        $this->_block->setFrameCallback([$this, 'testGetRowFieldExportWithFrameCallback']);
        $this->_block->getRowField($row);
    }
}
