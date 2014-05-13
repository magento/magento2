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
            array('setSortable', 'setRendererType', 'setFilterType', 'addHeaderCssClass', 'setGrid'),
            array(),
            '',
            false
        );
        $this->_layoutMock = $this->getMock('Magento\Framework\View\Layout', array(), array(), '', false);
        $this->_layoutMock->expects(
            $this->any()
        )->method(
            'getChildBlocks'
        )->will(
            $this->returnValue(array($this->_columnMock))
        );

        $context = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\View\Element\Template\Context',
            array('layout' => $this->_layoutMock)
        );
        $this->_block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\Backend\Block\Widget\Grid\ColumnSet',
            '',
            array('context' => $context)
        );
        $this->_block->setTemplate(null);
    }

    public function testBeforeToHtmlAddsClassToLastColumn()
    {
        $this->_columnMock->expects($this->any())->method('addHeaderCssClass')->with($this->equalTo('last'));
        $this->_block->toHtml();
    }
}
