<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model\Observer;

use Magento\Customer\Model\Logger;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Event\Observer;
use Magento\Customer\Model\Observer\Log;

/**
 * Class LogTest
 */
class LogTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Log
     */
    protected $logObserver;

    /**
     * @var Logger | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->loggerMock = $this->getMock('Magento\Customer\Model\Logger', [], [], '', false);
        $this->logObserver = new Log($this->loggerMock);
    }

    /**
     * @return void
     */
    public function testLogLastLoginAt()
    {
        $id = 1;

        $observerMock = $this->getMock('Magento\Framework\Event\Observer', [], [], '', false);
        $eventMock = $this->getMock('Magento\Framework\Event', ['getCustomer'], [], '', false);
        $customerMock = $this->getMock('Magento\Customer\Model\Customer', [], [], '', false);

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

        $this->logObserver->logLastLoginAt($observerMock);
    }

    /**
     * @return void
     */
    public function testLogLastLogoutAt()
    {
        $id = 1;

        $observerMock = $this->getMock('Magento\Framework\Event\Observer', [], [], '', false);
        $eventMock = $this->getMock('Magento\Framework\Event', ['getCustomer'], [], '', false);
        $customerMock = $this->getMock('Magento\Customer\Model\Customer', [], [], '', false);

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

        $this->logObserver->logLastLogoutAt($observerMock);
    }
}
