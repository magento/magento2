<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Test\Unit\Select;

class RendererProxyTest extends \PHPUnit_Framework_TestCase
{
    public function testRender()
    {
        $objectManager = $this->getMockBuilder('Magento\Framework\ObjectManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $selectRender = $this->getMockBuilder('Magento\Framework\DB\Select\SelectRenderer')
            ->disableOriginalConstructor()
            ->getMock();
        $objectManager->expects($this->once())
            ->method('get')
            ->with('\\Magento\\Framework\\DB\\Select\\SelectRenderer')
            ->willReturn($selectRender);
        $selectMock = $this->getMockBuilder('Magento\Framework\DB\Select')
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
