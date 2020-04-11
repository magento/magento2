<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Persistent\Test\Unit\Observer;

use PHPUnit\Framework\TestCase;
use Magento\Persistent\Observer\ApplyPersistentDataObserver;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Persistent\Helper\Session;
use Magento\Persistent\Helper\Data;
use Magento\Framework\Event\Observer;
use Magento\Persistent\Model\Persistent\Config;
use Magento\Persistent\Model\Persistent\ConfigFactory;

class ApplyPersistentDataObserverTest extends TestCase
{
    /**
     * @var ApplyPersistentDataObserver
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $sessionMock;

    /**
     * @var MockObject
     */
    protected $persistentHelperMock;

    /**
     * @var MockObject
     */
    protected $customerSessionMock;

    /**
     * @var MockObject
     */
    protected $persistentConfigMock;

    /**
     * @var MockObject
     */
    protected $observerMock;

    /**
     * @var MockObject
     */
    protected $configMock;

    protected function setUp(): void
    {
        $this->sessionMock = $this->createMock(Session::class);
        $this->customerSessionMock = $this->createMock(\Magento\Customer\Model\Session::class);
        $this->persistentHelperMock = $this->createMock(Data::class);
        $this->observerMock = $this->createMock(Observer::class);
        $this->persistentConfigMock = $this->createMock(Config::class);
        $this->configMock =
            $this->createPartialMock(ConfigFactory::class, ['create']);
        $this->model = new ApplyPersistentDataObserver(
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
