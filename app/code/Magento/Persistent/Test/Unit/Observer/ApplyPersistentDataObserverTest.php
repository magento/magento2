<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Persistent\Test\Unit\Observer;

class ApplyPersistentDataObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Persistent\Observer\ApplyPersistentDataObserver
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $sessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $persistentHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $persistentConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $observerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    protected function setUp()
    {
        $this->sessionMock = $this->getMock(\Magento\Persistent\Helper\Session::class, [], [], '', false);
        $this->customerSessionMock = $this->getMock(\Magento\Customer\Model\Session::class, [], [], '', false);
        $this->persistentHelperMock = $this->getMock(\Magento\Persistent\Helper\Data::class, [], [], '', false);
        $this->observerMock = $this->getMock(\Magento\Framework\Event\Observer::class, [], [], '', false);
        $this->persistentConfigMock = $this->getMock(
            \Magento\Persistent\Model\Persistent\Config::class,
            [],
            [],
            '',
            false
        );
        $this->configMock =
            $this->getMock(\Magento\Persistent\Model\Persistent\ConfigFactory::class, ['create'], [], '', false);
        $this->model = new \Magento\Persistent\Observer\ApplyPersistentDataObserver(
            $this->sessionMock,
            $this->persistentHelperMock,
            $this->customerSessionMock,
            $this->configMock
        );
    }

    public function testExecuteWhenCanNotApplyPersistentData()
    {
        $this->persistentHelperMock
            ->expects($this->once())
            ->method('canProcess')
            ->with($this->observerMock)
            ->will($this->returnValue(false));
        $this->configMock->expects($this->never())->method('create');
        $this->model->execute($this->observerMock);
    }

    public function testExecuteWhenCustomerIsNotPersistent()
    {
        $this->persistentHelperMock
            ->expects($this->once())
            ->method('canProcess')
            ->with($this->observerMock)
            ->will($this->returnValue(true));
        $this->sessionMock->expects($this->once())->method('isPersistent')->will($this->returnValue(false));
        $this->configMock->expects($this->never())->method('create');
        $this->model->execute($this->observerMock);
    }

    public function testExecuteWhenCustomerIsLoggedIn()
    {
        $this->persistentHelperMock
            ->expects($this->once())
            ->method('canProcess')
            ->with($this->observerMock)
            ->will($this->returnValue(true));
        $this->sessionMock->expects($this->once())->method('isPersistent')->will($this->returnValue(true));
        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->will($this->returnValue(true));
        $this->configMock->expects($this->never())->method('create');
        $this->model->execute($this->observerMock);
    }

    public function testExecute()
    {
        $this->persistentHelperMock
            ->expects($this->once())
            ->method('canProcess')
            ->with($this->observerMock)
            ->will($this->returnValue(true));
        $this->sessionMock->expects($this->once())->method('isPersistent')->will($this->returnValue(true));
        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->will($this->returnValue(false));
        $this->configMock
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->persistentConfigMock));
        $this->persistentHelperMock
            ->expects($this->once())
            ->method('getPersistentConfigFilePath')
            ->will($this->returnValue('path/path1'));
        $this->persistentConfigMock
            ->expects($this->once())
            ->method('setConfigFilePath')
            ->with('path/path1')
            ->will($this->returnSelf());
        $this->persistentConfigMock->expects($this->once())->method('fire');
        $this->model->execute($this->observerMock);
    }
}
