<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data\Test\Unit\Collection\Db\FetchStrategy;

class QueryTest extends \PHPUnit_Framework_TestCase
{
    public function testFetchAll()
    {
        $expectedResult = new \stdClass();
        $bindParams = ['param_one' => 'value_one', 'param_two' => 'value_two'];
        $adapter = $this->getMock('Magento\Framework\DB\Adapter\Pdo\Mysql', ['fetchAll'], [], '', false);
        $select = new \Magento\Framework\DB\Select($adapter);
        $adapter->expects(
            $this->once()
        )->method(
            'fetchAll'
        )->with(
            $select,
            $bindParams
        )->will(
            $this->returnValue($expectedResult)
        );
        $object = new \Magento\Framework\Data\Collection\Db\FetchStrategy\Query();
        $this->assertSame($expectedResult, $object->fetchAll($select, $bindParams));
    }
}
