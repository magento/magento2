<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\DB\Test\Unit\Select;

use Magento\Framework\DB\Select;
use Magento\Framework\DB\Select\RendererInterface;
use Magento\Framework\DB\Select\SelectRenderer;
use PHPUnit\Framework\TestCase;

class SelectRendererTest extends TestCase
{
    public function testRender()
    {
        $rendererOne = $this->getMockBuilder(RendererInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $renders = [
            ['renderer' => $rendererOne, 'sort' => 10, 'part' => 'from'],
            ['renderer' => $rendererOne, 'sort' => 20, 'part' => 'from'],
            ['renderer' => $rendererOne, 'sort' => 5, 'part' => 'from'],
        ];
        $selectMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rendererOne->expects($this->any())
            ->method('render')
            ->withAnyParameters()
            ->willReturn('render1');

        $model = new SelectRenderer($renders);
        $this->assertEquals('render1', $model->render($selectMock));
    }
}
