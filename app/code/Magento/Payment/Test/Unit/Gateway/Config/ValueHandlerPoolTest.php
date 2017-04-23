<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Test\Unit\Gateway\Config;

use Magento\Payment\Gateway\Config\ValueHandlerInterface;
use Magento\Payment\Gateway\Config\ValueHandlerPool;

class ValueHandlerPoolTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructorException()
    {
        $this->setExpectedException('LogicException');
        $tMapFactory = $this->getMockBuilder(\Magento\Framework\ObjectManager\TMapFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $tMapFactory->expects(static::never())
            ->method('create');
        new ValueHandlerPool($tMapFactory, []);
    }

    public function testGet()
    {
        $defaultHandler = $this->getMockBuilder(ValueHandlerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $someValueHandler = $this->getMockBuilder(ValueHandlerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $tMapFactory = $this->getMockBuilder(\Magento\Framework\ObjectManager\TMapFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $tMap = $this->getMockBuilder(\Magento\Framework\ObjectManager\TMap::class)
            ->disableOriginalConstructor()
            ->getMock();

        $tMapFactory->expects(static::once())
            ->method('create')
            ->with(
                [
                    'array' => [
                        ValueHandlerPool::DEFAULT_HANDLER => ValueHandlerInterface::class,
                        'some_value' => ValueHandlerInterface::class
                    ],
                    'type' => ValueHandlerInterface::class
                ]
            )
            ->willReturn($tMap);
        $tMap->expects(static::exactly(3))
            ->method('offsetExists')
            ->willReturnMap(
                [
                    [ValueHandlerPool::DEFAULT_HANDLER, true],
                    ['some_value', true]
                ]
            );
        $tMap->expects(static::exactly(3))
            ->method('offsetGet')
            ->willReturnMap(
                [
                    [ValueHandlerPool::DEFAULT_HANDLER, $defaultHandler],
                    ['some_value', $someValueHandler]
                ]
            );

        $pool = new ValueHandlerPool(
            $tMapFactory,
            [
                ValueHandlerPool::DEFAULT_HANDLER => ValueHandlerInterface::class,
                'some_value' => ValueHandlerInterface::class
            ]
        );
        static::assertSame($someValueHandler, $pool->get('some_value'));
        static::assertSame($defaultHandler, $pool->get(ValueHandlerPool::DEFAULT_HANDLER));
        static::assertSame($defaultHandler, $pool->get('no_custom_logic_required'));
    }
}
