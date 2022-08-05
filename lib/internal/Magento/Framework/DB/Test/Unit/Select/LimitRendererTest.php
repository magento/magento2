<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\DB\Test\Unit\Select;

use Magento\Framework\DB\Select;
use Magento\Framework\DB\Select\LimitRenderer;
use Magento\Framework\DB\Sql\LimitExpression;
use PHPUnit\Framework\TestCase;

class LimitRendererTest extends TestCase
{
    public function testRender()
    {
        $selectMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $offset = 10;
        $selectMock->expects($this->exactly(4))
            ->method('getPart')
            ->willReturnMap([[Select::LIMIT_OFFSET, $offset], [Select::LIMIT_COUNT, 2]]);
        $model = new LimitRenderer();
        $result = $model->render($selectMock);
        $this->assertInstanceOf(LimitExpression::class, $result);
        $this->assertEquals('LIMIT 2 OFFSET 10', $result->__toString());
    }
}
