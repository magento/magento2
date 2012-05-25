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
 * @category    Varien
 * @package     Varien_Data
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Varien_Data_Collection_DbTest extends Magento_Test_TestCase_ZendDbAdapterAbstract
{
    /**
     * @var Varien_Data_Collection_Db
     */
    protected $_collection;

    protected function setUp()
    {
        $this->_collection = new Varien_Data_Collection_Db;
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|Zend_Db_Adapter_Abstract
     */
    public function testSetAddOrder()
    {
        $adapter = $this->_getAdapterMock('Zend_Db_Adapter_Pdo_Mysql', array('fetchAll'), null);
        $this->_collection->setConnection($adapter);

        $select = $this->_collection->getSelect();
        $this->assertEmpty($select->getPart(Zend_Db_Select::ORDER));

        /* Direct access to select object is available and many places are using it for sort order declaration */
        $select->order('select_field', Varien_Data_Collection::SORT_ORDER_ASC);
        $this->_collection->addOrder('some_field', Varien_Data_Collection::SORT_ORDER_ASC);
        $this->_collection->setOrder('other_field', Varien_Data_Collection::SORT_ORDER_ASC);
        $this->_collection->addOrder('other_field', Varien_Data_Collection::SORT_ORDER_DESC);

        $this->_collection->load();
        $selectOrders = $select->getPart(Zend_Db_Select::ORDER);
        $this->assertEquals(array('select_field', 'ASC'), array_shift($selectOrders));
        $this->assertEquals('some_field ASC', (string)array_shift($selectOrders));
        $this->assertEquals('other_field DESC', (string)array_shift($selectOrders));
        $this->assertEmpty(array_shift($selectOrders));

        return $adapter;
    }

    /**
     * @param PHPUnit_Framework_MockObject_MockObject|Zend_Db_Adapter_Abstract $adapter
     * @depends testSetAddOrder
     */
    public function testUnshiftOrder($adapter)
    {
        $this->_collection->setConnection($adapter);
        $this->_collection->addOrder('some_field', Varien_Data_Collection::SORT_ORDER_ASC);
        $this->_collection->unshiftOrder('other_field', Varien_Data_Collection::SORT_ORDER_ASC);

        $this->_collection->load();
        $selectOrders = $this->_collection->getSelect()->getPart(Zend_Db_Select::ORDER);
        $this->assertEquals('other_field ASC', (string)array_shift($selectOrders));
        $this->assertEquals('some_field ASC', (string)array_shift($selectOrders));
        $this->assertEmpty(array_shift($selectOrders));
    }

    /**
     * Test that adding field to filter builds proper sql WHERE condition
     */
    public function testAddFieldToFilter()
    {
        $adapter =$this->_getAdapterMock(
            'Zend_Db_Adapter_Pdo_Mysql',
            array('fetchAll', 'prepareSqlCondition'),
            null
        );
        $adapter->expects($this->any())
            ->method('prepareSqlCondition')
            ->with(
                $this->stringContains('is_imported'),
                $this->anything()
            )
            ->will($this->returnValue('is_imported = 1'));
        $this->_collection->setConnection($adapter);
        $select = $this->_collection->getSelect()->from('test');

        $this->_collection->addFieldToFilter('is_imported', array('eq' => '1'));
        $this->assertEquals('SELECT `test`.* FROM `test` WHERE (is_imported = 1)', $select->assemble());
    }

    /**
     * Test that adding multiple fields to filter at once
     * builds proper sql WHERE condition and created conditions are joined with OR
     */
    public function testAddFieldToFilterWithMultipleParams()
    {
        $adapter = $this->_getAdapterMock(
            'Zend_Db_Adapter_Pdo_Mysql',
            array('fetchAll', 'prepareSqlCondition'),
            null
        );
        $adapter->expects($this->at(0))
            ->method('prepareSqlCondition')
            ->with(
                'weight',
                array('in' => array(1,3))
            )
            ->will($this->returnValue('weight in (1, 3)'));
        $adapter->expects($this->at(1))
            ->method('prepareSqlCondition')
            ->with(
                'name',
                array('like' => 'M%')
            )
            ->will($this->returnValue("name like 'M%'"));
        $this->_collection->setConnection($adapter);
        $select = $this->_collection->getSelect()->from("test");

        $this->_collection->addFieldToFilter(
            array('weight', 'name'),
            array(array('in' => array(1,3)), array('like' => 'M%'))
        );

        $this->assertEquals(
            "SELECT `test`.* FROM `test` WHERE ((weight in (1, 3)) OR (name like 'M%'))",
            $select->assemble()
        );

        $adapter->expects($this->at(0))
            ->method('prepareSqlCondition')
            ->with(
                'is_imported',
                $this->anything()
            )
            ->will($this->returnValue('is_imported = 1'));

        $this->_collection->addFieldToFilter('is_imported', array('eq' => '1'));
        $this->assertEquals(
            "SELECT `test`.* FROM `test` WHERE ((weight in (1, 3)) OR (name like 'M%')) AND (is_imported = 1)",
            $select->assemble()
        );
    }
}
