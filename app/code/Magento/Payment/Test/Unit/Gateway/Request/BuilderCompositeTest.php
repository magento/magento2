<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Test\Unit\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderComposite;
use Magento\Payment\Gateway\Request\BuilderInterface;

class BuilderCompositeTest extends \PHPUnit_Framework_TestCase
{
    public function testBuildEmpty()
    {
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
                    'type' => BuilderInterface::class
                ]
            )
            ->willReturn($tMap);
        $tMap->expects(static::once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([]));

        $builder = new BuilderComposite($tMapFactory, []);
        static::assertEquals([], $builder->build([]));
    }

    public function testBuild()
    {
        $expectedRequest = [
            'user' => 'Mrs G. Crump',
            'url' => 'https://url.in',
            'amount' => 10.00,
            'currency' => 'pound',
            'address' => '46 Egernon Crescent',
            'item' => 'gas cooker',
            'quantity' => 1
        ];

        $tMapFactory = $this->getMockBuilder('Magento\Framework\ObjectManager\TMapFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $tMap = $this->getMockBuilder('Magento\Framework\ObjectManager\TMap')
            ->disableOriginalConstructor()
            ->getMock();
        $customerBuilder = $this->getMockBuilder('Magento\Payment\Gateway\Request\BuilderInterface')
            ->getMockForAbstractClass();
        $productBuilder = $this->getMockBuilder('Magento\Payment\Gateway\Request\BuilderInterface')
            ->getMockForAbstractClass();
        $magentoBuilder = $this->getMockBuilder('Magento\Payment\Gateway\Request\BuilderInterface')
            ->getMockForAbstractClass();

        $customerBuilder->expects(static::once())
            ->method('build')
            ->willReturn(
                [
                    'user' => 'Mrs G. Crump',
                    'address' => '46 Egernon Crescent'
                ]
            );
        $productBuilder->expects(static::once())
            ->method('build')
            ->willReturn(
                [
                    'amount' => 10.00,
                    'currency' => 'pound',
                    'item' => 'gas cooker',
                    'quantity' => 1
                ]
            );
        $magentoBuilder->expects(static::once())
            ->method('build')
            ->willReturn(
                [
                    'url' => 'https://url.in'
                ]
            );

        $tMapFactory->expects(static::once())
            ->method('create')
            ->with(
                [
                    'array' => [
                        'customer' => 'Magento\Payment\Gateway\Request\BuilderInterface',
                        'product' => 'Magento\Payment\Gateway\Request\BuilderInterface',
                        'magento' => 'Magento\Payment\Gateway\Request\BuilderInterface'
                    ],
                    'type' => BuilderInterface::class
                ]
            )
            ->willReturn($tMap);
        $tMap->expects(static::once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$customerBuilder, $productBuilder, $magentoBuilder]));

        $builder = new BuilderComposite(
            $tMapFactory,
            [
                'customer' => 'Magento\Payment\Gateway\Request\BuilderInterface',
                'product' => 'Magento\Payment\Gateway\Request\BuilderInterface',
                'magento' => 'Magento\Payment\Gateway\Request\BuilderInterface'
            ]
        );

        static::assertEquals($expectedRequest, $builder->build([]));
    }
}
