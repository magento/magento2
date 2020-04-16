<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Observer;

use Magento\Customer\Model\Logger;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Event\Observer;
use Magento\Customer\Observer\LogLastLogoutAtObserver;

/**
 * Class LogLastLogoutAtObserverTest
 */
class LogLastLogoutAtObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var LogLastLogoutAtObserver
     */
    protected $logLastLogoutAtObserver;

    /**
     * @var Logger | \PHPUnit\Framework\MockObject\MockObject
     */
    protected $loggerMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->loggerMock = $this->createMock(\Magento\Customer\Model\Logger::class);
        $this->logLastLogoutAtObserver = new LogLastLogoutAtObserver($this->loggerMock);
    }

    /**
     * @return void
     */
    public function testLogLastLogoutAt()
    {
        $id = 1;

        $observerMock = $this->createMock(\Magento\Framework\Event\Observer::class);
        $eventMock = $this->createPartialMock(\Magento\Framework\Event::class, ['getCustomer']);
        $customerMock = $this->createMock(\Magento\Customer\Model\Customer::class);

        $observerMock->expects($this->once())
            ->method('getEvent')
            ->willReturn($eventMock);
        $eventMock->expects($this->once())
            ->method('getCustomer')
            ->willReturn($customerMock);
        $customerMock->expects($this->once())
            ->method('getId')
            ->willReturn($id);

        $this->loggerMock->expects($this->once())
            ->method('log');

        $this->logLastLogoutAtObserver->execute($observerMock);
    }
}
