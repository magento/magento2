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
class ExtendedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected $_block;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $_layoutMock;

    protected function setUp()
    {
        parent::setUp();

        $this->_layoutMock = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        );
        $context = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Backend\Block\Template\Context',
            array('layout' => $this->_layoutMock)
        );
        $this->_block = $this->_layoutMock->createBlock(
            'Magento\Backend\Block\Widget\Grid\Extended',
            'grid',
            array('context' => $context)
        );

        $this->_block->addColumn('column1', array('id' => 'columnId1'));
        $this->_block->addColumn('column2', array('id' => 'columnId2'));
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testAddColumnAddsChildToColumnSet()
    {
        $this->assertInstanceOf(
            'Magento\Backend\Block\Widget\Grid\Column',
            $this->_block->getColumnSet()->getChildBlock('column1')
        );
        $this->assertCount(2, $this->_block->getColumnSet()->getChildNames());
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testRemoveColumn()
    {
        $this->assertCount(2, $this->_block->getColumnSet()->getChildNames());
        $this->_block->removeColumn('column1');
        $this->assertCount(1, $this->_block->getColumnSet()->getChildNames());
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testSortColumnsByOrder()
    {
        $columnNames = $this->_block->getLayout()->getChildNames($this->_block->getColumnSet()->getNameInLayout());
        $this->assertEquals($this->_block->getColumn('column1')->getNameInLayout(), $columnNames[0]);
        $this->_block->addColumnsOrder('column1', 'column2');
        $this->_block->sortColumnsByOrder();
        $columnNames = $this->_block->getLayout()->getChildNames($this->_block->getColumnSet()->getNameInLayout());
        $this->assertEquals($this->_block->getColumn('column2')->getNameInLayout(), $columnNames[0]);
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testGetMainButtonsHtmlReturnsEmptyStringIfFiltersArentVisible()
    {
        $this->_block->setFilterVisibility(false);
        $this->assertEquals('', $this->_block->getMainButtonsHtml());
    }
}
