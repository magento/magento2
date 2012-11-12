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

class Mage_Backend_Block_Widget_Grid_ColumnSetTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Backend_Block_Widget_Grid_ColumnSet
     */
    protected $_block;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_layoutMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_columnMock;

    protected function setUp()
    {
        $this->_columnMock = $this->getMock('Mage_Backend_Block_Widget_Grid_Column',
            array('setSortable', 'setRendererType', 'setFilterType', 'addHeaderCssClass', 'setGrid'),
            array(), '', false
        );
        $this->_layoutMock = $this->getMock('Mage_Core_Model_Layout', array(), array(), '', false);
        $this->_layoutMock->expects($this->any())->method('getChildBlocks')->will($this->returnValue(
            array($this->_columnMock)
        ));

        $this->_block = Mage::app()->getLayout()->createBlock(
            'Mage_Backend_Block_Widget_Grid_ColumnSet', '', array('layout' => $this->_layoutMock)
        );
    }

    public function testBeforeToHtmlAddsClassToLastColumn()
    {
        $this->_columnMock->expects($this->any())->method('addHeaderCssClass')->with($this->equalTo('last'));
        $this->_block->toHtml();
    }
}
