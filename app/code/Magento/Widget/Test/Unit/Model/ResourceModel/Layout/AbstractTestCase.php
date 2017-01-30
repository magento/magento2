<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Test\Unit\Model\ResourceModel\Layout;

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
     * @var \Magento\Widget\Model\ResourceModel\Layout\Update\Collection
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
    protected $_expectedConditions = [];

    protected function setUp()
    {
        $this->_expectedConditions = [
            'counter' => 0,
            'data' => [
                0 => [$this->_tableAlias . '.updated_at', ['notnull' => true]],
                1 => [$this->_tableAlias . '.updated_at', ['lt' => 'date']],
            ],
        ];
    }

    /**
     * Retrieve resource model instance
     *
     * @param \Magento\Framework\DB\Select $select
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getResource(\Magento\Framework\DB\Select $select)
    {
        $connection = $this->getMock('Magento\Framework\DB\Adapter\Pdo\Mysql', [], [], '', false);
        $connection->expects($this->once())->method('select')->will($this->returnValue($select));
        $connection->expects($this->any())->method('quoteIdentifier')->will($this->returnArgument(0));

        $resource = $this->getMockForAbstractClass(
            'Magento\Framework\Model\ResourceModel\Db\AbstractDb',
            [],
            '',
            false,
            true,
            true,
            ['getConnection', 'getMainTable', 'getTable', '__wakeup']
        );
        $resource->expects($this->any())->method('getConnection')->will($this->returnValue($connection));
        $resource->expects($this->any())->method('getTable')->will($this->returnArgument(0));

        return $resource;
    }

    /**
     * @abstract
     * @param \Magento\Framework\DB\Select $select
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    abstract protected function _getCollection(\Magento\Framework\DB\Select $select);

    public function testAddUpdatedDaysBeforeFilter()
    {
        $select = $this->getMock('Magento\Framework\DB\Select', [], [], '', false);
        $select->expects($this->any())->method('where')->with(self::TEST_WHERE_CONDITION);

        $collection = $this->_getCollection($select);

        /** @var $connection \PHPUnit_Framework_MockObject_MockObject */
        $connection = $collection->getResource()->getConnection();
        $connection->expects(
            $this->any()
        )->method(
            'prepareSqlCondition'
        )->will(
            $this->returnCallback([$this, 'verifyPrepareSqlCondition'])
        );

        // expected date without time
        $datetime = new \DateTime('now', new \DateTimeZone('UTC'));
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
