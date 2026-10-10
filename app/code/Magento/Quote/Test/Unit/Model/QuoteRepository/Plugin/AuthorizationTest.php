<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\QuoteRepository\Plugin;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteRepository\Plugin\Authorization;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AuthorizationTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var Authorization
     */
    private $authorization;

    /**
     * @var MockObject|UserContextInterface
     */
    private $userContextMock;

    protected function setUp(): void
    {
        $this->userContextMock = $this->createMock(UserContextInterface::class);
        $this->authorization = new Authorization($this->userContextMock);
    }

    public function testAfterGetActiveThrowsExceptionIfQuoteIsNotAllowedForCurrentUserContext()
    {
        $this->expectException('Magento\Framework\Exception\NoSuchEntityException');
        $this->expectExceptionMessage('No such entity');
        // Quote without customer ID
        $quoteMock = $this->createPartialMockWithReflection(Quote::class, ['getCustomerId']);
        $quoteRepositoryMock = $this->createMock(CartRepositoryInterface::class);
        $this->userContextMock->method('getUserType')->willReturn(UserContextInterface::USER_TYPE_CUSTOMER);
        $this->userContextMock->method('getUserId')->willReturn(1);
        $quoteMock->expects($this->exactly(2))->method('getCustomerId')->willReturn(2);
        $this->authorization->afterGetActive($quoteRepositoryMock, $quoteMock);
    }

    public function testAfterGetActiveReturnsQuoteIfQuoteIsAllowedForCurrentUserContext()
    {
        $quoteMock = $this->createPartialMockWithReflection(Quote::class, ['getCustomerId']);
        $quoteRepositoryMock = $this->createMock(CartRepositoryInterface::class);
        $this->userContextMock->method('getUserType')->willReturn(UserContextInterface::USER_TYPE_GUEST);
        $this->assertEquals($quoteMock, $this->authorization->afterGetActive($quoteRepositoryMock, $quoteMock));
    }

    public function testAfterGetActiveForCustomerThrowsExceptionIfQuoteIsNotAllowedForCurrentUserContext()
    {
        $this->expectException('Magento\Framework\Exception\NoSuchEntityException');
        $this->expectExceptionMessage('No such entity');
        // Quote without customer ID
        $quoteMock = $this->createPartialMockWithReflection(Quote::class, ['getCustomerId']);
        $quoteRepositoryMock = $this->createMock(CartRepositoryInterface::class);
        $this->userContextMock->method('getUserType')->willReturn(
            UserContextInterface::USER_TYPE_CUSTOMER
        );
        $quoteMock->expects($this->exactly(2))->method('getCustomerId')->willReturn(2);
        $this->userContextMock->method('getUserId')->willReturn(1);
        $this->authorization->afterGetActive($quoteRepositoryMock, $quoteMock);
    }

    public function testAfterGetActiveForCustomerReturnsQuoteIfQuoteIsAllowedForCurrentUserContext()
    {
        $quoteMock = $this->createMock(Quote::class);
        $quoteRepositoryMock = $this->createMock(CartRepositoryInterface::class);
        $this->userContextMock->method('getUserType')->willReturn(UserContextInterface::USER_TYPE_GUEST);
        $this->assertEquals($quoteMock, $this->authorization->afterGetActive($quoteRepositoryMock, $quoteMock));
    }
}
