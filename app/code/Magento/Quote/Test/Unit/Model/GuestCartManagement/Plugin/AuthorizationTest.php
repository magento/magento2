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
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $userContextMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $quoteManagementMock;

    protected function setUp(): void
    {
        $this->userContextMock = $this->createMock(\Magento\Authorization\Model\UserContextInterface::class);
        $this->quoteManagementMock = $this->createMock(\Magento\Quote\Model\GuestCart\GuestCartManagement::class);
        $this->plugin = new Authorization(
            $this->userContextMock
        );
    }

    /**
     */
    public function testBeforeAssignCustomer()
    {
        $this->expectException(\Magento\Framework\Exception\StateException::class);
        $this->expectExceptionMessage('You don\'t have the correct permissions to assign the customer to the cart.');

        $this->userContextMock->expects($this->once())->method('getUserId')->willReturn('10');
        $this->plugin->beforeAssignCustomer($this->quoteManagementMock, 1, 2, 1);
    }
}
