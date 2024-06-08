<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\ResourceModel\Provider;

use Magento\Framework\ObjectManager\TMapFactory;
use Magento\Sales\Model\ResourceModel\Provider\NotSyncedDataProvider;
use Magento\Sales\Model\ResourceModel\Provider\NotSyncedDataProviderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/** testing not synchronized DataProvider.
 */
class NotSyncedDataProviderTest extends TestCase
{
    public function testGetIdsEmpty()
    {
        /** @var TMapFactory|MockObject $tMapFactory */
        $tMapFactory = $this->getMockBuilder(TMapFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
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
            ->onlyMethods(['create'])
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
