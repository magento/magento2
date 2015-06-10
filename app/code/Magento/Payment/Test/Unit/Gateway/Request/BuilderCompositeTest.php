<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Test\Unit\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderComposite;

class BuilderCompositeTest extends \PHPUnit_Framework_TestCase
{
    public function testBuildEmpty()
    {
        $tMap = $this->getMockBuilder('Magento\Framework\ObjectManager\TMap')
            ->disableOriginalConstructor()
            ->getMock();
        $tMap->expects(static::once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([]));
        $builder = new BuilderComposite($tMap);
        static::assertEquals([], $builder->build([]));
    }

    public function testBuild()
    {
        $expectedRequest = [
            'user' => 'Mrs G. Crump',
            'url' => 'https://url.in',
            'amount' => 10.00,
            'currecy' => 'pound',
            'address' => '46 Egernon Crescent',
            'item' => 'gas cooker',
            'quantity' => 1
        ];

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
                    'currecy' => 'pound',
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

        $tMap = $this->getMockBuilder('Magento\Framework\ObjectManager\TMap')
            ->disableOriginalConstructor()
            ->getMock();
        $tMap->expects(static::once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$customerBuilder, $productBuilder, $magentoBuilder]));

        $builder = new BuilderComposite($tMap);

        static::assertEquals($expectedRequest, $builder->build([]));
    }
}
