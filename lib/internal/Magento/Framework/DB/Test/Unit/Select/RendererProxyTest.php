<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\DB\Test\Unit\Select;

use Magento\Framework\DB\Select;
use Magento\Framework\DB\Select\RendererProxy;
use Magento\Framework\DB\Select\SelectRenderer;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\TestCase;

class RendererProxyTest extends TestCase
{
    public function testRender()
    {
        $objectManager = $this->getMockBuilder(ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $selectRender = $this->getMockBuilder(SelectRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManager->expects($this->once())
            ->method('get')
            ->with(SelectRenderer::class)
            ->willReturn($selectRender);
        $selectMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $selectRender->expects($this->once())
            ->method('render')
            ->with($selectMock, '')
            ->willReturn('sql');

        $model = new RendererProxy($objectManager);
        $this->assertEquals('sql', $model->render($selectMock, ''));
    }
}
