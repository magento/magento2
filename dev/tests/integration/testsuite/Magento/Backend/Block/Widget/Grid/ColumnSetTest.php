<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget\Grid;

/**
 * @magentoAppArea adminhtml
 */
class ColumnSetTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Block\Widget\Grid\ColumnSet
     */
    protected $_block;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_layoutMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_columnMock;

    protected function setUp()
    {
        parent::setUp();

        $this->_columnMock = $this->getMock(
            'Magento\Backend\Block\Widget\Grid\Column',
            ['setSortable', 'setRendererType', 'setFilterType', 'addHeaderCssClass', 'setGrid'],
            [],
            '',
            false
        );
        $this->_layoutMock = $this->getMock('Magento\Framework\View\Layout', [], [], '', false);
        $this->_layoutMock->expects(
            $this->any()
        )->method(
            'getChildBlocks'
        )->will(
            $this->returnValue([$this->_columnMock])
        );

        $context = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\View\Element\Template\Context',
            ['layout' => $this->_layoutMock]
        );
        $this->_block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\Backend\Block\Widget\Grid\ColumnSet',
            '',
            ['context' => $context]
        );
        $this->_block->setTemplate(null);
    }

    public function testBeforeToHtmlAddsClassToLastColumn()
    {
        $this->_columnMock->expects($this->any())->method('addHeaderCssClass')->with($this->equalTo('last'));
        $this->_block->toHtml();
    }
}
