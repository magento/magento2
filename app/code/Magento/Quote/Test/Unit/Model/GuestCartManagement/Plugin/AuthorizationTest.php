<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\GuestCartManagement\Plugin;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Quote\Model\GuestCart\GuestCartManagement;
use Magento\Quote\Model\GuestCartManagement\Plugin\Authorization;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AuthorizationTest extends TestCase
{
    /**
     * @var Authorization
     */
    private $plugin;

    /**
     * @var MockObject
     */
    private $userContextMock;

    /**
     * @var MockObject
     */
    private $quoteManagementMock;

    protected function setUp(): void
    {
        $this->userContextMock = $this->getMockForAbstractClass(UserContextInterface::class);
        $this->quoteManagementMock = $this->createMock(GuestCartManagement::class);
        $this->plugin = new Authorization(
            $this->userContextMock
        );
    }

    public function testBeforeAssignCustomer()
    {
        $this->expectException('Magento\Framework\Exception\StateException');
        $this->expectExceptionMessage('You don\'t have the correct permissions to assign the customer to the cart.');
        $this->userContextMock->expects($this->once())->method('getUserId')->willReturn('10');
        $this->plugin->beforeAssignCustomer($this->quoteManagementMock, 1, 2, 1);
    }
}
