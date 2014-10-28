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

class UpdateCustomerCookiesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Persistent\Model\Observer\UpdateCustomerCookies
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $sessionHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $accountServiceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $observerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $sessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerMock;

    protected function setUp()
    {
        $eventMethods = ['getCustomerCookies', '__wakeUp'];
        $sessionMethods = ['getId', 'getGroupId', 'getCustomerId', '__wakeUp'];
        $this->sessionHelperMock = $this->getMock('Magento\Persistent\Helper\Session', [], [], '', false);
        $this->accountServiceMock = $this->getMock('Magento\Customer\Service\V1\CustomerAccountServiceInterface');
        $this->observerMock = $this->getMock('Magento\Framework\Event\Observer', [], [], '', false);
        $this->eventManagerMock = $this->getMock('\Magento\Framework\Event', $eventMethods, [], '', false);
        $this->sessionMock = $this->getMock('Magento\Persistent\Model\Session', $sessionMethods, [], '', false);
        $this->customerMock = $this->getMock('Magento\Customer\Service\V1\Data\Customer', [], [], '', false);
        $this->model = new \Magento\Persistent\Model\Observer\UpdateCustomerCookies(
          $this->sessionHelperMock,
          $this->accountServiceMock
        );
    }

    public function testExecuteWhenSessionNotPersistent()
    {
        $this->sessionHelperMock->expects($this->once())->method('isPersistent')->will($this->returnValue(false));
        $this->observerMock->expects($this->never())->method('getEvent');
        $this->model->execute($this->observerMock);
    }

    public function testExecuteWhenCustomerCookieExist()
    {
        $customerId = 1;
        $customerGroupId = 2;
        $cookieMock =
            $this->getMock('Magento\Framework\Object',
                ['setCustomerId', 'setCustomerGroupId', '__wakeUp'],
                [], '', false);
        $this->sessionHelperMock->expects($this->once())->method('isPersistent')->will($this->returnValue(true));
        $this->observerMock
            ->expects($this->once())
            ->method('getEvent')
            ->will($this->returnValue($this->eventManagerMock));
        $this->eventManagerMock
            ->expects($this->once())
            ->method('getCustomerCookies')
            ->will($this->returnValue($cookieMock));
        $this->sessionHelperMock
            ->expects($this->once())
            ->method('getSession')
            ->will($this->returnValue($this->sessionMock));
        $this->sessionMock->expects($this->once())->method('getCustomerId')->will($this->returnValue($customerId));
        $this->accountServiceMock
            ->expects($this->once())
            ->method('getCustomer')
            ->will($this->returnValue($this->customerMock));
        $this->customerMock->expects($this->once())->method('getId')->will($this->returnValue($customerId));
        $this->customerMock->expects($this->once())->method('getGroupId')->will($this->returnValue($customerGroupId));
        $cookieMock->expects($this->once())->method('setCustomerId')->with($customerId)->will($this->returnSelf());
        $cookieMock
            ->expects($this->once())
            ->method('setCustomerGroupId')
            ->with($customerGroupId)
            ->will($this->returnSelf());
        $this->model->execute($this->observerMock);
    }
}
