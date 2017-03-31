<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Test\Unit\Model\QuoteRepository\Plugin;

use Magento\Quote\Model\QuoteRepository\Plugin\Authorization;
use Magento\Authorization\Model\UserContextInterface;

/**
 * Class AuthorizationTest
 */
class AuthorizationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Quote\Model\QuoteRepository\Plugin\Authorization
     */
    private $authorization;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Authorization\Model\UserContextInterface
     */
    private $userContextMock;

    protected function setUp()
    {
        $this->userContextMock = $this->getMock(\Magento\Authorization\Model\UserContextInterface::class);
        $this->authorization = new Authorization($this->userContextMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity
     */
    public function testAfterGetActiveThrowsExceptionIfQuoteIsNotAllowedForCurrentUserContext()
    {
        // Quote without customer ID
        $quoteMock = $this->getMock(\Magento\Quote\Model\Quote::class, ['getCustomerId'], [], '', false);
        $quoteRepositoryMock = $this->getMock(\Magento\Quote\Api\CartRepositoryInterface::class);
        $this->userContextMock->expects($this->any())
            ->method('getUserType')
            ->willReturn(UserContextInterface::USER_TYPE_CUSTOMER);
        $this->userContextMock->expects($this->any())->method('getUserId')->willReturn(1);
        $quoteMock->expects($this->exactly(2))->method('getCustomerId')->willReturn(2);
        $this->authorization->afterGetActive($quoteRepositoryMock, $quoteMock);
    }

    public function testAfterGetActiveReturnsQuoteIfQuoteIsAllowedForCurrentUserContext()
    {
        $quoteMock = $this->getMock(\Magento\Quote\Model\Quote::class, [], [], '', false);
        $quoteRepositoryMock = $this->getMock(\Magento\Quote\Api\CartRepositoryInterface::class);
        $this->userContextMock->expects($this->any())
            ->method('getUserType')
            ->willReturn(UserContextInterface::USER_TYPE_GUEST);
        $this->assertEquals($quoteMock, $this->authorization->afterGetActive($quoteRepositoryMock, $quoteMock));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity
     */
    public function testAfterGetActiveForCustomerThrowsExceptionIfQuoteIsNotAllowedForCurrentUserContext()
    {
        // Quote without customer ID
        $quoteMock = $this->getMock(\Magento\Quote\Model\Quote::class, ['getCustomerId'], [], '', false);
        $quoteRepositoryMock = $this->getMock(\Magento\Quote\Api\CartRepositoryInterface::class);
        $this->userContextMock->expects($this->any())->method('getUserType')->willReturn(
            UserContextInterface::USER_TYPE_CUSTOMER
        );
        $quoteMock->expects($this->exactly(2))->method('getCustomerId')->willReturn(2);
        $this->userContextMock->expects($this->any())->method('getUserId')->willReturn(1);
        $this->authorization->afterGetActive($quoteRepositoryMock, $quoteMock);
    }

    public function testAfterGetActiveForCustomerReturnsQuoteIfQuoteIsAllowedForCurrentUserContext()
    {
        $quoteMock = $this->getMock(\Magento\Quote\Model\Quote::class, [], [], '', false);
        $quoteRepositoryMock = $this->getMock(\Magento\Quote\Api\CartRepositoryInterface::class);
        $this->userContextMock->expects($this->any())
            ->method('getUserType')
            ->willReturn(UserContextInterface::USER_TYPE_GUEST);
        $this->assertEquals($quoteMock, $this->authorization->afterGetActive($quoteRepositoryMock, $quoteMock));
    }
}
