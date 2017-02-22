<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Test\Unit\Gateway\Command;

use Magento\Payment\Gateway\Command\CommandPool;
use Magento\Payment\Gateway\CommandInterface;

class CommandPoolTest extends \PHPUnit_Framework_TestCase
{
    public function testGet()
    {
        $commandI = $this->getMockBuilder('Magento\Payment\Gateway\CommandInterface')
            ->getMockForAbstractClass();
        $tMapFactory = $this->getMockBuilder('Magento\Framework\ObjectManager\TMapFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $tMap = $this->getMockBuilder('Magento\Framework\ObjectManager\TMap')
            ->disableOriginalConstructor()
            ->getMock();

        $tMapFactory->expects(static::once())
            ->method('create')
            ->with(
                [
                    'array' => ['Magento\Payment\Gateway\CommandInterface'],
                    'type' => CommandInterface::class
                ]
            )
            ->willReturn($tMap);
        $tMap->expects(static::once())
            ->method('offsetExists')
            ->with('command')
            ->willReturn(true);
        $tMap->expects(static::once())
            ->method('offsetGet')
            ->with('command')
            ->willReturn($commandI);

        $pool = new CommandPool($tMapFactory, ['Magento\Payment\Gateway\CommandInterface']);

        static::assertSame($commandI, $pool->get('command'));
    }

    public function testGetException()
    {
        $this->setExpectedException('Magento\Framework\Exception\NotFoundException');

        $tMapFactory = $this->getMockBuilder('Magento\Framework\ObjectManager\TMapFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $tMap = $this->getMockBuilder('Magento\Framework\ObjectManager\TMap')
            ->disableOriginalConstructor()
            ->getMock();

        $tMapFactory->expects(static::once())
            ->method('create')
            ->with(
                [
                    'array' => [],
                    'type' => CommandInterface::class
                ]
            )
            ->willReturn($tMap);
        $tMap->expects(static::once())
            ->method('offsetExists')
            ->with('command')
            ->willReturn(false);

        $pool = new CommandPool($tMapFactory, []);
        $pool->get('command');
    }
}
