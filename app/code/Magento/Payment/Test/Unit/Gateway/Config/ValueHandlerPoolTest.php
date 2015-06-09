<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Test\Unit\Gateway\Config;

use Magento\Payment\Gateway\Config\ValueHandlerPool;

class ValueHandlerPoolTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructorException()
    {
        $this->setExpectedException('LogicException');
        $tMap = $this->getMockBuilder('Magento\Framework\ObjectManager\TMap')
            ->disableOriginalConstructor()
            ->getMock();
        $tMap->expects(static::once())
            ->method('offsetExists')
            ->with(ValueHandlerPool::DEFAULT_HANDLER)
            ->willReturn(false);
        new ValueHandlerPool($tMap);
    }

    public function testGet()
    {
        $defaultHandler = $this->getMockBuilder('Magento\Payment\Gateway\Config\ValueHandlerInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $someValueHandler = $this->getMockBuilder('Magento\Payment\Gateway\Config\ValueHandlerInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $tMap = $this->getMockBuilder('Magento\Framework\ObjectManager\TMap')
            ->disableOriginalConstructor()
            ->getMock();
        $tMap->expects(static::exactly(4))
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

        $pool = new ValueHandlerPool($tMap);
        static::assertSame($someValueHandler, $pool->get('some_value'));
        static::assertSame($defaultHandler, $pool->get(ValueHandlerPool::DEFAULT_HANDLER));
        static::assertSame($defaultHandler, $pool->get('no_custom_logic_required'));
    }
}
