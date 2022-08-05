<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Payment\Test\Unit\Gateway\Command;

use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\ObjectManager\TMap;
use Magento\Framework\ObjectManager\TMapFactory;
use Magento\Payment\Gateway\Command\CommandPool;
use Magento\Payment\Gateway\CommandInterface;
use PHPUnit\Framework\TestCase;

class CommandPoolTest extends TestCase
{
    public function testGet()
    {
        $commandI = $this->getMockBuilder(CommandInterface::class)
            ->getMockForAbstractClass();
        $tMapFactory = $this->getMockBuilder(TMapFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $tMap = $this->getMockBuilder(TMap::class)
            ->disableOriginalConstructor()
            ->getMock();

        $tMapFactory->expects(static::once())
            ->method('create')
            ->with(
                [
                    'array' => [CommandInterface::class],
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

        $pool = new CommandPool($tMapFactory, [CommandInterface::class]);

        static::assertSame($commandI, $pool->get('command'));
    }

    public function testGetException()
    {
        $this->expectException(NotFoundException::class);

        $tMapFactory = $this->getMockBuilder(TMapFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $tMap = $this->getMockBuilder(TMap::class)
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
