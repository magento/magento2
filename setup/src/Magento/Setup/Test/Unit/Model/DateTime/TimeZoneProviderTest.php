<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model\DateTime;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Setup\Model\DateTime\TimeZoneProvider;
use Magento\Setup\Model\ObjectManagerProvider;

class TimeZoneProviderTest extends \PHPUnit\Framework\TestCase
{
    public function testGet()
    {
        $timeZone = $this->createMock(\Magento\Framework\Stdlib\DateTime\Timezone::class);
        $objectManager = $this->getMockForAbstractClass(
            \Magento\Framework\ObjectManagerInterface::class,
            [],
            '',
            false
        );
        $objectManager->expects($this->once())
            ->method('create')
            ->with(
                \Magento\Framework\Stdlib\DateTime\Timezone::class,
                ['scopeType' => ScopeConfigInterface::SCOPE_TYPE_DEFAULT]
            )
            ->willReturn($timeZone);
        /** @var ObjectManagerProvider|\PHPUnit\Framework\MockObject\MockObject $objectManagerProvider */
        $objectManagerProvider = $this->createMock(\Magento\Setup\Model\ObjectManagerProvider::class);
        $objectManagerProvider->expects($this->any())
            ->method('get')
            ->willReturn($objectManager);
        $object = new TimeZoneProvider($objectManagerProvider);
        $this->assertSame($timeZone, $object->get());
        // Assert that the provider always returns the same object
        $this->assertSame($timeZone, $object->get());
    }
}
