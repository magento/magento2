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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Backend_Block_Widget_GridTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Backend_Block_Widget_Grid_ColumnSet
     */
    protected $_block;

    /**
     * @var Mage_Core_Model_Layout|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_layoutMock;

    /**
     * @var Mage_Backend_Block_Widget_Grid_ColumnSet|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_columnSetMock;

    /**
     * List of block injection classes
     *
     * @var array
     */
    protected $_blockInjections = array(
        'Mage_Core_Controller_Request_Http',
        'Mage_Core_Model_Layout',
        'Mage_Core_Model_Event_Manager',
        'Mage_Backend_Model_Url',
        'Mage_Core_Model_Translate',
        'Mage_Core_Model_Cache',
        'Mage_Core_Model_Design_Package',
        'Mage_Core_Model_Session',
        'Mage_Core_Model_Store_Config',
        'Mage_Core_Controller_Varien_Front',
        'Mage_Core_Model_Factory_Helper',
        'Mage_Backend_Helper_Data',
        'Mage_Backend_Model_Widget_Grid_Row_UrlGeneratorFactory',
    );

    protected function setUp()
    {
        $this->_layoutMock = $this->getMock('Mage_Core_Model_Layout', array(), array(), '', false);
        $this->_columnSetMock = $this->getMock(
            'Mage_Backend_Block_Widget_Grid_ColumnSet', array(), $this->_prepareConstructorArguments()
        );

        $returnValueMap = array(
            array('grid', 'grid.columnSet', 'grid.columnSet'),
            array('grid', 'reset_filter_button', 'reset_filter_button'),
            array('grid', 'search_button', 'search_button')
        );
        $this->_layoutMock->expects($this->any())->method('getChildName')
            ->will($this->returnValueMap($returnValueMap));
        $this->_layoutMock->expects($this->any())->method('getBlock')
            ->with('grid.columnSet')
            ->will($this->returnValue($this->_columnSetMock));
        $this->_layoutMock->expects($this->any())->method('createBlock')
            ->with('Mage_Backend_Block_Widget_Button')
            ->will($this->returnValue(Mage::app()->getLayout()->createBlock('Mage_Backend_Block_Widget_Button')));

        $this->_block = Mage::app()->getLayout()->createBlock('Mage_Backend_Block_Widget_Grid');
        $this->_block->setLayout($this->_layoutMock);
        $this->_block->setNameInLayout('grid');
    }

    /**
     * List of block constructor arguments
     *
     * @return array
     */
    protected function _prepareConstructorArguments()
    {
        $arguments = array();
        foreach ($this->_blockInjections as $injectionClass) {
            $arguments[] = Mage::getModel($injectionClass);
        }
        return $arguments;
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
}
