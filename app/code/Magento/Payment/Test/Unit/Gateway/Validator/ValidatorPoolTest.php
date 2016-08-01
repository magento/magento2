<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Test\Unit\Gateway\Validator;

use Magento\Payment\Gateway\Validator\ValidatorInterface;
use Magento\Payment\Gateway\Validator\ValidatorPool;

class ValidatorPoolTest extends \PHPUnit_Framework_TestCase
{
    public function testGet()
    {
        $commandI = $this->getMockBuilder(\Magento\Payment\Gateway\Validator\ValidatorInterface::class)
            ->getMockForAbstractClass();
        $tMap = $this->getMockBuilder(\Magento\Framework\ObjectManager\TMap::class)
            ->disableOriginalConstructor()
            ->getMock();
        $tMapFactory = $this->getMockBuilder(\Magento\Framework\ObjectManager\TMapFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $tMapFactory->expects(static::once())
            ->method('create')
            ->with(
                [
                    'array' => ['validator' => \Magento\Payment\Gateway\Validator\ValidatorInterface::class],
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
            ['validator' => \Magento\Payment\Gateway\Validator\ValidatorInterface::class]
        );

        static::assertSame($commandI, $pool->get('validator'));
    }

    public function testGetException()
    {
        $this->setExpectedException(\Magento\Framework\Exception\NotFoundException::class);

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
