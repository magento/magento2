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

class CustomerAuthenticatedEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Persistent\Model\Observer\CustomerAuthenticatedEvent
     */
    protected $model;

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
    protected $quoteManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;


    protected function setUp()
    {
        $this->customerSessionMock = $this->getMock('Magento\Customer\Model\Session', [], [], '', false);
        $this->observerMock = $this->getMock('Magento\Framework\Event\Observer', [], [], '', false);
        $this->quoteManagerMock = $this->getMock('Magento\Persistent\Model\QuoteManager', [], [], '', false);
        $this->requestMock = $this->getMock('Magento\Framework\App\RequestInterface');
        $this->model = new \Magento\Persistent\Model\Observer\CustomerAuthenticatedEvent(
            $this->customerSessionMock,
            $this->requestMock,
            $this->quoteManagerMock
        );
    }

    public function testExecute()
    {
        $this->customerSessionMock
            ->expects($this->once())
            ->method('setCustomerId')
            ->with(null)
            ->will($this->returnSelf());
        $this->customerSessionMock
            ->expects($this->once())
            ->method('setCustomerGroupId')
            ->with(null)
            ->will($this->returnSelf());
        $this->requestMock
            ->expects($this->once())
            ->method('getParam')
            ->with('context')
            ->will($this->returnValue('not_checkout'));
        $this->quoteManagerMock->expects($this->once())->method('expire');
        $this->quoteManagerMock->expects($this->never())->method('setGuest');
        $this->model->execute($this->observerMock);
    }

    public function testExecuteDuringCheckout()
    {
        $this->customerSessionMock
            ->expects($this->once())
            ->method('setCustomerId')
            ->with(null)
            ->will($this->returnSelf());
        $this->customerSessionMock
            ->expects($this->once())
            ->method('setCustomerGroupId')
            ->with(null)
            ->will($this->returnSelf());
        $this->requestMock
            ->expects($this->once())
            ->method('getParam')
            ->with('context')
            ->will($this->returnValue('checkout'));
        $this->quoteManagerMock->expects($this->never())->method('expire');
        $this->quoteManagerMock->expects($this->once())->method('setGuest');
        $this->model->execute($this->observerMock);
    }
}
