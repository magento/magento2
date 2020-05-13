<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Captcha\Test\Unit\Observer;

use Magento\Captcha\Model\ResourceModel\Log;
use Magento\Captcha\Model\ResourceModel\LogFactory;
use Magento\Captcha\Observer\ResetAttemptForFrontendObserver;
use Magento\Customer\Model\Customer;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for \Magento\Captcha\Observer\ResetAttemptForFrontendObserver
 */
class ResetAttemptForFrontendObserverTest extends TestCase
{
    /**
     * Test that the method resets attempts for Frontend
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
            ->addMethods(['getModel'])
            ->disableOriginalConstructor()
            ->getMock();
        $eventObserverMock->expects($this->once())
            ->method('getModel')
            ->willReturn($this->createMock(Customer::class));

        $objectManager = new ObjectManagerHelper($this);
        /** @var ResetAttemptForFrontendObserver $observer */
        $observer = $objectManager->getObject(
            ResetAttemptForFrontendObserver::class,
            ['resLogFactory' => $resLogFactoryMock]
        );
        $this->assertInstanceOf(Log::class, $observer->execute($eventObserverMock));
    }
}
