<?php declare(strict_types=1);
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Persistent\Test\Unit\Observer;

use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Persistent\Helper\Data;
use Magento\Persistent\Helper\Session;
use Magento\Persistent\Model\Persistent\Config;
use Magento\Persistent\Model\Persistent\ConfigFactory;
use Magento\Persistent\Observer\ApplyBlockPersistentDataObserver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ApplyBlockPersistentDataObserverTest extends TestCase
{
    /**
     * @var ApplyBlockPersistentDataObserver
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
    protected $observerMock;

    /**
     * @var MockObject
     */
    protected $configMock;

    /**
     * @var MockObject
     */
    protected $eventMock;

    /**
     * @var MockObject
     */
    protected $blockMock;

    /**
     * @var MockObject
     */
    protected $persistentConfigMock;

    protected function setUp(): void
    {
        $eventMethods = ['getConfigFilePath', 'getBlock', '__wakeUp'];
        $this->sessionMock = $this->createMock(Session::class);
        $this->customerSessionMock = $this->createMock(\Magento\Customer\Model\Session::class);
        $this->persistentHelperMock = $this->createMock(Data::class);
        $this->configMock =
            $this->createPartialMock(ConfigFactory::class, ['create']);
        $this->observerMock = $this->createMock(Observer::class);
        $this->eventMock = $this->createPartialMock(Event::class, $eventMethods);
        $this->blockMock = $this->createMock(AbstractBlock::class);
        $this->persistentConfigMock = $this->createMock(Config::class);
        $this->model = new ApplyBlockPersistentDataObserver(
            $this->sessionMock,
            $this->persistentHelperMock,
            $this->customerSessionMock,
            $this->configMock
        );
    }

    public function testExecuteWhenSessionNotPersistent()
    {
        $this->sessionMock->expects($this->once())->method('isPersistent')->will($this->returnValue(false));
        $this->observerMock->expects($this->never())->method('getEvent');
        $this->model->execute($this->observerMock);
    }

    public function testExecuteForLoggedInAndPersistentCustomer()
    {
        $this->sessionMock->expects($this->once())->method('isPersistent')->will($this->returnValue(true));
        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->will($this->returnValue(true));
        $this->observerMock->expects($this->never())->method('getEvent');
        $this->model->execute($this->observerMock);
    }

    public function testExecuteWhenBlockDoesNotExist()
    {
        $this->sessionMock->expects($this->once())->method('isPersistent')->will($this->returnValue(true));
        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->will($this->returnValue(false));
        $this->observerMock->expects($this->once())->method('getEvent')->will($this->returnValue($this->eventMock));
        $this->eventMock->expects($this->once())->method('getBlock')->will($this->returnValue(null));
        $this->eventMock->expects($this->never())->method('getConfigFilePath');
        $this->model->execute($this->observerMock);
    }

    public function testExecuteWhenConfigFilePathDoesNotExist()
    {
        $this->sessionMock->expects($this->once())->method('isPersistent')->will($this->returnValue(true));
        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->will($this->returnValue(false));
        $this->observerMock
            ->expects($this->any())
            ->method('getEvent')
            ->will($this->returnValue($this->eventMock));
        $this->eventMock->expects($this->once())->method('getBlock')->will($this->returnValue($this->blockMock));
        $this->eventMock->expects($this->once())->method('getConfigFilePath')->will($this->returnValue(false));
        $this->persistentHelperMock
            ->expects($this->once())
            ->method('getPersistentConfigFilePath')
            ->will($this->returnValue('path1/path2'));
        $this->configMock
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->persistentConfigMock));
        $this->persistentConfigMock->expects($this->once())->method('setConfigFilePath')->with('path1/path2');
        $this->persistentConfigMock
            ->expects($this->once())
            ->method('getBlockConfigInfo')
            ->with(get_class($this->blockMock))
            ->will($this->returnValue(['persistentConfigInfo']));
        $this->persistentConfigMock
            ->expects($this->once())
            ->method('fireOne')
            ->with('persistentConfigInfo', $this->blockMock);
        $this->model->execute($this->observerMock);
    }

    public function testExecute()
    {
        $this->sessionMock->expects($this->once())->method('isPersistent')->will($this->returnValue(true));
        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->will($this->returnValue(false));
        $this->observerMock
            ->expects($this->any())
            ->method('getEvent')
            ->will($this->returnValue($this->eventMock));
        $this->eventMock->expects($this->once())->method('getBlock')->will($this->returnValue($this->blockMock));
        $this->eventMock->expects($this->once())->method('getConfigFilePath')->will($this->returnValue('path1/path2'));
        $this->persistentHelperMock
            ->expects($this->never())
            ->method('getPersistentConfigFilePath');
        $this->configMock
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->persistentConfigMock));
        $this->persistentConfigMock->expects($this->once())->method('setConfigFilePath')->with('path1/path2');
        $this->persistentConfigMock
            ->expects($this->once())
            ->method('getBlockConfigInfo')
            ->with(get_class($this->blockMock))
            ->will($this->returnValue(['persistentConfigInfo']));
        $this->persistentConfigMock
            ->expects($this->once())
            ->method('fireOne')
            ->with('persistentConfigInfo', $this->blockMock);
        $this->model->execute($this->observerMock);
    }
}
