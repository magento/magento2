<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Test\Unit\Select;

use Magento\Framework\DB\Select;

class LimitRendererTest extends \PHPUnit\Framework\TestCase
{
    public function testRender()
    {
        $selectMock = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $offset = 10;
        $selectMock->expects($this->exactly(4))
            ->method('getPart')
            ->willReturnMap([[Select::LIMIT_OFFSET, $offset], [Select::LIMIT_COUNT, 2]]);
        $model = new \Magento\Framework\DB\Select\LimitRenderer();
        $result = $model->render($selectMock);
        $this->assertInstanceOf(\Magento\Framework\DB\Sql\LimitExpression::class, $result);
        $this->assertEquals('LIMIT 2 OFFSET 10', $result->__toString());
    }
}
