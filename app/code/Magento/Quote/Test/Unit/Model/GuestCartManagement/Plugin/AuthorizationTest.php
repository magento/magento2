<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Test\Unit\Model\GuestCartManagement\Plugin;

use Magento\Quote\Model\GuestCartManagement\Plugin\Authorization;

class AuthorizationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Authorization
     */
    private $plugin;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $userContextMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteManagementMock;

    protected function setUp()
    {
        $this->userContextMock = $this->createMock(\Magento\Authorization\Model\UserContextInterface::class);
        $this->quoteManagementMock = $this->createMock(\Magento\Quote\Model\GuestCart\GuestCartManagement::class);
        $this->plugin = new Authorization(
            $this->userContextMock
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\StateException
     * @expectedExceptionMessage You don't have the correct permissions to assign the customer to the cart.
     */
    public function testBeforeAssignCustomer()
    {
        $this->userContextMock->expects($this->once())->method('getUserId')->willReturn('10');
        $this->plugin->beforeAssignCustomer($this->quoteManagementMock, 1, 2, 1);
    }
}
