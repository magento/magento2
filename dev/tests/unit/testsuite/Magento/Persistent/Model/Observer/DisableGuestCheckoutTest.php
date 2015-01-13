<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Persistent\Model\Observer;

class DisableGuestCheckoutTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Persistent\Model\Observer\DisableGuestCheckout
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $observerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $sessionHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManagerMock;

    protected function setUp()
    {
        $this->eventManagerMock =
            $this->getMock('Magento\Framework\Event\ManagerInterface', ['getResult', 'dispatch', '__wakeUp']);
        $this->sessionHelperMock = $this->getMock('\Magento\Persistent\Helper\Session', [], [], '', false);
        $this->observerMock = $this->getMock('Magento\Framework\Event\Observer', [], [], '', false);
        $this->model = new \Magento\Persistent\Model\Observer\DisableGuestCheckout(
            $this->sessionHelperMock
        );
    }

    public function testExecuteWithNotPersistentSession()
    {
        $this->sessionHelperMock->expects($this->once())->method('isPersistent')->will($this->returnValue(false));
        $this->observerMock->expects($this->never())->method('getEvent');
        $this->model->execute($this->observerMock);
    }

    public function testExecuteWithPersistentSession()
    {
        $resultMock = $this->getMock('Magento\Framework\Object', ['setIsAllowed', '__wakeUp'], [], '', false);
        $this->sessionHelperMock->expects($this->once())->method('isPersistent')->will($this->returnValue(true));
        $this->observerMock
            ->expects($this->once())
            ->method('getEvent')
            ->will($this->returnValue($this->eventManagerMock));
        $this->eventManagerMock->expects($this->once())->method('getResult')->will($this->returnValue($resultMock));
        $resultMock->expects($this->once())->method('setIsAllowed')->with(false);
        $this->model->execute($this->observerMock);
    }
}
