<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model\DateTime;

use Magento\Setup\Model\DateTime\DateTimeProvider;
use Magento\Setup\Model\DateTime\TimeZoneProvider;
use Magento\Setup\Model\ObjectManagerProvider;

class DateTimeProviderTest extends \PHPUnit\Framework\TestCase
{
    public function testGet()
    {
        $dateTime = $this->createMock(\Magento\Framework\Stdlib\DateTime\DateTime::class);
        /** @var TimeZoneProvider|\PHPUnit_Framework_MockObject_MockObject $timeZoneProvider */
        $timeZoneProvider = $this->createMock(\Magento\Setup\Model\DateTime\TimeZoneProvider::class);
        $timeZone = $this->createMock(\Magento\Framework\Stdlib\DateTime\Timezone::class);
        $timeZoneProvider->expects($this->any())
            ->method('get')
            ->willReturn($timeZone);
        $objectManager = $this->getMockForAbstractClass(
            \Magento\Framework\ObjectManagerInterface::class,
            [],
            '',
            false
        );
        $objectManager->expects($this->once())
            ->method('create')
            ->with(
                \Magento\Framework\Stdlib\DateTime\DateTime::class,
                ['localeDate' => $timeZone]
            )
            ->willReturn($dateTime);
        /** @var ObjectManagerProvider|\PHPUnit_Framework_MockObject_MockObject $objectManagerProvider */
        $objectManagerProvider = $this->createMock(\Magento\Setup\Model\ObjectManagerProvider::class);
        $objectManagerProvider->expects($this->any())
            ->method('get')
            ->willReturn($objectManager);
        $object = new DateTimeProvider($timeZoneProvider, $objectManagerProvider);
        $this->assertSame($dateTime, $object->get());
        // Assert that the provider always returns the same object
        $this->assertSame($dateTime, $object->get());
    }
}
