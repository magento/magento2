<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Captcha\Test\Unit\Observer;

use Magento\Captcha\Model\ResourceModel\Log;
use Magento\Captcha\Model\ResourceModel\LogFactory;
use Magento\Captcha\Observer\ResetAttemptForBackendObserver;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for \Magento\Captcha\Observer\ResetAttemptForBackendObserver
 */
class ResetAttemptForBackendObserverTest extends TestCase
{
    /**
     * Test that the method resets attempts for Backend
     */
    public function testExecuteExpectsDeleteUserAttemptsCalled()
    {
        $logMock = $this->createMock(Log::class);
        $logMock->expects($this->once())->method('deleteUserAttempts')->willReturnSelf();

        $resLogFactoryMock = $this->createMock(LogFactory::class);
        $resLogFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($logMock);

        /** @var MockObject|Observer $eventObserverMock */
        $eventObserverMock = $this->getMockBuilder(Observer::class)
            ->addMethods(['getUser'])
            ->disableOriginalConstructor()
            ->getMock();
        $eventMock = $this->createMock(Event::class);
        $eventObserverMock->expects($this->once())
            ->method('getUser')
            ->willReturn($eventMock);

        $objectManager = new ObjectManagerHelper($this);
        /** @var ResetAttemptForBackendObserver $observer */
        $observer = $objectManager->getObject(
            ResetAttemptForBackendObserver::class,
            ['resLogFactory' => $resLogFactoryMock]
        );
        $observer->execute($eventObserverMock);
    }
}
