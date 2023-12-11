<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\DB\Test\Unit\Sql;

use Magento\Framework\DB\Select;
use Magento\Framework\DB\Sql\UnionExpression;
use PHPUnit\Framework\TestCase;

class UnionExpressionTest extends TestCase
{
    public function testToString()
    {
        $sqlMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $sqlMock->expects($this->once())
            ->method('assemble')
            ->willReturn('test_assemble');
        $parts = [
            $sqlMock,
            '(test_column)'
        ];
        $model = new UnionExpression($parts);
        $this->assertEquals('(test_assemble)' . Select::SQL_UNION . '(test_column)', $model->__toString());
    }
}
