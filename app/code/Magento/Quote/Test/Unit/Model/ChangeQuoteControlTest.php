<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\ChangeQuoteControl;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for \Magento\Quote\Model\ChangeQuoteControl
 */
class ChangeQuoteControlTest extends TestCase
{
    /**
     * @var ChangeQuoteControl
     */
    protected $model;

    /**
     * @var MockObject|UserContextInterface
     */
    protected $userContextMock;

    /**
     * @var MockObject|CartInterface
     */
    protected $quoteMock;

    protected function setUp(): void
    {
        $this->userContextMock = $this->getMockForAbstractClass(UserContextInterface::class);

        $this->model = new ChangeQuoteControl($this->userContextMock);

        $this->quoteMock = $this->getMockBuilder(CartInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(['getCustomerId'])
            ->getMockForAbstractClass();
    }

    public function testIsAllowedIfTheQuoteIsBelongedToCustomer()
    {
        $quoteCustomerId = 1;
        $this->quoteMock->method('getCustomerId')
            ->willReturn($quoteCustomerId);
        $this->userContextMock->method('getUserType')
            ->willReturn(UserContextInterface::USER_TYPE_CUSTOMER);
        $this->userContextMock->method('getUserId')
            ->willReturn($quoteCustomerId);

        $this->assertTrue($this->model->isAllowed($this->quoteMock));
    }

    public function testIsAllowedIfTheQuoteIsNotBelongedToCustomer()
    {
        $currentCustomerId = 1;
        $quoteCustomerId = 2;

        $this->quoteMock->method('getCustomerId')
            ->willReturn($quoteCustomerId);
        $this->userContextMock->method('getUserType')
            ->willReturn(UserContextInterface::USER_TYPE_CUSTOMER);
        $this->userContextMock->method('getUserId')
            ->willReturn($currentCustomerId);

        $this->assertFalse($this->model->isAllowed($this->quoteMock));
    }

    public function testIsAllowedIfQuoteIsBelongedToGuestAndContextIsGuest()
    {
        $quoteCustomerId = null;
        $this->quoteMock->method('getCustomerId')
            ->willReturn($quoteCustomerId);
        $this->userContextMock->method('getUserType')
            ->willReturn(UserContextInterface::USER_TYPE_GUEST);
        $this->assertTrue($this->model->isAllowed($this->quoteMock));
    }

    public function testIsAllowedIfQuoteIsBelongedToCustomerAndContextIsGuest()
    {
        $quoteCustomerId = 1;
        $this->quoteMock->method('getCustomerId')
            ->willReturn($quoteCustomerId);
        $this->userContextMock->method('getUserType')
            ->willReturn(UserContextInterface::USER_TYPE_GUEST);
        $this->assertFalse($this->model->isAllowed($this->quoteMock));
    }

    public function testIsAllowedIfContextIsAdmin()
    {
        $this->userContextMock->method('getUserType')
            ->willReturn(UserContextInterface::USER_TYPE_ADMIN);
        $this->assertTrue($this->model->isAllowed($this->quoteMock));
    }

    public function testIsAllowedIfContextIsIntegration()
    {
        $this->userContextMock->method('getUserType')
            ->willReturn(UserContextInterface::USER_TYPE_INTEGRATION);
        $this->assertTrue($this->model->isAllowed($this->quoteMock));
    }
}
