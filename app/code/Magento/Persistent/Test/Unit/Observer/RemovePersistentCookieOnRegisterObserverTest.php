<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Persistent\Test\Unit\Observer;

use \Magento\Persistent\Observer\RemovePersistentCookieOnRegisterObserver;

class RemovePersistentCookieOnRegisterObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var RemovePersistentCookieOnRegisterObserver
     */
    protected $model;

    /**
     * @var \Magento\Persistent\Helper\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $persistentSessionMock;

    /**
     * @var \Magento\Persistent\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $persistentDataMock;

    /**
     * @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSessionMock;

    /**
     * @var \Magento\Persistent\Model\QuoteManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteManagerMock;

    /**
     * @var \Magento\Framework\Event\Observer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $observerMock;

    /**
     * @var \Magento\Persistent\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sessionModelMock;

    protected function setUp()
    {
        $this->persistentSessionMock = $this->createMock(\Magento\Persistent\Helper\Session::class);
        $this->sessionModelMock = $this->createMock(\Magento\Persistent\Model\Session::class);
        $this->persistentDataMock = $this->createMock(\Magento\Persistent\Helper\Data::class);
        $this->customerSessionMock = $this->createMock(\Magento\Customer\Model\Session::class);
        $this->quoteManagerMock = $this->createMock(\Magento\Persistent\Model\QuoteManager::class);
        $this->observerMock = $this->createMock(\Magento\Framework\Event\Observer::class);

        $this->model = new RemovePersistentCookieOnRegisterObserver(
            $this->persistentSessionMock,
            $this->persistentDataMock,
            $this->customerSessionMock,
            $this->quoteManagerMock
        );
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
