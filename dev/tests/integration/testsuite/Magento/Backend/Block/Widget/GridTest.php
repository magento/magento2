<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Backend\Block\Widget;

/**
 * @magentoAppArea adminhtml
 */
class GridTest extends \PHPUnit_Framework_TestCase
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
        $this->_layoutMock = $this->getMock('Magento\Framework\View\Layout', array(), array(), '', false);
        $this->_columnSetMock = $this->_getColumnSetMock();

        $returnValueMap = array(
            array('grid', 'grid.columnSet', 'grid.columnSet'),
            array('grid', 'reset_filter_button', 'reset_filter_button'),
            array('grid', 'search_button', 'search_button')
        );
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
            'Magento\Backend\Block\Widget\Button'
        )->will(
            $this->returnValue(
                \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                    'Magento\Framework\View\LayoutInterface'
                )->createBlock(
                    'Magento\Backend\Block\Widget\Button'
                )
            )
        );
        $this->_layoutMock->expects(
            $this->any()
        )->method(
            'helper'
        )->with(
            'Magento\Core\Helper\Data'
        )->will(
            $this->returnValue(
                \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Core\Helper\Data')
            )
        );


        $this->_block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\Backend\Block\Widget\Grid'
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
            'Magento\Framework\App\Filesystem\DirectoryList',
            array('root' => __DIR__)
        );
        return $this->getMock(
            'Magento\Backend\Block\Widget\Grid\ColumnSet',
            array(),
            array(
                $objectManager->create(
                    'Magento\Framework\View\Element\Template\Context',
                    array(
                        'filesystem' => $objectManager->create(
                            '\Magento\Framework\App\Filesystem',
                            array('directoryList' => $directoryList)
                        )
                    )
                ),
                $objectManager->create('Magento\Backend\Model\Widget\Grid\Row\UrlGeneratorFactory'),
                $objectManager->create('Magento\Backend\Model\Widget\Grid\SubTotals'),
                $objectManager->create('Magento\Backend\Model\Widget\Grid\Totals')
            )
        );
    }

    public function testToHtmlPreparesColumns()
    {
        $this->_columnSetMock->expects($this->once())->method('setRendererType');
        $this->_columnSetMock->expects($this->once())->method('setFilterType');
        $this->_columnSetMock->expects($this->once())->method('setSortable');
        $this->_block->setColumnRenderers(array('filter' => 'Filter_Class'));
        $this->_block->setColumnFilters(array('filter' => 'Filter_Class'));
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
            'Magento\Framework\View\LayoutInterface'
        );
        /** @var $block \Magento\Backend\Block\Widget\Grid */
        $block = $layout->createBlock('Magento\Backend\Block\Widget\Grid\Extended', 'block');
        $child = $layout->addBlock('Magento\Framework\View\Element\Template', 'massaction', 'block');
        $this->assertSame($child, $block->getMassactionBlock());
    }
}
