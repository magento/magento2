<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Widget\Test\Unit\Model\ResourceModel\Layout;

use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Widget\Model\ResourceModel\Layout\Update\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Abstract test case for layout resource model tests
 */
abstract class AbstractTestCase extends TestCase
{
    /**
     * Test 'where' condition for assertion
     */
    public const TEST_WHERE_CONDITION = 'condition = 1';

    /**
     * Test interval in days
     */
    public const TEST_DAYS_BEFORE = 3;

    /**
     * Collection instance
     *
     * @var Collection
     */
    protected $collection;

    /**
     * Name of main table alias
     *
     * @var string
     */
    protected $tableAlias = 'main_table';

    /**
     * Expected conditions for testAddUpdatedDaysBeforeFilter
     *
     * @var array
     */
    protected $expectedConditions = [];

    /**
     * Set up test environment
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->expectedConditions = [
            'counter' => 0,
            'data' => [
                0 => [$this->tableAlias . '.updated_at', ['notnull' => true]],
                1 => [$this->tableAlias . '.updated_at', ['lt' => 'date']],
            ],
        ];
    }

    /**
     * Retrieve resource model instance
     *
     * @param Select $select Select object
     *
     * @return MockObject
     */
    protected function getResource(Select $select)
    {
        $connection = $this->createMock(Mysql::class);
        $connection->expects($this->once())
            ->method('select')
            ->willReturn($select);
        $connection->expects($this->any())
            ->method('quoteIdentifier')
            ->willReturnArgument(0);

        $resource = $this->createPartialMock(
            AbstractDb::class,
            [
                'getConnection',
                'getMainTable',
                'getTable',
                '__wakeup',
                '_construct'
            ]
        );
        $resource->expects($this->any())
            ->method('getConnection')
            ->willReturn($connection);
        $resource->expects($this->any())
            ->method('getTable')
            ->willReturnArgument(0);

        return $resource;
    }

    /**
     * Get collection instance
     *
     * @param Select $select Select object
     *
     * @return AbstractCollection
     */
    abstract protected function getCollection(Select $select);

    /**
     * Test add updated days before filter
     *
     * @return void
     */
    public function testAddUpdatedDaysBeforeFilter()
    {
        $select = $this->createMock(Select::class);
        $select->expects($this->any())
            ->method('where')
            ->with(self::TEST_WHERE_CONDITION);

        $collection = $this->getCollection($select);

        /**
         * Mock connection object
         *
         * @var \PHPUnit\Framework\MockObject\MockObject $connection
         */
        $connection = $collection->getResource()->getConnection();
        $connection->expects($this->any())
            ->method('prepareSqlCondition')
            ->willReturnCallback(
                [$this, 'verifyPrepareSqlCondition']
            );

        // expected date without time
        $datetime = new \DateTime('now', new \DateTimeZone('UTC'));
        $storeInterval = new \DateInterval('P' . self::TEST_DAYS_BEFORE . 'D');
        $datetime->sub($storeInterval);
        $dateTimeLib = new \Magento\Framework\Stdlib\DateTime();
        $expectedDate = $dateTimeLib->formatDate($datetime->getTimestamp());
        $this->expectedConditions['data'][1][1]['lt'] = $expectedDate;

        $collection->addUpdatedDaysBeforeFilter(self::TEST_DAYS_BEFORE);
    }

    /**
     * Assert SQL condition
     *
     * @param string $fieldName Field name
     * @param array  $condition Condition array
     *
     * @return string
     */
    public function verifyPrepareSqlCondition($fieldName, $condition)
    {
        $counter = $this->expectedConditions['counter'];
        $data = $this->expectedConditions['data'][$counter];
        $this->expectedConditions['counter']++;

        $this->assertEquals($data[0], $fieldName);

        $this->assertCount(1, $data[1]);
        $key = array_keys($data[1]);
        $key = reset($key);
        $value = reset($data[1]);

        $this->assertArrayHasKey($key, $condition);

        if ($key == 'lt') {
            $this->assertStringContainsString($value, $condition[$key]);
        } else {
            $this->assertContains($value, $condition);
        }

        return self::TEST_WHERE_CONDITION;
    }
}
