<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Unit\Model\Plugin;

use Magento\Customer\Model\Plugin\CustomerChangePassword;

/**
 * Test for \Magento\Customer\Model\Plugin\CustomerChangePassword class.
 */
class CustomerChangePasswordTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CustomerChangePassword
     */
    private $plugin;

    /**
     * @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerSessionMock;

    /**
     * @var \Magento\Customer\Model\AccountManagement|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subject;

    public function setUp()
    {
        $this->customerSessionMock = $this->getMockBuilder(\Magento\Customer\Model\Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['logout'])
            ->getMock();

        $this->subject = $this->getMockBuilder(\Magento\Customer\Model\AccountManagement::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->plugin = new CustomerChangePassword($this->customerSessionMock);
    }

    public function testAfterDispatch()
    {
        $this->customerSessionMock->expects($this->once())
            ->method('logout')
            ->willReturnSelf();

        $this->plugin->afterChangePassword($this->subject, true);
    }
}
