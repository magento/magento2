<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Test\Unit\Sql;

class LimitExpressionTest extends \PHPUnit\Framework\TestCase
{
    /**
     */
    public function testToStringExceptionCount()
    {
        $this->expectException(\Zend_Db_Adapter_Exception::class);
        $this->expectExceptionMessage('LIMIT argument count=0 is not valid');

        $sql = 'test sql';
        $count = 0;
        $model = new \Magento\Framework\DB\Sql\LimitExpression($sql, $count);
        $model->__toString();
    }

    /**
     */
    public function testToStringExceptionOffset()
    {
        $this->expectException(\Zend_Db_Adapter_Exception::class);
        $this->expectExceptionMessage('LIMIT argument offset=-1 is not valid');

        $sql = 'test sql';
        $count = 1;
        $offset = -1;
        $model = new \Magento\Framework\DB\Sql\LimitExpression($sql, $count, $offset);
        $model->__toString();
    }

    public function testToString()
    {
        $sql = 'select * from test_table';
        $count = 1;
        $offset = 1;
        $model = new \Magento\Framework\DB\Sql\LimitExpression($sql, $count, $offset);
        $this->assertEquals('select * from test_table LIMIT 1 OFFSET 1', $model->__toString());
    }
}
