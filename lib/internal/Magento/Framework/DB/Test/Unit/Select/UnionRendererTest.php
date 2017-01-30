<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Test\Unit\Select;

use Magento\Framework\DB\Select;

class UnionRendererTest extends \PHPUnit_Framework_TestCase
{
    public function testRender()
    {
        $model = new \Magento\Framework\DB\Select\UnionRenderer();
        $select = $this->getMockBuilder('Magento\Framework\DB\Select')
            ->disableOriginalConstructor()
            ->getMock();
        $selectPart = $this->getMockBuilder('Magento\Framework\DB\Select')
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
