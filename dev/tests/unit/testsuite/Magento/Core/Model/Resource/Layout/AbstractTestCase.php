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
namespace Magento\Core\Model\Resource\Layout;

abstract class AbstractTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * Test 'where' condition for assertion
     */
    const TEST_WHERE_CONDITION = 'condition = 1';

    /**
     * Test interval in days
     */
    const TEST_DAYS_BEFORE = 3;

    /**
     * @var \Magento\Core\Model\Resource\Layout\Update\Collection
     */
    protected $_collection;

    /**
     * Name of main table alias
     *
     * @var string
     */
    protected $_tableAlias = 'main_table';

    /**
     * Expected conditions for testAddUpdatedDaysBeforeFilter
     *
     * @var array
     */
    protected $_expectedConditions = array();

    protected function setUp()
    {
        $this->_expectedConditions = array(
            'counter' => 0,
            'data' => array(
                0 => array($this->_tableAlias . '.updated_at', array('notnull' => true)),
                1 => array($this->_tableAlias . '.updated_at', array('lt' => 'date'))
            )
        );
    }

    /**
     * Retrieve resource model instance
     *
     * @param \Zend_Db_Select $select
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getResource(\Zend_Db_Select $select)
    {
        $connection = $this->getMock('Magento\Framework\DB\Adapter\Pdo\Mysql', array(), array(), '', false);
        $connection->expects($this->once())->method('select')->will($this->returnValue($select));
        $connection->expects($this->any())->method('quoteIdentifier')->will($this->returnArgument(0));

        $resource = $this->getMockForAbstractClass(
            'Magento\Framework\Model\Resource\Db\AbstractDb',
            array(),
            '',
            false,
            true,
            true,
            array('getReadConnection', 'getMainTable', 'getTable', '__wakeup')
        );
        $resource->expects($this->any())->method('getReadConnection')->will($this->returnValue($connection));
        $resource->expects($this->any())->method('getTable')->will($this->returnArgument(0));

        return $resource;
    }

    /**
     * @abstract
     * @param \Zend_Db_Select $select
     * @return \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection
     */
    abstract protected function _getCollection(\Zend_Db_Select $select);

    public function testAddUpdatedDaysBeforeFilter()
    {
        $select = $this->getMock('Zend_Db_Select', array(), array(), '', false);
        $select->expects($this->any())->method('where')->with(self::TEST_WHERE_CONDITION);

        $collection = $this->_getCollection($select);

        /** @var $connection \PHPUnit_Framework_MockObject_MockObject */
        $connection = $collection->getResource()->getReadConnection();
        $connection->expects(
            $this->any()
        )->method(
            'prepareSqlCondition'
        )->will(
            $this->returnCallback(array($this, 'verifyPrepareSqlCondition'))
        );

        // expected date without time
        $datetime = new \DateTime();
        $storeInterval = new \DateInterval('P' . self::TEST_DAYS_BEFORE . 'D');
        $datetime->sub($storeInterval);
        $dateTimeLib = new \Magento\Framework\Stdlib\DateTime();
        $expectedDate = $dateTimeLib->formatDate($datetime->getTimestamp());
        $this->_expectedConditions['data'][1][1]['lt'] = $expectedDate;

        $collection->addUpdatedDaysBeforeFilter(self::TEST_DAYS_BEFORE);
    }

    /**
     * Assert SQL condition
     *
     * @param string $fieldName
     * @param array $condition
     * @return string
     */
    public function verifyPrepareSqlCondition($fieldName, $condition)
    {
        $counter = $this->_expectedConditions['counter'];
        $data = $this->_expectedConditions['data'][$counter];
        $this->_expectedConditions['counter']++;

        $this->assertEquals($data[0], $fieldName);

        $this->assertCount(1, $data[1]);
        $key = array_keys($data[1]);
        $key = reset($key);
        $value = reset($data[1]);

        $this->assertArrayHasKey($key, $condition);

        if ($key == 'lt') {
            $this->assertContains($value, $condition[$key]);
        } else {
            $this->assertContains($value, $condition);
        }

        return self::TEST_WHERE_CONDITION;
    }
}
