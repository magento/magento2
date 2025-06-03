<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Payment\Test\Unit\Gateway\Validator;

use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\ObjectManager\TMap;
use Magento\Framework\ObjectManager\TMapFactory;
use Magento\Payment\Gateway\Validator\ValidatorInterface;
use Magento\Payment\Gateway\Validator\ValidatorPool;
use PHPUnit\Framework\TestCase;

class ValidatorPoolTest extends TestCase
{
    public function testGet()
    {
        $commandI = $this->getMockBuilder(ValidatorInterface::class)
            ->getMockForAbstractClass();
        $tMap = $this->getMockBuilder(TMap::class)
            ->disableOriginalConstructor()
            ->getMock();
        $tMapFactory = $this->getMockBuilder(TMapFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $tMapFactory->expects(static::once())
            ->method('create')
            ->with(
                [
                    'array' => ['validator' => ValidatorInterface::class],
                    'type' => ValidatorInterface::class
                ]
            )
            ->willReturn($tMap);
        $tMap->expects(static::once())
            ->method('offsetExists')
            ->with('validator')
            ->willReturn(true);
        $tMap->expects(static::once())
            ->method('offsetGet')
            ->with('validator')
            ->willReturn($commandI);

        $pool = new ValidatorPool(
            $tMapFactory,
            ['validator' => ValidatorInterface::class]
        );

        static::assertSame($commandI, $pool->get('validator'));
    }

    public function testGetException()
    {
        $this->expectException(NotFoundException::class);

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
                    'array' => [],
                    'type' => ValidatorInterface::class
                ]
            )
            ->willReturn($tMap);
        $tMap->expects(static::once())
            ->method('offsetExists')
            ->with('validator')
            ->willReturn(false);

        $pool = new ValidatorPool($tMapFactory, []);
        $pool->get('validator');
    }
}
