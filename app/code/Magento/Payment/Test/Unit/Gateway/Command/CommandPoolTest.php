<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Test\Unit\Gateway\Command;

use Magento\Payment\Gateway\Command\CommandPool;

class CommandPoolTest extends \PHPUnit_Framework_TestCase
{
    public function testGet()
    {
        $commandI = $this->getMockBuilder('Magento\Payment\Gateway\CommandInterface')
            ->getMockForAbstractClass();
        $tMap = $this->getMockBuilder('Magento\Framework\ObjectManager\TMap')
            ->disableOriginalConstructor()
            ->getMock();
        $tMap->expects(static::once())
            ->method('offsetExists')
            ->with('command')
            ->willReturn(true);
        $tMap->expects(static::once())
            ->method('offsetGet')
            ->with('command')
            ->willReturn($commandI);

        $pool = new CommandPool($tMap);

        static::assertSame($commandI, $pool->get('command'));
    }

    public function testGetException()
    {
        $this->setExpectedException('Magento\Framework\Exception\NotFoundException');
        $tMap = $this->getMockBuilder('Magento\Framework\ObjectManager\TMap')
            ->disableOriginalConstructor()
            ->getMock();
        $tMap->expects(static::once())
            ->method('offsetExists')
            ->with('command')
            ->willReturn(false);

        $pool = new CommandPool($tMap);
        $pool->get('command');
    }
}
