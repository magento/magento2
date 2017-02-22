<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model\DateTime;

use Magento\Setup\Model\DateTime\DateTimeProvider;
use Magento\Setup\Model\DateTime\TimezoneProvider;
use Magento\Setup\Model\ObjectManagerProvider;

class DateTimeProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testGet()
    {
        $dateTime = $this->getMock('\Magento\Framework\Stdlib\DateTime\DateTime', [], [], '', false);
        /** @var TimezoneProvider|\PHPUnit_Framework_MockObject_MockObject $timeZoneProvider */
        $timeZoneProvider = $this->getMock('\Magento\Setup\Model\DateTime\TimezoneProvider', [], [], '', false);
        $timeZone = $this->getMock('\Magento\Framework\Stdlib\DateTime\Timezone', [], [], '', false);
        $timeZoneProvider->expects($this->any())
            ->method('get')
            ->willReturn($timeZone);
        $objectManager = $this->getMockForAbstractClass('\Magento\Framework\ObjectManagerInterface', [], '', false);
        $objectManager->expects($this->once())
            ->method('create')
            ->with(
                'Magento\Framework\Stdlib\DateTime\DateTime',
                ['localeDate' => $timeZone]
            )
            ->willReturn($dateTime);
        /** @var ObjectManagerProvider|\PHPUnit_Framework_MockObject_MockObject $objectManagerProvider */
        $objectManagerProvider = $this->getMock('\Magento\Setup\Model\ObjectManagerProvider', [], [], '', false);
        $objectManagerProvider->expects($this->any())
            ->method('get')
            ->willReturn($objectManager);
        $object = new DateTimeProvider($timeZoneProvider, $objectManagerProvider);
        $this->assertSame($dateTime, $object->get());
        // Assert that the provider always returns the same object
        $this->assertSame($dateTime, $object->get());
    }
}
