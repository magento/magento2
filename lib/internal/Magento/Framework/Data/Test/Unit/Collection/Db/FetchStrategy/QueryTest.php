<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Data\Test\Unit\Collection\Db\FetchStrategy;

use Magento\Framework\Data\Collection\Db\FetchStrategy\Query;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Select\SelectRenderer;
use PHPUnit\Framework\TestCase;

class QueryTest extends TestCase
{
    public function testFetchAll()
    {
        $expectedResult = new \stdClass();
        $bindParams = ['param_one' => 'value_one', 'param_two' => 'value_two'];
        $adapter = $this->createPartialMock(Mysql::class, ['fetchAll']);
        $renderer = $this->createMock(SelectRenderer::class);
        $select = new Select($adapter, $renderer);
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
        $object = new Query();
        $this->assertSame($expectedResult, $object->fetchAll($select, $bindParams));
    }
}
