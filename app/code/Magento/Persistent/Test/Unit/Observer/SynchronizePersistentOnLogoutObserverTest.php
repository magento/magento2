<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Persistent\Test\Unit\Observer;

use PHPUnit\Framework\TestCase;
use Magento\Persistent\Observer\SynchronizePersistentOnLogoutObserver;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Persistent\Helper\Data;
use Magento\Persistent\Helper\Session;
use Magento\Persistent\Model\SessionFactory;
use Magento\Framework\Event\Observer;

class SynchronizePersistentOnLogoutObserverTest extends TestCase
{
    /**
     * @var SynchronizePersistentOnLogoutObserver
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $helperMock;

    /**
     * @var MockObject
     */
    protected $sessionHelperMock;

    /**
     * @var MockObject
     */
    protected $sessionFactoryMock;

    /**
     * @var MockObject
     */
    protected $observerMock;

    /**
     * @var MockObject
     */
    protected $sessionMock;

    protected function setUp(): void
    {
        $this->helperMock = $this->createMock(Data::class);
        $this->sessionHelperMock = $this->createMock(Session::class);
        $this->sessionFactoryMock =
            $this->createPartialMock(SessionFactory::class, ['create']);
        $this->observerMock = $this->createMock(Observer::class);
        $this->sessionMock = $this->createMock(\Magento\Persistent\Model\Session::class);
        $this->model = new SynchronizePersistentOnLogoutObserver(
            $this->helperMock,
            $this->sessionHelperMock,
            $this->sessionFactoryMock
        );
    }

    public function testSynchronizePersistentOnLogoutWhenPersistentDataNotEnabled()
    {
        $this->helperMock->expects($this->once())->method('isEnabled')->will($this->returnValue(false));
        $this->sessionFactoryMock->expects($this->never())->method('create');
        $this->model->execute($this->observerMock);
    }

    public function testSynchronizePersistentOnLogoutWhenPersistentDataIsEnabled()
    {
        $this->helperMock->expects($this->once())->method('isEnabled')->will($this->returnValue(true));
        $this->helperMock->expects($this->once())->method('getClearOnLogout')->will($this->returnValue(true));
        $this->sessionFactoryMock
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->sessionMock));
        $this->sessionMock->expects($this->once())->method('removePersistentCookie');
        $this->sessionHelperMock->expects($this->once())->method('setSession')->with(null);
        $this->model->execute($this->observerMock);
    }
}
