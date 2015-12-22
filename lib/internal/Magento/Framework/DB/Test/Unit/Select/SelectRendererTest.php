<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Test\Unit\Select;

class SelectRendererTest extends \PHPUnit_Framework_TestCase
{
    public function testRender()
    {
        $rendererOne = $this->getMockBuilder('Magento\Framework\DB\Select\RendererInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $renders = [
            ['renderer' => $rendererOne, 'sort' => 10],
            ['renderer' => $rendererOne, 'sort' => 20],
            ['renderer' => $rendererOne, 'sort' => 5],
        ];
        $selectMock = $this->getMockBuilder('Magento\Framework\DB\Select')
            ->disableOriginalConstructor()
            ->getMock();
        $rendererOne->expects($this->any())
            ->method('render')
            ->withAnyParameters()
            ->willReturn('render1');

        $model = new \Magento\Framework\DB\Select\SelectRenderer($renders);
        $this->assertEquals('render1', $model->render($selectMock));
    }
}
