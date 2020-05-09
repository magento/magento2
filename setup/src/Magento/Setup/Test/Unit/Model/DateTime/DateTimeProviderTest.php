<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Model\DateTime;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\Timezone;
use Magento\Setup\Model\DateTime\DateTimeProvider;
use Magento\Setup\Model\DateTime\TimeZoneProvider;
use Magento\Setup\Model\ObjectManagerProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DateTimeProviderTest extends TestCase
{
    public function testGet()
    {
        $dateTime = $this->createMock(DateTime::class);
        /** @var TimeZoneProvider|MockObject $timeZoneProvider */
        $timeZoneProvider = $this->createMock(TimeZoneProvider::class);
        $timeZone = $this->createMock(Timezone::class);
        $timeZoneProvider->expects($this->any())
            ->method('get')
            ->willReturn($timeZone);
        $objectManager = $this->getMockForAbstractClass(
            ObjectManagerInterface::class,
            [],
            '',
            false
        );
        $objectManager->expects($this->once())
            ->method('create')
            ->with(
                DateTime::class,
                ['localeDate' => $timeZone]
            )
            ->willReturn($dateTime);
        /** @var ObjectManagerProvider|MockObject $objectManagerProvider */
        $objectManagerProvider = $this->createMock(ObjectManagerProvider::class);
        $objectManagerProvider->expects($this->any())
            ->method('get')
            ->willReturn($objectManager);
        $object = new DateTimeProvider($timeZoneProvider, $objectManagerProvider);
        $this->assertSame($dateTime, $object->get());
        // Assert that the provider always returns the same object
        $this->assertSame($dateTime, $object->get());
    }
}
