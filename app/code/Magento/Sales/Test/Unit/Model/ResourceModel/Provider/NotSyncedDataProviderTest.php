<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\ResourceModel\Provider;

use Magento\Framework\ObjectManager\TMap;
use Magento\Framework\ObjectManager\TMapFactory;
use Magento\Sales\Model\ResourceModel\Provider\NotSyncedDataProvider;
use Magento\Sales\Model\ResourceModel\Provider\NotSyncedDataProviderInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class NotSyncedDataProviderTest
 */
class NotSyncedDataProviderTest extends \PHPUnit\Framework\TestCase
{
    public function testGetIdsEmpty()
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
                    'type' => NotSyncedDataProviderInterface::class
                ]
            )
            ->willReturn($tMap);
        $tMap->expects(static::once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([]));

        $provider = new NotSyncedDataProvider($tMapFactory, []);
        static::assertEquals([], $provider->getIds('main_table', 'grid_table'));
    }

    /**
     * @covers \Magento\Sales\Model\ResourceModel\Provider\NotSyncedDataProvider::getIds
     */
    public function testGetIds()
    {
        /** @var TMapFactory|MockObject $tMapFactory */
        $tMapFactory = $this->getMockBuilder(TMapFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $tMap = $this->getMockBuilder(TMap::class)
            ->disableOriginalConstructor()
            ->getMock();

        $provider1 = $this->getMockBuilder(NotSyncedDataProviderInterface::class)
            ->getMockForAbstractClass();
        $provider1->expects(static::once())
            ->method('getIds')
            ->willReturn([1, 2]);

        $provider2 = $this->getMockBuilder(NotSyncedDataProviderInterface::class)
            ->getMockForAbstractClass();
        $provider2->expects(static::once())
            ->method('getIds')
            ->willReturn([2, 3, 4]);

        $tMapFactory->expects(static::once())
            ->method('create')
            ->with(
                [
                    'array' => [
                        'provider1' => NotSyncedDataProviderInterface::class,
                        'provider2' => NotSyncedDataProviderInterface::class
                    ],
                    'type' => NotSyncedDataProviderInterface::class
                ]
            )
            ->willReturn($tMap);
        $tMap->expects(static::once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$provider1, $provider2]));

        $provider = new NotSyncedDataProvider(
            $tMapFactory,
            [
                'provider1' => NotSyncedDataProviderInterface::class,
                'provider2' => NotSyncedDataProviderInterface::class,
            ]
        );

        static::assertEquals([1, 2, 3, 4], array_values($provider->getIds('main_table', 'grid_table')));
    }
}
