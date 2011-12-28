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
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Varien_Data_Collection_DbTest extends PHPUnit_Framework_TestCase
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
        $this->_collection->addOrder('some_field', Varien_Data_Collection::SORT_ORDER_ASC);
        $this->_collection->setOrder('other_field', Varien_Data_Collection::SORT_ORDER_ASC);
        $this->_collection->addOrder('other_field', Varien_Data_Collection::SORT_ORDER_DESC);

        $this->_collection->load();
        $selectOrders = $select->getPart(Zend_Db_Select::ORDER);
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
     * Create an adapter mock object
     *
     * @param string $adapterClass
     * @param array $mockMethods
     * @param array|null $constructArgs
     * @param string $mockStatementMethods
     * @return Zend_Db_Adapter_Abstract|PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getAdapterMock($adapterClass, $mockMethods, $constructArgs = array(),
        $mockStatementMethods = 'execute'
    ) {
        if (null == $constructArgs) {
            $adapter = $this->getMock($adapterClass, $mockMethods, array(), '', false);
        } else {
            $adapter = $this->getMock($adapterClass, $mockMethods, $constructArgs);
        }
        if (null !== $mockStatementMethods) {
            $statement = $this->getMock('Zend_Db_Statement', array_merge((array)$mockStatementMethods,
                    array('closeCursor', 'columnCount', 'errorCode', 'errorInfo', 'fetch', 'nextRowset', 'rowCount')
                ), array(), '', false
            );
            $adapter->expects($this->any())
                    ->method('query')
                    ->will($this->returnValue($statement));
        }
        return $adapter;
    }
}
