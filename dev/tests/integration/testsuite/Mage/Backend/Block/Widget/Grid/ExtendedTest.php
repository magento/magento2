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
 * @category    Magento
 * @package     Mage_Backend
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @magentoAppArea adminhtml
 */
class Mage_Backend_Block_Widget_Grid_ExtendedTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Backend_Block_Widget_Grid_Extended
     */
    protected $_block;

    /**
     * @var Mage_Core_Model_Layout
     */
    protected $_layoutMock;

    protected function setUp()
    {
        parent::setUp();

        $this->_layoutMock = Mage::getModel('Mage_Core_Model_Layout');
        $context = Mage::getModel('Mage_Backend_Block_Template_Context', array('layout' => $this->_layoutMock));
        $this->_block = $this->_layoutMock->createBlock(
            'Mage_Backend_Block_Widget_Grid_Extended', 'grid', array('context' => $context)
        );

        $this->_block->addColumn('column1',
            array('id' => 'columnId1')
        );
        $this->_block->addColumn('column2',
            array('id' => 'columnId2')
        );
    }

    public function testAddColumnAddsChildToColumnSet()
    {
        $this->assertInstanceOf(
            'Mage_Backend_Block_Widget_Grid_Column',
            $this->_block->getColumnSet()->getChildBlock('column1')
        );
        $this->assertCount(2, $this->_block->getColumnSet()->getChildNames());
    }

    public function testRemoveColumn()
    {
        $this->assertCount(2, $this->_block->getColumnSet()->getChildNames());
        $this->_block->removeColumn('column1');
        $this->assertCount(1, $this->_block->getColumnSet()->getChildNames());
    }

    public function testSortColumnsByOrder()
    {
        $columnNames = $this->_block->getLayout()->getChildNames($this->_block->getColumnSet()->getNameInLayout());
        $this->assertEquals($this->_block->getColumn('column1')->getNameInLayout(), $columnNames[0]);
        $this->_block->addColumnsOrder('column1', 'column2');
        $this->_block->sortColumnsByOrder();
        $columnNames = $this->_block->getLayout()->getChildNames($this->_block->getColumnSet()->getNameInLayout());
        $this->assertEquals($this->_block->getColumn('column2')->getNameInLayout(), $columnNames[0]);
    }

    public function testGetMainButtonsHtmlReturnsEmptyStringIfFiltersArentVisible()
    {
        $this->_block->setFilterVisibility(false);
        $this->assertEquals('', $this->_block->getMainButtonsHtml());
    }
}
