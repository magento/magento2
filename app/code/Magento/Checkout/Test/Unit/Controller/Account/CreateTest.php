<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\Unit\Controller\Account;

/**
 * Shopping cart edit tests
 */
class CreateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSession;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $checkoutSession;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManager;

    /**
     * @var \Magento\Checkout\Controller\Account\Create
     */
    protected $action;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderCustomerService;

    public function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->checkoutSession = $this->getMock('\Magento\Checkout\Model\Session', ['getLastOrderId'], [], '', false);
        $this->customerSession = $this->getMock('\Magento\Customer\Model\Session', [], [], '', false);
        $this->orderCustomerService = $this->getMock(
            '\Magento\Sales\Api\OrderCustomerManagementInterface',
            [],
            [],
            '',
            false
        );
        $this->messageManager = $this->getMock('\Magento\Framework\Message\ManagerInterface');

        $this->action = $objectManagerHelper->getObject(
            'Magento\Checkout\Controller\Account\Create',
            [
                'checkoutSession' => $this->checkoutSession,
                'customerSession' => $this->customerSession,
                'orderCustomerService' => $this->orderCustomerService,
                'messageManager' => $this->messageManager

            ]
        );
    }

    public function testExecuteAddsSessionMessageIfCustomerIsLoggedIn()
    {
        $this->customerSession->expects($this->once())->method('isLoggedIn')->will($this->returnValue(true));
        $this->messageManager->expects($this->once())->method('addError')->with();
        $this->action->execute();
    }

    public function testExecute()
    {
        $this->customerSession->expects($this->once())->method('isLoggedIn')->will($this->returnValue(false));
        $this->checkoutSession->expects($this->once())->method('getLastOrderId')->will($this->returnValue(100));
        $customer = $this->getMock('\Magento\Customer\Api\Data\CustomerInterface');
        $this->orderCustomerService->expects($this->once())->method('create')->with(100)->will(
            $this->returnValue($customer)
        );
        $this->action->execute();
    }
}
