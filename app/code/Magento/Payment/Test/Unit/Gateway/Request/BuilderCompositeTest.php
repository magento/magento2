<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Test\Unit\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderComposite;
use Magento\Payment\Gateway\Request\BuilderInterface;

class BuilderCompositeTest extends \PHPUnit_Framework_TestCase
{
    public function testBuildEmpty()
    {
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

    /**
     * @param array $expected
     * @covers \Magento\Payment\Gateway\Request\BuilderComposite::build
     * @dataProvider buildDataProvider
     */
    public function testBuild(array $expected)
    {
        $tMapFactory = $this->getMockBuilder(\Magento\Framework\ObjectManager\TMapFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $tMap = $this->getMockBuilder(\Magento\Framework\ObjectManager\TMap::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customerBuilder = $this->getMockBuilder(\Magento\Payment\Gateway\Request\BuilderInterface::class)
            ->getMockForAbstractClass();
        $productBuilder = $this->getMockBuilder(\Magento\Payment\Gateway\Request\BuilderInterface::class)
            ->getMockForAbstractClass();
        $magentoBuilder = $this->getMockBuilder(\Magento\Payment\Gateway\Request\BuilderInterface::class)
            ->getMockForAbstractClass();

        $customerBuilder->expects(static::once())
            ->method('build')
            ->willReturn(
                [
                    'user' => $expected['user'],
                    'address' => $expected['address']
                ]
            );
        $productBuilder->expects(static::once())
            ->method('build')
            ->willReturn(
                [
                    'amount' => $expected['amount'],
                    'currency' => $expected['currency'],
                    'item' => $expected['item'],
                    'quantity' => $expected['quantity'],
                    'options' => ['product' => $expected['options']['product']]
                ]
            );
        $magentoBuilder->expects(static::once())
            ->method('build')
            ->willReturn(
                [
                    'url' => $expected['url'],
                    'options' => ['magento' => $expected['options']['magento']]
                ]
            );

        $tMapFactory->expects(static::once())
            ->method('create')
            ->with(
                [
                    'array' => [
                        'customer' => \Magento\Payment\Gateway\Request\BuilderInterface::class,
                        'product' => \Magento\Payment\Gateway\Request\BuilderInterface::class,
                        'magento' => \Magento\Payment\Gateway\Request\BuilderInterface::class
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
                'customer' => \Magento\Payment\Gateway\Request\BuilderInterface::class,
                'product' => \Magento\Payment\Gateway\Request\BuilderInterface::class,
                'magento' => \Magento\Payment\Gateway\Request\BuilderInterface::class
            ]
        );

        static::assertEquals($expected, $builder->build([]));
    }

    /**
     * Get list of variations
     */
    public function buildDataProvider()
    {
        return [
            [[
                'user' => 'Mrs G. Crump',
                'address' => '46 Egernon Crescent',
                'amount' => 10.00,
                'currency' => 'pound',
                'item' => 'gas cooker',
                'quantity' => 1,
                'options' => ['product' => '', 'magento' => 'magento'],
                'url' => 'https://url.in',
            ]],
            [[
                'user' => 'John Doe',
                'address' => '46 Main Street',
                'amount' => 250.00,
                'currency' => 'usd',
                'item' => 'phone',
                'quantity' => 2,
                'options' => ['product' => 'product', 'magento' => 'magento'],
                'url' => 'https://url.io',
            ]],
            [[
                'user' => 'John Smit',
                'address' => '46 Egernon Crescent',
                'amount' => 1100.00,
                'currency' => 'usd',
                'item' => 'notebook',
                'quantity' => 1,
                'options' => ['product' => ['discount' => ['price' => 2.00]], 'magento' => 'magento'],
                'url' => 'http://url.ua',
            ]],
        ];
    }
}
