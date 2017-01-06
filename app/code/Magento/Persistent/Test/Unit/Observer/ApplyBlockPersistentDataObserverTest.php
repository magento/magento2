<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Persistent\Test\Unit\Observer;

class ApplyBlockPersistentDataObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Persistent\Observer\ApplyBlockPersistentDataObserver
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
    protected $observerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $blockMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $persistentConfigMock;

    protected function setUp()
    {
        $eventMethods = ['getConfigFilePath', 'getBlock', '__wakeUp'];
        $this->sessionMock = $this->getMock(\Magento\Persistent\Helper\Session::class, [], [], '', false);
        $this->customerSessionMock = $this->getMock(\Magento\Customer\Model\Session::class, [], [], '', false);
        $this->persistentHelperMock = $this->getMock(\Magento\Persistent\Helper\Data::class, [], [], '', false);
        $this->configMock =
            $this->getMock(\Magento\Persistent\Model\Persistent\ConfigFactory::class, ['create'], [], '', false);
        $this->observerMock = $this->getMock(\Magento\Framework\Event\Observer::class, [], [], '', false);
        $this->eventMock = $this->getMock(\Magento\Framework\Event::class, $eventMethods, [], '', false);
        $this->blockMock = $this->getMock(\Magento\Framework\View\Element\AbstractBlock::class, [], [], '', false);
        $this->persistentConfigMock = $this->getMock(
            \Magento\Persistent\Model\Persistent\Config::class,
            [],
            [],
            '',
            false
        );
        $this->model = new \Magento\Persistent\Observer\ApplyBlockPersistentDataObserver(
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
