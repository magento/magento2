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
 * @package     Magento_Data
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Data\Collection;

class DbTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Data\Collection\Db
     */
    protected $_collection;

    protected function setUp()
    {
        $fetchStrategy = $this->getMockForAbstractClass('Magento\Data\Collection\Db\FetchStrategyInterface');
        $entityFactory = $this->getMock('Magento\Core\Model\EntityFactory', array(), array(), '', false);
        $logger = $this->getMock('Magento\Logger', array(), array(), '', false);
        $this->_collection = new \Magento\Data\Collection\Db($entityFactory, $logger, $fetchStrategy);
    }

    protected function tearDown()
    {
        unset($this->_collection);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Zend_Db_Adapter_Abstract
     */
    public function testSetAddOrder()
    {
        $adapter = $this->getMockForAbstractClass(
            'Zend_Db_Adapter_Abstract', array(), '', false, true, true, array('fetchAll')
        );
        $this->_collection->setConnection($adapter);

        $select = $this->_collection->getSelect();
        $this->assertEmpty($select->getPart(\Zend_Db_Select::ORDER));

        /* Direct access to select object is available and many places are using it for sort order declaration */
        $select->order('select_field', \Magento\Data\Collection::SORT_ORDER_ASC);
        $this->_collection->addOrder('some_field', \Magento\Data\Collection::SORT_ORDER_ASC);
        $this->_collection->setOrder('other_field', \Magento\Data\Collection::SORT_ORDER_ASC);
        $this->_collection->addOrder('other_field', \Magento\Data\Collection::SORT_ORDER_DESC);

        $this->_collection->load();
        $selectOrders = $select->getPart(\Zend_Db_Select::ORDER);
        $this->assertEquals(array('select_field', 'ASC'), array_shift($selectOrders));
        $this->assertEquals('some_field ASC', (string)array_shift($selectOrders));
        $this->assertEquals('other_field DESC', (string)array_shift($selectOrders));
        $this->assertEmpty(array_shift($selectOrders));

        return $adapter;
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject|Zend_Db_Adapter_Abstract $adapter
     * @depends testSetAddOrder
     */
    public function testUnshiftOrder($adapter)
    {
        $this->_collection->setConnection($adapter);
        $this->_collection->addOrder('some_field', \Magento\Data\Collection::SORT_ORDER_ASC);
        $this->_collection->unshiftOrder('other_field', \Magento\Data\Collection::SORT_ORDER_ASC);

        $this->_collection->load();
        $selectOrders = $this->_collection->getSelect()->getPart(\Zend_Db_Select::ORDER);
        $this->assertEquals('other_field ASC', (string)array_shift($selectOrders));
        $this->assertEquals('some_field ASC', (string)array_shift($selectOrders));
        $this->assertEmpty(array_shift($selectOrders));
    }

    /**
     * Test that adding field to filter builds proper sql WHERE condition
     */
    public function testAddFieldToFilter()
    {
        $adapter = $this->getMock('Zend_Db_Adapter_Pdo_Mysql', array('prepareSqlCondition'), array(), '', false);
        $adapter->expects($this->any())
            ->method('prepareSqlCondition')
            ->with($this->stringContains('is_imported'), $this->anything())
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
        $adapter = $this->getMock('Zend_Db_Adapter_Pdo_Mysql', array('prepareSqlCondition'), array(), '', false);
        $adapter->expects($this->at(0))
            ->method('prepareSqlCondition')
            ->with('`weight`', array('in' => array(1, 3)))
            ->will($this->returnValue('weight in (1, 3)'));
        $adapter->expects($this->at(1))
            ->method('prepareSqlCondition')
            ->with('`name`', array('like' => 'M%'))
            ->will($this->returnValue("name like 'M%'"));
        $this->_collection->setConnection($adapter);
        $select = $this->_collection->getSelect()->from("test");

        $this->_collection->addFieldToFilter(
            array('weight', 'name'),
            array(array('in' => array(1, 3)), array('like' => 'M%'))
        );

        $this->assertEquals(
            "SELECT `test`.* FROM `test` WHERE ((weight in (1, 3)) OR (name like 'M%'))",
            $select->assemble()
        );

        $adapter->expects($this->at(0))
            ->method('prepareSqlCondition')
            ->with(
                '`is_imported`',
                $this->anything()
            )
            ->will($this->returnValue('is_imported = 1'));

        $this->_collection->addFieldToFilter('is_imported', array('eq' => '1'));
        $this->assertEquals(
            "SELECT `test`.* FROM `test` WHERE ((weight in (1, 3)) OR (name like 'M%')) AND (is_imported = 1)",
            $select->assemble()
        );
    }

    /**
     * Test that adding field to filter by value which contains question mark produce correct SQL
     */
    public function testAddFieldToFilterValueContainsQuestionMark()
    {
        $adapter = $this->getMock('Zend_Db_Adapter_Pdo_Mysql',
            array('select', 'prepareSqlCondition', 'supportStraightJoin'), array(), '', false
        );
        $adapter->expects($this->once())
            ->method('prepareSqlCondition')
            ->with('`email`', array('like' => 'value?'))
            ->will($this->returnValue('email LIKE \'%value?%\''));
        $adapter->expects($this->once())
            ->method('select')
            ->will($this->returnValue(new \Magento\DB\Select($adapter)));
        $this->_collection->setConnection($adapter);

        $select = $this->_collection->getSelect()->from('test');
        $this->_collection->addFieldToFilter('email', array('like' => 'value?'));
        $this->assertEquals("SELECT `test`.* FROM `test` WHERE (email LIKE '%value?%')", $select->assemble());
    }

    /**
     * Test that field is quoted when added to SQL via addFieldToFilter()
     */
    public function testAddFieldToFilterFieldIsQuoted()
    {
        $adapter = $this->getMock('Zend_Db_Adapter_Pdo_Mysql',
            array('quoteIdentifier', 'prepareSqlCondition'), array(), '', false);
        $adapter->expects($this->once())
            ->method('quoteIdentifier')
            ->with('email')
            ->will($this->returnValue('`email`'));
        $adapter->expects($this->any())
            ->method('prepareSqlCondition')
            ->with($this->stringContains('`email`'), $this->anything())
            ->will($this->returnValue('`email` = "foo@example.com"'));
        $this->_collection->setConnection($adapter);
        $select = $this->_collection->getSelect()->from('test');

        $this->_collection->addFieldToFilter('email', array('eq' => 'foo@example.com'));
        $this->assertEquals('SELECT `test`.* FROM `test` WHERE (`email` = "foo@example.com")', $select->assemble());
    }

    /**
     * Test that after cloning collection $this->_select in initial and cloned collections
     * do not reference the same object
     *
     * @covers \Magento\Data\Collection\Db::__clone
     */
    public function testClone()
    {
        $adapter = $this->getMockForAbstractClass('Zend_Db_Adapter_Abstract', array(), '', false);
        $this->_collection->setConnection($adapter);
        $this->assertInstanceOf('Zend_Db_Select', $this->_collection->getSelect());

        $clonedCollection = clone $this->_collection;

        $this->assertInstanceOf('Zend_Db_Select', $clonedCollection->getSelect());
        $this->assertNotSame($clonedCollection->getSelect(), $this->_collection->getSelect(),
            'Collection was cloned but $this->_select in both initial and cloned collections reference the same object'
        );
    }

    /**
     * @param bool $printQuery
     * @param bool $printFlag
     * @param string $query
     * @param string $expected
     *
     * @dataProvider printLogQueryPrintingDataProvider
     */
    public function testPrintLogQueryPrinting($printQuery, $printFlag, $query, $expected)
    {
        $this->expectOutputString($expected);
        $this->_collection->setFlag('print_query', $printFlag);
        $this->_collection->printLogQuery($printQuery, false, $query);
    }

    public function printLogQueryPrintingDataProvider()
    {
        return array(
            array(false, false, 'some_query', ''),
            array(true,  false, 'some_query', 'some_query'),
            array(false,  true, 'some_query', 'some_query'),
        );
    }

    /**
     * @param bool $logQuery
     * @param bool $logFlag
     * @param int $expectedCalls
     *
     * @dataProvider printLogQueryLoggingDataProvider
     */
    public function testPrintLogQueryLogging($logQuery, $logFlag, $expectedCalls)
    {
        $fetchStrategy = $this->getMock('Magento\Data\Collection\Db\FetchStrategyInterface');
        $entityFactory = $this->getMock('Magento\Core\Model\EntityFactory', array(), array(), '', false);
        $logger = $this->getMock('Magento\Logger', array(), array(), '', false);
        $collection = $this->getMock(
            'Magento\Data\Collection\Db',
            array('_logQuery'),
            array($entityFactory, $logger, $fetchStrategy)
        );
        $collection->setFlag('log_query', $logFlag);
        $collection->expects($this->exactly($expectedCalls))->method('_logQuery');
        $collection->printLogQuery(false, $logQuery, 'some_query');
    }

    public function printLogQueryLoggingDataProvider()
    {
        return array(
            array(true, false, 1),
            array(false, true, 1),
            array(false, false, 0),
        );
    }
}
