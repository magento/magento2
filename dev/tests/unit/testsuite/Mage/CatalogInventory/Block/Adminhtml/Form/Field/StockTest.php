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
 * @package     Mage_Catalog
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_CatalogInventory_Block_Adminhtml_Form_Field_StockTest extends PHPUnit_Framework_TestCase
{
    const ATTRIBUTE_NAME = 'quantity_and_stock_status';

    /**
     * @var Mage_CatalogInventory_Block_Adminhtml_Form_Field_Stock
     */
    protected $_model;

    /**
     * @var Varien_Data_Form_Element_Text
     */
    protected $_qty;

    protected function setUp()
    {
        $this->_qty = $this->getMock('Varien_Data_Form_Element_Text',
            array('getElementHtml', 'setForm', 'setValue', 'setName')
        );
        $this->_model = $this->getMock('Mage_CatalogInventory_Block_Adminhtml_Form_Field_Stock',
            array('getElementHtml'), array(array('qty' => $this->_qty, 'name' => self::ATTRIBUTE_NAME)));
    }

    public function testGetElementHtml()
    {
        $this->_qty->expects($this->once())->method('getElementHtml')->will($this->returnValue('html'));
        $this->_model->expects($this->once())->method('getElementHtml')
            ->will($this->returnValue($this->_qty->getElementHtml()));
        $this->assertEquals('html', $this->_model->getElementHtml());
    }

    public function testSetForm()
    {
        $this->_qty->expects($this->once())->method('setForm')
            ->with($this->isInstanceOf('Varien_Data_Form_Element_Abstract'));
        $this->_model->setForm(new Varien_Data_Form_Element_Text());
    }

    public function testSetValue()
    {
        $value = array('qty' => 1, 'is_in_stock' => 0);
        $this->_qty->expects($this->once())->method('setValue')->with($this->equalTo(1));
        $this->_model->setValue($value);
    }

    public function testSetName()
    {
        $this->_qty->expects($this->once())->method('setName')->with(self::ATTRIBUTE_NAME . '[qty]');
        $this->_model->setName(self::ATTRIBUTE_NAME);
    }
}
