<?php
/***
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Variable\Test\Unit\Model\ResourceModel\Variable;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Variable\Model\ResourceModel\Variable\Collection;
use PHPUnit\Framework\TestCase;

/**
 * Provide tests for Variable collection class.
 */
class CollectionTest extends TestCase
{
    /**
     * Test Collection::addValuesToResult() build correct query.
     *
     * @return void
     */
    public function testAddValuesToResult()
    {
        $mainTableName = 'testMainTable';
        $tableName = 'variable_value';
        $field = 'value_table.store_id';

        $select = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $select->expects($this->once())
            ->method('from')
            ->with($this->identicalTo(['main_table' => $mainTableName]))
            ->willReturnSelf();
        $select->expects($this->once())
            ->method('join')
            ->with(
                $this->identicalTo(['value_table' => $tableName]),
                $this->identicalTo('value_table.variable_id = main_table.variable_id'),
                $this->identicalTo(['value_table.plain_value', 'value_table.html_value'])
            )->willReturnSelf();

        $connection = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['select', 'prepareSqlCondition', 'quoteIdentifier'])
            ->getMockForAbstractClass();
        $connection->expects($this->any())
            ->method('select')
            ->willReturn($select);
        $connection->expects($this->once())
            ->method('quoteIdentifier')
            ->with($this->identicalTo($field))
            ->willReturn($field);
        $connection->expects($this->once())
            ->method('prepareSqlCondition')
            ->with(
                $this->identicalTo($field),
                $this->identicalTo(['eq' => 0])
            )->willReturn('testResultCondition');

        $resource = $this->getMockBuilder(AbstractDb::class)
            ->setMethods(['getTable', 'getMainTable', 'getConnection'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $resource->expects($this->any())
            ->method('getConnection')
            ->willReturn($connection);
        $resource->expects($this->once())
            ->method('getMainTable')
            ->willReturn('testMainTable');
        $resource->expects($this->exactly(2))
            ->method('getTable')
            ->withConsecutive(
                [$mainTableName],
                [$tableName]
            )->willReturnOnConsecutiveCalls(
                $mainTableName,
                $tableName
            );

        $objectManager = new ObjectManager($this);
        $collection = $objectManager->getObject(
            Collection::class,
            [
                'resource' => $resource,
            ]
        );
        $this->assertInstanceOf(Collection::class, $collection->addValuesToResult());
    }
}
