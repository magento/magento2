<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Persistent\Test\Unit\Observer;

class ApplyBlockPersistentDataObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Persistent\Observer\ApplyBlockPersistentDataObserver
     */
    protected $model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $sessionMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $persistentHelperMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $customerSessionMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $observerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $configMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $eventMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $blockMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $persistentConfigMock;

    protected function setUp(): void
    {
        $eventMethods = ['getConfigFilePath', 'getBlock', '__wakeUp'];
        $this->sessionMock = $this->createMock(\Magento\Persistent\Helper\Session::class);
        $this->customerSessionMock = $this->createMock(\Magento\Customer\Model\Session::class);
        $this->persistentHelperMock = $this->createMock(\Magento\Persistent\Helper\Data::class);
        $this->configMock =
            $this->createPartialMock(\Magento\Persistent\Model\Persistent\ConfigFactory::class, ['create']);
        $this->observerMock = $this->createMock(\Magento\Framework\Event\Observer::class);
        $this->eventMock = $this->createPartialMock(\Magento\Framework\Event::class, $eventMethods);
        $this->blockMock = $this->createMock(\Magento\Framework\View\Element\AbstractBlock::class);
        $this->persistentConfigMock = $this->createMock(\Magento\Persistent\Model\Persistent\Config::class);
        $this->model = new \Magento\Persistent\Observer\ApplyBlockPersistentDataObserver(
            $this->sessionMock,
            $this->persistentHelperMock,
            $this->customerSessionMock,
            $this->configMock
        );
    }

    public function testExecuteWhenSessionNotPersistent()
    {
        $this->sessionMock->expects($this->once())->method('isPersistent')->willReturn(false);
        $this->observerMock->expects($this->never())->method('getEvent');
        $this->model->execute($this->observerMock);
    }

    public function testExecuteForLoggedInAndPersistentCustomer()
    {
        $this->sessionMock->expects($this->once())->method('isPersistent')->willReturn(true);
        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $this->observerMock->expects($this->never())->method('getEvent');
        $this->model->execute($this->observerMock);
    }

    public function testExecuteWhenBlockDoesNotExist()
    {
        $this->sessionMock->expects($this->once())->method('isPersistent')->willReturn(true);
        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->willReturn(false);
        $this->observerMock->expects($this->once())->method('getEvent')->willReturn($this->eventMock);
        $this->eventMock->expects($this->once())->method('getBlock')->willReturn(null);
        $this->eventMock->expects($this->never())->method('getConfigFilePath');
        $this->model->execute($this->observerMock);
    }

    public function testExecuteWhenConfigFilePathDoesNotExist()
    {
        $this->sessionMock->expects($this->once())->method('isPersistent')->willReturn(true);
        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->willReturn(false);
        $this->observerMock
            ->expects($this->any())
            ->method('getEvent')
            ->willReturn($this->eventMock);
        $this->eventMock->expects($this->once())->method('getBlock')->willReturn($this->blockMock);
        $this->eventMock->expects($this->once())->method('getConfigFilePath')->willReturn(false);
        $this->persistentHelperMock
            ->expects($this->once())
            ->method('getPersistentConfigFilePath')
            ->willReturn('path1/path2');
        $this->configMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->persistentConfigMock);
        $this->persistentConfigMock->expects($this->once())->method('setConfigFilePath')->with('path1/path2');
        $this->persistentConfigMock
            ->expects($this->once())
            ->method('getBlockConfigInfo')
            ->with(get_class($this->blockMock))
            ->willReturn(['persistentConfigInfo']);
        $this->persistentConfigMock
            ->expects($this->once())
            ->method('fireOne')
            ->with('persistentConfigInfo', $this->blockMock);
        $this->model->execute($this->observerMock);
    }

    public function testExecute()
    {
        $this->sessionMock->expects($this->once())->method('isPersistent')->willReturn(true);
        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->willReturn(false);
        $this->observerMock
            ->expects($this->any())
            ->method('getEvent')
            ->willReturn($this->eventMock);
        $this->eventMock->expects($this->once())->method('getBlock')->willReturn($this->blockMock);
        $this->eventMock->expects($this->once())->method('getConfigFilePath')->willReturn('path1/path2');
        $this->persistentHelperMock
            ->expects($this->never())
            ->method('getPersistentConfigFilePath');
        $this->configMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->persistentConfigMock);
        $this->persistentConfigMock->expects($this->once())->method('setConfigFilePath')->with('path1/path2');
        $this->persistentConfigMock
            ->expects($this->once())
            ->method('getBlockConfigInfo')
            ->with(get_class($this->blockMock))
            ->willReturn(['persistentConfigInfo']);
        $this->persistentConfigMock
            ->expects($this->once())
            ->method('fireOne')
            ->with('persistentConfigInfo', $this->blockMock);
        $this->model->execute($this->observerMock);
    }
}
