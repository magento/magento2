<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\ResourceModel\Provider;

use Magento\Framework\ObjectManager\TMap;
use Magento\Framework\ObjectManager\TMapFactory;
use Magento\Sales\Model\ResourceModel\Provider\IdListProviderComposite;
use Magento\Sales\Model\ResourceModel\Provider\IdListProviderInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class IdListProviderCompositeTest
 */
class IdListProviderCompositeTest extends \PHPUnit_Framework_TestCase
{
    public function testGetEmpty()
    {
        /** @var TMapFactory|MockObject $tMapFactory */
        $tMapFactory = $this->getMockBuilder(TMapFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $tMap = $this->getMockBuilder(TMap::class)
            ->disableOriginalConstructor()
            ->getMock();

        $tMapFactory->expects(static::once())
            ->method('create')
            ->with(
                [
                    'array' => [],
                    'type' => IdListProviderInterface::class
                ]
            )
            ->willReturn($tMap);
        $tMap->expects(static::once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([]));

        $provider = new IdListProviderComposite($tMapFactory, []);
        static::assertEquals([], $provider->get('main_table', 'grid_table'));
    }

    /**
     * @covers \Magento\Sales\Model\ResourceModel\Provider\IdListProviderComposite::get
     */
    public function testGet()
    {
        /** @var TMapFactory|MockObject $tMapFactory */
        $tMapFactory = $this->getMockBuilder(TMapFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $tMap = $this->getMockBuilder(TMap::class)
            ->disableOriginalConstructor()
            ->getMock();

        $provider1 = $this->getMockBuilder(IdListProviderInterface::class)
            ->getMockForAbstractClass();
        $provider1->expects(static::once())
            ->method('get')
            ->willReturn([1, 2]);

        $provider2 = $this->getMockBuilder(IdListProviderInterface::class)
            ->getMockForAbstractClass();
        $provider2->expects(static::once())
            ->method('get')
            ->willReturn([3, 4]);

        $tMapFactory->expects(static::once())
            ->method('create')
            ->with(
                [
                    'array' => [
                        'provider1' => IdListProviderInterface::class,
                        'provider2' => IdListProviderInterface::class
                    ],
                    'type' => IdListProviderInterface::class
                ]
            )
            ->willReturn($tMap);
        $tMap->expects(static::once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$provider1, $provider2]));

        $provider = new IdListProviderComposite(
            $tMapFactory,
            [
                'provider1' => IdListProviderInterface::class,
                'provider2' => IdListProviderInterface::class,
            ]
        );

        static::assertEquals([1, 2, 3, 4], $provider->get('main_table', 'grid_table'));
    }
}
