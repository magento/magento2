<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\DB\Test\Unit\Select;

use Magento\Framework\DB\Select;
use Magento\Framework\DB\Select\UnionRenderer;
use PHPUnit\Framework\TestCase;

class UnionRendererTest extends TestCase
{
    public function testRender()
    {
        $model = new UnionRenderer();
        $select = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $selectPart = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $selectPart->expects($this->exactly(2))
            ->method('assemble')
            ->willReturnMap([['UNION (some select) as'], ['UNION (some select2)']]);

        $parts = [
            [$selectPart, 'type1'],
            [$selectPart, 'type2']
        ];
        $select->expects($this->any())
            ->method('getPart')
            ->with(Select::UNION)
            ->willReturn($parts);

        $this->assertEquals('UNION (some select) as type1 UNION (some select) as', $model->render($select));
    }
}
