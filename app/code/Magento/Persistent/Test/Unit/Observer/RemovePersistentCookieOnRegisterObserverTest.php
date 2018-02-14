<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Persistent\Test\Unit\Observer;

use \Magento\Persistent\Observer\RemovePersistentCookieOnRegisterObserver;

/**
 * Test for Magento\Persistent\Observer\RemovePersistentCookieOnRegisterObserver class.
 */
class RemovePersistentCookieOnRegisterObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RemovePersistentCookieOnRegisterObserver
     */
    private $model;

    /**
     * @var \Magento\Persistent\Helper\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    private $persistentSessionMock;

    /**
     * @var \Magento\Persistent\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    private $persistentDataMock;

    /**
     * @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerSessionMock;

    /**
     * @var \Magento\Persistent\Model\QuoteManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteManagerMock;

    /**
     * @var \Magento\Framework\Event\Observer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $observerMock;

    /**
     * @var \Magento\Persistent\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sessionModelMock;

    protected function setUp()
    {
        $this->persistentSessionMock = $this->getMock(\Magento\Persistent\Helper\Session::class, [], [], '', false);
        $this->sessionModelMock = $this->getMock(\Magento\Persistent\Model\Session::class, [], [], '', false);
        $this->persistentDataMock = $this->getMock(\Magento\Persistent\Helper\Data::class, [], [], '', false);
        $this->customerSessionMock = $this->getMock(\Magento\Customer\Model\Session::class, [], [], '', false);
        $this->quoteManagerMock = $this->getMock(\Magento\Persistent\Model\QuoteManager::class, [], [], '', false);
        $this->observerMock = $this->getMock(\Magento\Framework\Event\Observer::class, [], [], '', false);

        $this->model = new RemovePersistentCookieOnRegisterObserver(
            $this->persistentSessionMock,
            $this->persistentDataMock,
            $this->customerSessionMock,
            $this->quoteManagerMock);
    }

    public function testExecuteWithPersistentDataThatCanNotBeProcess()
    {
        $this->persistentDataMock->expects($this->once())
            ->method('canProcess')->with($this->observerMock)->will($this->returnValue(false));
        $this->persistentSessionMock->expects($this->never())->method('getSession');

        $this->model->execute($this->observerMock);
    }

    public function testExecuteWhenSessionIsNotPersistent()
    {
        $this->persistentDataMock->expects($this->once())
            ->method('canProcess')->with($this->observerMock)->will($this->returnValue(true));
        $this->persistentSessionMock->expects($this->once())->method('isPersistent')->will($this->returnValue(false));

        $this->persistentSessionMock->expects($this->never())->method('getSession');

        $this->model->execute($this->observerMock);
    }

    public function testExecuteWithNotLoggedInCustomer()
    {
        $this->persistentDataMock->expects($this->once())
            ->method('canProcess')->with($this->observerMock)->will($this->returnValue(true));
        $this->persistentSessionMock->expects($this->once())->method('isPersistent')->will($this->returnValue(true));
        $this->persistentSessionMock->expects($this->once())
            ->method('getSession')->will($this->returnValue($this->sessionModelMock));
        $this->sessionModelMock->expects($this->once())->method('removePersistentCookie')->will($this->returnSelf());
        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->will($this->returnValue(false));
        $this->customerSessionMock->expects($this->once())
            ->method('setCustomerId')->with(null)->will($this->returnSelf());
        $this->customerSessionMock->expects($this->once())
            ->method('setCustomerGroupId')->with(null)->will($this->returnSelf());
        $this->quoteManagerMock->expects($this->once())->method('setGuest');

        $this->model->execute($this->observerMock);
    }

    public function testExecute()
    {
        $this->persistentDataMock->expects($this->once())
            ->method('canProcess')->with($this->observerMock)->will($this->returnValue(true));
        $this->persistentSessionMock->expects($this->once())->method('isPersistent')->will($this->returnValue(true));
        $this->persistentSessionMock->expects($this->once())
            ->method('getSession')->will($this->returnValue($this->sessionModelMock));
        $this->sessionModelMock->expects($this->once())->method('removePersistentCookie')->will($this->returnSelf());
        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->will($this->returnValue(true));
        $this->customerSessionMock->expects($this->never())->method('setCustomerId');
        $this->customerSessionMock->expects($this->never())->method('setCustomerGroupId');
        $this->quoteManagerMock->expects($this->once())->method('setGuest');

        $this->model->execute($this->observerMock);
    }
}
