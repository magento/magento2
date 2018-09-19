<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Test\Unit\Select;

class RendererProxyTest extends \PHPUnit\Framework\TestCase
{
    public function testRender()
    {
        $objectManager = $this->getMockBuilder(\Magento\Framework\ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $selectRender = $this->getMockBuilder(\Magento\Framework\DB\Select\SelectRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManager->expects($this->once())
            ->method('get')
            ->with(\Magento\Framework\DB\Select\SelectRenderer::class)
            ->willReturn($selectRender);
        $selectMock = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $selectRender->expects($this->once())
            ->method('render')
            ->with($selectMock, '')
            ->willReturn('sql');

        $model = new \Magento\Framework\DB\Select\RendererProxy($objectManager);
        $this->assertEquals('sql', $model->render($selectMock, ''));
    }
}
