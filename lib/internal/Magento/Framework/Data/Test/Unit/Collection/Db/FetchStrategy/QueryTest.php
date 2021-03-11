<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data\Test\Unit\Collection\Db\FetchStrategy;

class QueryTest extends \PHPUnit\Framework\TestCase
{
    public function testFetchAll()
    {
        $expectedResult = new \stdClass();
        $bindParams = ['param_one' => 'value_one', 'param_two' => 'value_two'];
        $adapter = $this->createPartialMock(\Magento\Framework\DB\Adapter\Pdo\Mysql::class, ['fetchAll']);
        $renderer = $this->createMock(\Magento\Framework\DB\Select\SelectRenderer::class);
        $select = new \Magento\Framework\DB\Select($adapter, $renderer);
        $adapter->expects(
            $this->once()
        )->method(
            'fetchAll'
        )->with(
            $select,
            $bindParams
        )->willReturn(
            $expectedResult
        );
        $object = new \Magento\Framework\Data\Collection\Db\FetchStrategy\Query();
        $this->assertSame($expectedResult, $object->fetchAll($select, $bindParams));
    }
}
