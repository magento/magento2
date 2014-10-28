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

class EmulateQuoteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Persistent\Model\Observer\EmulateQuote
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerAccountMock;

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
    protected $checkoutSessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $sessionMock;

    protected function setUp()
    {
        $eventMethods = ['getRequest', 'dispatch', '__wakeUp'];
        $this->customerAccountMock = $this->getMock('Magento\Customer\Service\V1\CustomerAccountServiceInterface');
        $this->customerSessionMock = $this->getMock('Magento\Customer\Model\Session', [], [], '', false);
        $this->sessionHelperMock = $this->getMock('Magento\Persistent\Helper\Session', [], [], '', false);
        $this->helperMock = $this->getMock('Magento\Persistent\Helper\Data', [], [], '', false);
        $this->observerMock = $this->getMock('Magento\Framework\Event\Observer', [], [], '', false);
        $this->checkoutSessionMock = $this->getMock('\Magento\Checkout\Model\Session', [], [], '', false);
        $this->eventMock = $this->getMock('Magento\Framework\Event', $eventMethods, [], '', false);
        $this->requestMock = $this->getMock('\Magento\Framework\App\Request\Http', [], [], '', false);
        $this->customerMock = $this->getMock('Magento\Customer\Service\V1\Data\Customer', [], [], '', false);
        $this->sessionMock =
            $this->getMock('\Magento\Persistent\Model\Session', ['getCustomerId', '__wakeUp'], [], '', false);
        $this->model = new \Magento\Persistent\Model\Observer\EmulateQuote(
            $this->sessionHelperMock,
            $this->helperMock,
            $this->checkoutSessionMock,
            $this->customerSessionMock,
            $this->customerAccountMock
        );
    }

    public function testExecuteWhenCannotProcess()
    {
        $this->helperMock
            ->expects($this->once())
            ->method('canProcess')
            ->with($this->observerMock)
            ->will($this->returnValue(false));
        $this->sessionHelperMock->expects($this->never())->method('isPersistent');
        $this->observerMock->expects($this->never())->method('getEvent');
        $this->model->execute($this->observerMock);
    }

    public function testExecuteWhenSessionIsNotPersistent()
    {
        $this->helperMock
            ->expects($this->once())
            ->method('canProcess')
            ->with($this->observerMock)
            ->will($this->returnValue(true));
        $this->sessionHelperMock->expects($this->once())->method('isPersistent')->will($this->returnValue(false));
        $this->checkoutSessionMock->expects($this->never())->method('isLoggedIn');
        $this->observerMock->expects($this->never())->method('getEvent');
        $this->model->execute($this->observerMock);
    }

    public function testExecuteWhenCustomerLoggedIn()
    {
        $this->helperMock
            ->expects($this->once())
            ->method('canProcess')
            ->with($this->observerMock)
            ->will($this->returnValue(true));
        $this->sessionHelperMock->expects($this->once())->method('isPersistent')->will($this->returnValue(true));
        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->will($this->returnValue(true));
        $this->observerMock->expects($this->never())->method('getEvent');
        $this->model->execute($this->observerMock);
    }

    public function testExecuteWhenActionIsStop()
    {
        $this->helperMock
            ->expects($this->once())
            ->method('canProcess')
            ->with($this->observerMock)
            ->will($this->returnValue(true));
        $this->sessionHelperMock->expects($this->once())->method('isPersistent')->will($this->returnValue(true));
        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->will($this->returnValue(false));
        $this->observerMock->expects($this->once())->method('getEvent')->will($this->returnValue($this->eventMock));
        $this->eventMock->expects($this->once())->method('getRequest')->will($this->returnValue($this->requestMock));
        $this->requestMock
            ->expects($this->once())
            ->method('getFullActionName')
            ->will($this->returnValue('persistent_index_saveMethod'));
        $this->helperMock->expects($this->never())->method('isShoppingCartPersist');
        $this->model->execute($this->observerMock);
    }

    public function testExecuteWhenShoppingCartIsPersistent()
    {
        $customerId = 1;
        $quoteMock = $this->getMock('\Magento\Sales\Model\Quote', [], [], '', false);
        $this->helperMock
            ->expects($this->once())
            ->method('canProcess')
            ->with($this->observerMock)
            ->will($this->returnValue(true));
        $this->sessionHelperMock->expects($this->once())->method('isPersistent')->will($this->returnValue(true));
        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->will($this->returnValue(false));
        $this->observerMock->expects($this->once())->method('getEvent')->will($this->returnValue($this->eventMock));
        $this->eventMock->expects($this->once())->method('getRequest')->will($this->returnValue($this->requestMock));
        $this->requestMock
            ->expects($this->once())
            ->method('getFullActionName')
            ->will($this->returnValue('method_name'));
        $this->helperMock
            ->expects($this->once())
            ->method('isShoppingCartPersist')
            ->will($this->returnValue(true));
        $this->sessionHelperMock
            ->expects($this->once())
            ->method('getSession')
            ->will($this->returnValue($this->sessionMock));
        $this->sessionMock->expects($this->once())->method('getCustomerId')->will($this->returnValue($customerId));
        $this->customerAccountMock
            ->expects($this->once())
            ->method('getCustomer')
            ->with($customerId)
            ->will($this->returnValue($this->customerMock));
        $this->checkoutSessionMock->expects($this->once())->method('hasQuote')->will($this->returnValue(false));
        $this->checkoutSessionMock->expects($this->once())->method('getQuote')->will($this->returnValue($quoteMock));
        $this->checkoutSessionMock->expects($this->once())->method('setCustomerData')->with($this->customerMock);
        $this->model->execute($this->observerMock);
    }

    public function testExecuteWhenShoppingCartIsNotPersistent()
    {
        $this->helperMock
            ->expects($this->once())
            ->method('canProcess')
            ->with($this->observerMock)
            ->will($this->returnValue(true));
        $this->sessionHelperMock->expects($this->once())->method('isPersistent')->will($this->returnValue(true));
        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->will($this->returnValue(false));
        $this->observerMock->expects($this->once())->method('getEvent')->will($this->returnValue($this->eventMock));
        $this->eventMock->expects($this->once())->method('getRequest')->will($this->returnValue($this->requestMock));
        $this->requestMock
            ->expects($this->once())
            ->method('getFullActionName')
            ->will($this->returnValue('method_name'));
        $this->helperMock
            ->expects($this->once())
            ->method('isShoppingCartPersist')
            ->will($this->returnValue(false));
        $this->checkoutSessionMock->expects($this->never())->method('setCustomerData');
        $this->model->execute($this->observerMock);
    }

    public function testExecuteWhenShoppingCartIsPersistentAndQuoteExist()
    {
        $customerId = 1;
        $this->helperMock
            ->expects($this->once())
            ->method('canProcess')
            ->with($this->observerMock)
            ->will($this->returnValue(true));
        $this->sessionHelperMock->expects($this->once())->method('isPersistent')->will($this->returnValue(true));
        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->will($this->returnValue(false));
        $this->observerMock->expects($this->once())->method('getEvent')->will($this->returnValue($this->eventMock));
        $this->eventMock->expects($this->once())->method('getRequest')->will($this->returnValue($this->requestMock));
        $this->requestMock
            ->expects($this->once())
            ->method('getFullActionName')
            ->will($this->returnValue('method_name'));
        $this->helperMock
            ->expects($this->once())
            ->method('isShoppingCartPersist')
            ->will($this->returnValue(true));
        $this->sessionHelperMock
            ->expects($this->once())
            ->method('getSession')
            ->will($this->returnValue($this->sessionMock));
        $this->sessionMock->expects($this->once())->method('getCustomerId')->will($this->returnValue($customerId));
        $this->customerAccountMock
            ->expects($this->once())
            ->method('getCustomer')
            ->with($customerId)
            ->will($this->returnValue($this->customerMock));
        $this->checkoutSessionMock->expects($this->once())->method('hasQuote')->will($this->returnValue(true));
        $this->checkoutSessionMock->expects($this->once())->method('setCustomerData')->with($this->customerMock);
        $this->model->execute($this->observerMock);
    }
}
