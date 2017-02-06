<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
class LogLastLogoutAtObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LogLastLogoutAtObserver
     */
    protected $logLastLogoutAtObserver;

    /**
     * @var Logger | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->loggerMock = $this->getMock(\Magento\Customer\Model\Logger::class, [], [], '', false);
        $this->logLastLogoutAtObserver = new LogLastLogoutAtObserver($this->loggerMock);
    }

    /**
     * @return void
     */
    public function testLogLastLogoutAt()
    {
        $id = 1;

        $observerMock = $this->getMock(\Magento\Framework\Event\Observer::class, [], [], '', false);
        $eventMock = $this->getMock(\Magento\Framework\Event::class, ['getCustomer'], [], '', false);
        $customerMock = $this->getMock(\Magento\Customer\Model\Customer::class, [], [], '', false);

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
