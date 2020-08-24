<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\QuoteRepository\Plugin;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Exception\StateException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\ChangeQuoteControl;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteRepository;
use Magento\Quote\Model\QuoteRepository\Plugin\AccessChangeQuoteControl;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AccessChangeQuoteControlTest extends TestCase
{
    /**
     * @var AccessChangeQuoteControl
     */
    private $accessChangeQuoteControl;

    /**
     * @var UserContextInterface|MockObject
     */
    private $userContextMock;

    /**
     * @var Quote|MockObject
     */
    private $quoteMock;

    /**
     * @var QuoteRepository|MockObject
     */
    private $quoteRepositoryMock;

    /**
     * @var ChangeQuoteControl|MockObject
     */
    private $changeQuoteControlMock;

    protected function setUp(): void
    {
        $this->userContextMock = $this->getMockBuilder(UserContextInterface::class)
            ->getMockForAbstractClass();
        $this->userContextMock->method('getUserId')
            ->willReturn(1);

        $this->quoteMock = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->addMethods(['getCustomerId'])
            ->getMock();

        $this->quoteRepositoryMock = $this->getMockBuilder(QuoteRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->changeQuoteControlMock = $this->getMockBuilder(ChangeQuoteControl::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->accessChangeQuoteControl = new AccessChangeQuoteControl($this->changeQuoteControlMock);
    }

    public function testBeforeSaveForCustomerWithCustomerIdMatchinQuoteUserIdIsAllowed()
    {
        $this->quoteMock->method('getCustomerId')
            ->willReturn(1);

        $this->userContextMock->method('getUserType')
            ->willReturn(UserContextInterface::USER_TYPE_CUSTOMER);

        $this->changeQuoteControlMock->method('isAllowed')
            ->willReturn(true);

        $this->accessChangeQuoteControl->beforeSave($this->quoteRepositoryMock, $this->quoteMock);
    }

    public function testBeforeSaveThrowsExceptionForCustomerWithCustomerIdNotMatchingQuoteUserId()
    {
        $this->expectException(StateException::class);
        $this->expectExceptionMessage('Invalid state change requested');
        $this->quoteMock->method('getCustomerId')
            ->willReturn(2);

        $this->userContextMock->method('getUserType')
            ->willReturn(UserContextInterface::USER_TYPE_CUSTOMER);

        $this->changeQuoteControlMock->method('isAllowed')
            ->willReturn(false);

        $this->accessChangeQuoteControl->beforeSave($this->quoteRepositoryMock, $this->quoteMock);
    }

    public function testBeforeSaveForAdminUserRoleIsAllowed()
    {
        $this->quoteMock->method('getCustomerId')
            ->willReturn(2);

        $this->userContextMock->method('getUserType')
            ->willReturn(UserContextInterface::USER_TYPE_ADMIN);

        $this->changeQuoteControlMock->method('isAllowed')
            ->willReturn(true);

        $this->accessChangeQuoteControl->beforeSave($this->quoteRepositoryMock, $this->quoteMock);
    }

    public function testBeforeSaveForGuestIsAllowed()
    {
        $this->quoteMock->method('getCustomerId')
            ->willReturn(null);

        $this->userContextMock->method('getUserType')
            ->willReturn(UserContextInterface::USER_TYPE_GUEST);

        $this->changeQuoteControlMock->method('isAllowed')
            ->willReturn(true);

        $this->accessChangeQuoteControl->beforeSave($this->quoteRepositoryMock, $this->quoteMock);
    }

    public function testBeforeSaveThrowsExceptionForGuestDoesNotEquals()
    {
        $this->expectException(StateException::class);
        $this->expectExceptionMessage('Invalid state change requested');
        $this->quoteMock->method('getCustomerId')
            ->willReturn(1);

        $this->userContextMock->method('getUserType')
            ->willReturn(UserContextInterface::USER_TYPE_GUEST);

        $this->changeQuoteControlMock->method('isAllowed')
            ->willReturn(false);

        $this->accessChangeQuoteControl->beforeSave($this->quoteRepositoryMock, $this->quoteMock);
    }

    public function testBeforeSaveThrowsExceptionForUnknownUserType()
    {
        $this->expectException(StateException::class);
        $this->expectExceptionMessage('Invalid state change requested');
        $this->quoteMock->method('getCustomerId')
            ->willReturn(2);

        $this->userContextMock->method('getUserType')
            ->willReturn(10);

        $this->changeQuoteControlMock->method('isAllowed')
            ->willReturn(false);

        $this->accessChangeQuoteControl->beforeSave($this->quoteRepositoryMock, $this->quoteMock);
    }
}
