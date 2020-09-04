<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\DB\Test\Unit\Select;

use Magento\Framework\DB\Platform\Quote;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Select\OrderRenderer;
use PHPUnit\Framework\TestCase;

class OrderRendererTest extends TestCase
{
    public function testRender()
    {
        $quoteMock = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $selectMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $parts = [
            10,
            'ASC',
            ['field1', 1]
        ];

        $quoteMock->expects($this->any())
            ->method('quoteIdentifier')
            ->willReturnArgument(0);
        $selectMock->expects($this->any())
            ->method('getPart')
            ->with(Select::ORDER)
            ->willReturn($parts);
        $model = new OrderRenderer($quoteMock);
        $this->assertEquals(" ORDER BY 10, ASC, field1 1\n", $model->render($selectMock));
    }
}
