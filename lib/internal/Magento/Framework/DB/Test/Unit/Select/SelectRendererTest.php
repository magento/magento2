<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Test\Unit\Select;

class SelectRendererTest extends \PHPUnit_Framework_TestCase
{
    public function testRender()
    {
        $rendererOne = $this->getMockBuilder(\Magento\Framework\DB\Select\RendererInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renders = [
            ['renderer' => $rendererOne, 'sort' => 10, 'part' => 'from'],
            ['renderer' => $rendererOne, 'sort' => 20, 'part' => 'from'],
            ['renderer' => $rendererOne, 'sort' => 5, 'part' => 'from'],
        ];
        $selectMock = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
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
