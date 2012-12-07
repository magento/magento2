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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Catalog_Model_Product_Attribute_Backend_StockTest extends PHPUnit_Framework_TestCase
{
    const ATTRIBUTE_NAME = 'quantity_and_stock_status';

    /**
     * @var Mage_Catalog_Model_Product_Attribute_Backend_Stock
     */
    protected $_model;

    /**
     * @var Mage_CatalogInventory_Model_Stock_Item
     */
    protected $_inventory;

    protected function setUp()
    {
        $this->_inventory = $this->getMock('Mage_CatalogInventory_Model_Stock_Item',
            array('getIsInStock', 'getQty', 'loadByProduct'), array(), '', false);
        $this->_model = $this->getMock('Mage_Catalog_Model_Product_Attribute_Backend_Stock', array('getAttribute'),
            array(array('inventory' => $this->_inventory))
        );
        $attribute = $this->getMock('Varien_Object', array('getAttributeCode'));
        $attribute->expects($this->atLeastOnce())->method('getAttributeCode')
            ->will($this->returnValue(self::ATTRIBUTE_NAME));
        $this->_model->expects($this->atLeastOnce())->method('getAttribute')->will($this->returnValue($attribute));
    }

    public function testAfterLoad()
    {
        $this->_inventory->expects($this->once())->method('getIsInStock')->will($this->returnValue(1));
        $this->_inventory->expects($this->once())->method('getQty')->will($this->returnValue(5));
        $object = new Varien_Object();
        $this->_model->afterLoad($object);
        $data = $object->getData();
        $this->assertEquals(1, $data[self::ATTRIBUTE_NAME]['is_in_stock']);
        $this->assertEquals(5, $data[self::ATTRIBUTE_NAME]['qty']);
    }

    public function testBeforeSave()
    {
        $object = new Varien_Object(array(
            self::ATTRIBUTE_NAME => array('is_in_stock' => 1, 'qty' => 5),
            'stock_data'         => array('is_in_stock' => 2, 'qty' => 2)
        ));
        $stockData = $object->getStockData();
        $this->assertEquals(2, $stockData['is_in_stock']);
        $this->assertEquals(2, $stockData['qty']);
        $this->assertNotEmpty($object->getData(self::ATTRIBUTE_NAME));

        $this->_model->beforeSave($object);

        $stockData = $object->getStockData();
        $this->assertEquals(1, $stockData['is_in_stock']);
        $this->assertEquals(5, $stockData['qty']);
        $this->assertNull($object->getData(self::ATTRIBUTE_NAME));
    }

    public function testBeforeSaveQtyIsEmpty()
    {
        $object = new Varien_Object(array(
            self::ATTRIBUTE_NAME => array('is_in_stock' => 1, 'qty' => ''),
            'stock_data'         => array('is_in_stock' => 2, 'qty' => '')
        ));

        $this->_model->beforeSave($object);

        $stockData = $object->getStockData();
        $this->assertNull($stockData['qty']);
    }

    public function testBeforeSaveQtyIsZero()
    {
        $object = new Varien_Object(array(
            self::ATTRIBUTE_NAME => array('is_in_stock' => 1, 'qty' => 0),
            'stock_data'         => array('is_in_stock' => 2, 'qty' => 0)
        ));

        $this->_model->beforeSave($object);

        $stockData = $object->getStockData();
        $this->assertEquals(0, $stockData['qty']);
    }
}
