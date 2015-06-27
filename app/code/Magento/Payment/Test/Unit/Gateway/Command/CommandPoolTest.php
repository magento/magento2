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
                    'type' => 'Magento\Payment\Gateway\CommandInterface'
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

        $pool = new CommandPool(['Magento\Payment\Gateway\CommandInterface'], $tMapFactory);

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
                    'type' => 'Magento\Payment\Gateway\CommandInterface'
                ]
            )
            ->willReturn($tMap);
        $tMap->expects(static::once())
            ->method('offsetExists')
            ->with('command')
            ->willReturn(false);

        $pool = new CommandPool([], $tMapFactory);
        $pool->get('command');
    }
}
