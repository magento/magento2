<?php
/**
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Persistent\Model\Observer;

class MakePersistentQuoteGuestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Persistent\Model\Observer\MakePersistentQuoteGuest
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $sessionHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $helperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $observerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $actionMock;

    protected function setUp()
    {
        $this->actionMock = $this->getMock('Magento\Persistent\Controller\Index', [], [], '', false);
        $this->observerMock = $this->getMock('Magento\Framework\Event\Observer', [], [], '', false);
        $this->sessionHelperMock = $this->getMock('Magento\Persistent\Helper\Session', [], [], '', false);
        $this->helperMock = $this->getMock('Magento\Persistent\Helper\Data', [], [], '', false);
        $this->customerSessionMock = $this->getMock('Magento\Customer\Model\Session', [], [], '', false);
        $this->quoteManagerMock = $this->getMock('Magento\Persistent\Model\QuoteManager', [], [], '', false);
        $this->eventManagerMock =
            $this->getMock('Magento\Framework\Event', ['getControllerAction', '__wakeUp'], [], '', false);
        $this->observerMock
            ->expects($this->once())
            ->method('getEvent')
            ->will($this->returnValue($this->eventManagerMock));
        $this->model = new \Magento\Persistent\Model\Observer\MakePersistentQuoteGuest(
            $this->sessionHelperMock,
            $this->helperMock,
            $this->customerSessionMock,
            $this->quoteManagerMock
        );
    }

    public function testExecute()
    {
        $this->eventManagerMock
            ->expects($this->once())
            ->method('getControllerAction')
            ->will($this->returnValue($this->actionMock));
        $this->sessionHelperMock->expects($this->once())->method('isPersistent')->will($this->returnValue(true));
        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->will($this->returnValue(false));
        $this->helperMock->expects($this->never())->method('isShoppingCartPersist');
        $this->quoteManagerMock->expects($this->once())->method('setGuest')->with(true);
        $this->model->execute($this->observerMock);
    }

    public function testExecuteWhenShoppingCartIsPersist()
    {
        $this->eventManagerMock
            ->expects($this->once())
            ->method('getControllerAction')
            ->will($this->returnValue($this->actionMock));
        $this->sessionHelperMock->expects($this->once())->method('isPersistent')->will($this->returnValue(true));
        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->will($this->returnValue(true));
        $this->helperMock->expects($this->once())->method('isShoppingCartPersist')->will($this->returnValue(true));
        $this->quoteManagerMock->expects($this->once())->method('setGuest')->with(true);
        $this->model->execute($this->observerMock);
    }

    public function testExecuteWhenShoppingCartIsNotPersist()
    {
        $this->eventManagerMock
            ->expects($this->once())
            ->method('getControllerAction')
            ->will($this->returnValue($this->actionMock));
        $this->sessionHelperMock->expects($this->once())->method('isPersistent')->will($this->returnValue(true));
        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->will($this->returnValue(true));
        $this->helperMock->expects($this->once())->method('isShoppingCartPersist')->will($this->returnValue(false));
        $this->quoteManagerMock->expects($this->never())->method('setGuest');
        $this->model->execute($this->observerMock);
    }
}
