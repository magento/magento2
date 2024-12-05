<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Payment\Test\Unit\Gateway\Response;

use Magento\Framework\ObjectManager\TMap;
use Magento\Framework\ObjectManager\TMapFactory;
use Magento\Payment\Gateway\Response\HandlerChain;
use Magento\Payment\Gateway\Response\HandlerInterface;
use PHPUnit\Framework\TestCase;

class HandlerChainTest extends TestCase
{
    public function testHandle()
    {
        $handler1 = $this->getMockBuilder(HandlerInterface::class)
            ->getMockForAbstractClass();
        $handler2 = $this->getMockBuilder(HandlerInterface::class)
            ->getMockForAbstractClass();
        $tMapFactory = $this->getMockBuilder(TMapFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $tMap = $this->getMockBuilder(TMap::class)
            ->disableOriginalConstructor()
            ->getMock();

        $tMapFactory->expects(static::once())
            ->method('create')
            ->with(
                [
                    'array' => [
                        'handler1' => HandlerInterface::class,
                        'handler2' => HandlerInterface::class
                    ],
                    'type' => HandlerInterface::class
                ]
            )
            ->willReturn($tMap);
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

        $chain = new HandlerChain(
            $tMapFactory,
            [
                'handler1' => HandlerInterface::class,
                'handler2' => HandlerInterface::class
            ]
        );
        $chain->handle($handlingSubject, $response);
    }
}
