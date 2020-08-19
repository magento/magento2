<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\DB\Test\Unit\Sql;

use Magento\Framework\DB\Sql\LimitExpression;
use PHPUnit\Framework\TestCase;

class LimitExpressionTest extends TestCase
{
    public function testToStringExceptionCount()
    {
        $this->expectException('Zend_Db_Adapter_Exception');
        $this->expectExceptionMessage('LIMIT argument count=0 is not valid');
        $sql = 'test sql';
        $count = 0;
        $model = new LimitExpression($sql, $count);
        $model->__toString();
    }

    public function testToStringExceptionOffset()
    {
        $this->expectException('Zend_Db_Adapter_Exception');
        $this->expectExceptionMessage('LIMIT argument offset=-1 is not valid');
        $sql = 'test sql';
        $count = 1;
        $offset = -1;
        $model = new LimitExpression($sql, $count, $offset);
        $model->__toString();
    }

    public function testToString()
    {
        $sql = 'select * from test_table';
        $count = 1;
        $offset = 1;
        $model = new LimitExpression($sql, $count, $offset);
        $this->assertEquals('select * from test_table LIMIT 1 OFFSET 1', $model->__toString());
    }
}
