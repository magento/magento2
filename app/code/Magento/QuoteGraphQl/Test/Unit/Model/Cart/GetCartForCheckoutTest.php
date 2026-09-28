<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\QuoteGraphQl\Test\Unit\Model\Cart;

use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\QuoteGraphQl\Model\Cart\GetCartForCheckout;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Magento\QuoteGraphQl\Model\Cart\CheckCartCheckoutAllowance;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class GetCartForCheckoutTest extends TestCase
{
    /**
     * Verifies that when GetCartForUser::execute throws a NoSuchEntityException with a specific code,
     * GetCartForCheckout::execute rethrows it as a GraphQlNoSuchEntityException, preserving the original
     * exception message and code.
     *
     * @return void
     * @throws GraphQlNoSuchEntityException
     * @throws GraphQlAuthorizationException
     * @throws GraphQlInputException
     * @throws Exception
     */
    public function testExecuteThrowsGraphQlNoSuchEntityExceptionWithOriginalCode()
    {
        $cartHash = 'test_hash';
        $customerId = 1;
        $storeId = 2;
        $originalCode = 1234;
        $originalMessage = 'Cart not found';

        $getCartForUser = $this->createMock(GetCartForUser::class);
        $getCartForUser->method('execute')
            ->willThrowException(new NoSuchEntityException(__($originalMessage), null, $originalCode));

        $checkoutAllowance = $this->createMock(CheckCartCheckoutAllowance::class);

        $getCartForCheckout = new GetCartForCheckout($checkoutAllowance, $getCartForUser);

        $this->expectException(GraphQlNoSuchEntityException::class);
        $this->expectExceptionMessage($originalMessage);
        $this->expectExceptionCode($originalCode);

        $getCartForCheckout->execute($cartHash, $customerId, $storeId);
    }
}
