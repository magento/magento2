<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Test\Unit\Select;

class OrderRendererTest extends \PHPUnit_Framework_TestCase
{
    public function testRender()
    {
        $quoteMock = $this->getMockBuilder(\Magento\Framework\DB\Platform\Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $selectMock = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
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
            ->with(\Magento\Framework\DB\Select::ORDER)
            ->willReturn($parts);
        $model = new \Magento\Framework\DB\Select\OrderRenderer($quoteMock);
        $this->assertEquals(" ORDER BY 10, ASC, field1 1\n", $model->render($selectMock));
    }
}
