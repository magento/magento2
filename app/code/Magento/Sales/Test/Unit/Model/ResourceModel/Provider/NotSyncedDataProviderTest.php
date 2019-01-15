<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\ResourceModel\Provider;

use Magento\Framework\ObjectManager\TMapFactory;
use Magento\Sales\Model\ResourceModel\Provider\NotSyncedDataProvider;
use Magento\Sales\Model\ResourceModel\Provider\NotSyncedDataProviderInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class for testing not synchronized DataProvider.
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

        $tMapFactory->method('create')
            ->willReturn([]);

        $provider = new NotSyncedDataProvider($tMapFactory);
        self::assertEquals([], $provider->getIds('main_table', 'grid_table'));
    }

    public function testGetIds()
    {
        /** @var TMapFactory|MockObject $tMapFactory */
        $tMapFactory = $this->getMockBuilder(TMapFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $provider1 = $this->getMockBuilder(NotSyncedDataProviderInterface::class)
            ->getMockForAbstractClass();
        $provider1->method('getIds')
            ->willReturn([1, 2]);

        $provider2 = $this->getMockBuilder(NotSyncedDataProviderInterface::class)
            ->getMockForAbstractClass();
        $provider2->method('getIds')
            ->willReturn([2, 3, 4]);

        $tMapFactory->method('create')
            ->with(self::equalTo(
                [
                    'array' => [$provider1, $provider2],
                    'type' => NotSyncedDataProviderInterface::class
                ]
            ))
            ->willReturn([$provider1, $provider2]);

        $provider = new NotSyncedDataProvider($tMapFactory, [$provider1, $provider2]);

        self::assertEquals(
            [1, 2, 3, 4],
            array_values($provider->getIds('main_table', 'grid_table'))
        );
    }
}
