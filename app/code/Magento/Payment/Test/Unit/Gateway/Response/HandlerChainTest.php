<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Test\Unit\Gateway\Response;

use Magento\Payment\Gateway\Response\HandlerChain;

class HandlerChainTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $handler1 = $this->getMockBuilder('Magento\Payment\Gateway\Response\HandlerInterface')
            ->getMockForAbstractClass();
        $handler2 = $this->getMockBuilder('Magento\Payment\Gateway\Response\HandlerInterface')
            ->getMockForAbstractClass();

        $tMap = $this->getMockBuilder('Magento\Framework\ObjectManager\TMap')
            ->disableOriginalConstructor()
            ->getMock();
        $tMap->expects(static::once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$handler1, $handler2]));

        $handlingSubject = [];
        $response = [];
        $handler1->expects(static::once())
            ->method('handle')
            ->with($handlingSubject, $response);
        $handler2->expects(static::once())
            ->method('handle')
            ->with($handlingSubject, $response);

        $chain = new HandlerChain($tMap);
        $chain->handle($handlingSubject, $response);
    }
}
