<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Payment\Test\Unit\Gateway\Request;

use Magento\Framework\ObjectManager\TMap;
use Magento\Framework\ObjectManager\TMapFactory;
use Magento\Payment\Gateway\Request\BuilderComposite;
use Magento\Payment\Gateway\Request\BuilderInterface;
use PHPUnit\Framework\TestCase;

class BuilderCompositeTest extends TestCase
{
    public function testBuildEmpty()
    {
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
        $tMapFactory = $this->getMockBuilder(TMapFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $tMap = $this->getMockBuilder(TMap::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customerBuilder = $this->getMockBuilder(BuilderInterface::class)
            ->getMockForAbstractClass();
        $productBuilder = $this->getMockBuilder(BuilderInterface::class)
            ->getMockForAbstractClass();
        $magentoBuilder = $this->getMockBuilder(BuilderInterface::class)
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
                        'customer' => BuilderInterface::class,
                        'product' => BuilderInterface::class,
                        'magento' => BuilderInterface::class
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
                'customer' => BuilderInterface::class,
                'product' => BuilderInterface::class,
                'magento' => BuilderInterface::class
            ]
        );

        static::assertEquals($expected, $builder->build([]));
    }

    /**
     * Get list of variations
     */
    public static function buildDataProvider()
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
