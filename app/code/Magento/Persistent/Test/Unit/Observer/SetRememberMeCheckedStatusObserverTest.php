<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Persistent\Test\Unit\Observer;

class SetRememberMeCheckedStatusObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Persistent\Observer\SetRememberMeCheckedStatusObserver
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $helperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $sessionHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $checkoutSessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $observerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    protected function setUp()
    {
        $this->requestMock = $this->getMock(\Magento\Framework\App\Request\Http::class, [], [], '', false);
        $this->helperMock = $this->getMock(\Magento\Persistent\Helper\Data::class, [], [], '', false);
        $this->sessionHelperMock = $this->getMock(\Magento\Persistent\Helper\Session::class, [], [], '', false);
        $checkoutMethods = ['setRememberMeChecked', '__wakeUp'];
        $this->checkoutSessionMock = $this->getMock(
            \Magento\Checkout\Model\Session::class,
            $checkoutMethods,
            [],
            '',
            false
        );
        $this->observerMock = $this->getMock(\Magento\Framework\Event\Observer::class, [], [], '', false);
        $eventMethods = ['getRequest', '__wakeUp'];
        $this->eventManagerMock = $this->getMock(\Magento\Framework\Event::class, $eventMethods, [], '', false);
        $this->model = new \Magento\Persistent\Observer\SetRememberMeCheckedStatusObserver(
            $this->helperMock,
            $this->sessionHelperMock,
            $this->checkoutSessionMock
        );
    }

    public function testSetRememberMeCheckedStatusWhenPersistentDataCannotProcess()
    {
        $this->helperMock
            ->expects($this->once())
            ->method('canProcess')
            ->with($this->observerMock)
            ->will($this->returnValue(false));
        $this->helperMock->expects($this->never())->method('isEnabled');
        $this->observerMock->expects($this->never())->method('getEvent');
        $this->model->execute($this->observerMock);
    }

    public function testSetRememberMeCheckedStatusWhenPersistentDataCanProcess()
    {
        $rememberMeCheckbox = 1;
        $this->helperMock
            ->expects($this->once())
            ->method('canProcess')
            ->with($this->observerMock)
            ->will($this->returnValue(true));
        $this->helperMock->expects($this->once())->method('isEnabled')->will($this->returnValue(true));
        $this->helperMock->expects($this->once())->method('isRememberMeEnabled')->will($this->returnValue(true));

        $this->observerMock
            ->expects($this->once())
            ->method('getEvent')
            ->will($this->returnValue($this->eventManagerMock));
        $this->eventManagerMock
            ->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($this->requestMock));
        $this->requestMock
            ->expects($this->once())
            ->method('getPost')
            ->with('persistent_remember_me')
            ->will($this->returnValue($rememberMeCheckbox));
        $this->sessionHelperMock
            ->expects($this->once())
            ->method('setRememberMeChecked')
            ->with((bool)$rememberMeCheckbox);
        $this->requestMock
            ->expects($this->once())
            ->method('getFullActionName')
            ->will($this->returnValue('checkout_onepage_saveBilling'));
        $this->checkoutSessionMock
            ->expects($this->once())
            ->method('setRememberMeChecked')
            ->with((bool)$rememberMeCheckbox);
        $this->model->execute($this->observerMock);
    }

    public function testSetRememberMeCheckedStatusWhenActionNameIncorrect()
    {
        $rememberMeCheckbox = 1;
        $this->helperMock
            ->expects($this->once())
            ->method('canProcess')
            ->with($this->observerMock)
            ->will($this->returnValue(true));
        $this->helperMock->expects($this->once())->method('isEnabled')->will($this->returnValue(true));
        $this->helperMock->expects($this->once())->method('isRememberMeEnabled')->will($this->returnValue(true));

        $this->observerMock
            ->expects($this->once())
            ->method('getEvent')
            ->will($this->returnValue($this->eventManagerMock));
        $this->eventManagerMock
            ->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($this->requestMock));
        $this->requestMock
            ->expects($this->once())
            ->method('getPost')
            ->with('persistent_remember_me')
            ->will($this->returnValue($rememberMeCheckbox));
        $this->sessionHelperMock
            ->expects($this->once())
            ->method('setRememberMeChecked')
            ->with((bool)$rememberMeCheckbox);
        $this->requestMock
            ->expects($this->exactly(2))
            ->method('getFullActionName')
            ->will($this->returnValue('method_name'));
        $this->checkoutSessionMock
            ->expects($this->never())
            ->method('setRememberMeChecked');
        $this->model->execute($this->observerMock);
    }

    public function testSetRememberMeCheckedStatusWhenRequestNotExist()
    {
        $this->helperMock
            ->expects($this->once())
            ->method('canProcess')
            ->with($this->observerMock)
            ->will($this->returnValue(true));
        $this->helperMock->expects($this->once())->method('isEnabled')->will($this->returnValue(true));
        $this->helperMock->expects($this->once())->method('isRememberMeEnabled')->will($this->returnValue(true));

        $this->observerMock
            ->expects($this->once())
            ->method('getEvent')
            ->will($this->returnValue($this->eventManagerMock));
        $this->eventManagerMock
            ->expects($this->once())
            ->method('getRequest');
        $this->model->execute($this->observerMock);
    }
}
