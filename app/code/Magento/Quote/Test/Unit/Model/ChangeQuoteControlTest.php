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
 *
 * Class \Magento\Quote\Test\Unit\Model\ChangeQuoteControlTest
 */
class ChangeQuoteControlTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var ChangeQuoteControl
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $userContextMock;

    /**
     * @var MockObject
     */
    protected $quoteMock;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->userContextMock = $this->getMockForAbstractClass(UserContextInterface::class);

        $this->model = $this->objectManager->getObject(
            ChangeQuoteControl::class,
            [
                'userContext' => $this->userContextMock
            ]
        );

        $this->quoteMock = $this->getMockForAbstractClass(
            CartInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getCustomerId']
        );
    }

    /**
     * Test if the quote is belonged to customer
     */
    public function testIsAllowedIfTheQuoteIsBelongedToCustomer()
    {
        $quoteCustomerId = 1;
        $this->quoteMock->expects($this->any())->method('getCustomerId')
            ->willReturn($quoteCustomerId);
        $this->userContextMock->expects($this->any())->method('getUserType')
            ->willReturn(UserContextInterface::USER_TYPE_CUSTOMER);
        $this->userContextMock->expects($this->any())->method('getUserId')
            ->willReturn($quoteCustomerId);

        $this->assertTrue($this->model->isAllowed($this->quoteMock));
    }

    /**
     * Test if the quote is not belonged to customer
     */
    public function testIsAllowedIfTheQuoteIsNotBelongedToCustomer()
    {
        $currentCustomerId = 1;
        $quoteCustomerId = 2;

        $this->quoteMock->expects($this->any())->method('getCustomerId')
            ->willReturn($quoteCustomerId);
        $this->userContextMock->expects($this->any())->method('getUserType')
            ->willReturn(UserContextInterface::USER_TYPE_CUSTOMER);
        $this->userContextMock->expects($this->any())->method('getUserId')
            ->willReturn($currentCustomerId);

        $this->assertFalse($this->model->isAllowed($this->quoteMock));
    }

    /**
     * Test if the quote is belonged to guest and the context is guest
     */
    public function testIsAllowedIfQuoteIsBelongedToGuestAndContextIsGuest()
    {
        $quoteCustomerId = null;
        $this->quoteMock->expects($this->any())->method('getCustomerId')
            ->willReturn($quoteCustomerId);
        $this->userContextMock->expects($this->any())->method('getUserType')
            ->willReturn(UserContextInterface::USER_TYPE_GUEST);
        $this->assertTrue($this->model->isAllowed($this->quoteMock));
    }

    /**
     * Test if the quote is belonged to customer and the context is guest
     */
    public function testIsAllowedIfQuoteIsBelongedToCustomerAndContextIsGuest()
    {
        $quoteCustomerId = 1;
        $this->quoteMock->expects($this->any())->method('getCustomerId')
            ->willReturn($quoteCustomerId);
        $this->userContextMock->expects($this->any())->method('getUserType')
            ->willReturn(UserContextInterface::USER_TYPE_GUEST);
        $this->assertFalse($this->model->isAllowed($this->quoteMock));
    }

    /**
     * Test if the context is admin
     */
    public function testIsAllowedIfContextIsAdmin()
    {
        $this->userContextMock->expects($this->any())->method('getUserType')
            ->willReturn(UserContextInterface::USER_TYPE_ADMIN);
        $this->assertTrue($this->model->isAllowed($this->quoteMock));
    }

    /**
     * Test if the context is integration
     */
    public function testIsAllowedIfContextIsIntegration()
    {
        $this->userContextMock->expects($this->any())->method('getUserType')
            ->willReturn(UserContextInterface::USER_TYPE_INTEGRATION);
        $this->assertTrue($this->model->isAllowed($this->quoteMock));
    }
}
