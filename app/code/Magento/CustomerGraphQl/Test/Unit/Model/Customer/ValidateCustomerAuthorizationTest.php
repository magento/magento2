<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Test\Unit\Model\Customer;

use Magento\CustomerGraphQl\Exception\GraphQlCustomerAuthorizationException;
use Magento\CustomerGraphQl\Model\Customer\ValidateCustomerAuthorization;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\GraphQl\Model\Query\ContextExtensionInterface;
use Magento\GraphQl\Model\Query\ContextInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\CustomerGraphQl\Model\Customer\ValidateCustomerAuthorization
 */
class ValidateCustomerAuthorizationTest extends TestCase
{
    /**
     * @var ValidateCustomerAuthorization
     */
    private $validator;

    /**
     * @var ContextInterface|MockObject
     */
    private $contextMock;

    /**
     * @var ContextExtensionInterface|MockObject
     */
    private $contextExtensionMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(ContextInterface::class);
        $this->contextExtensionMock = $this->createMock(ContextExtensionInterface::class);
        $this->contextMock->method('getExtensionAttributes')->willReturn($this->contextExtensionMock);

        $this->validator = new ValidateCustomerAuthorization();
    }

    /**
     * Authorized customer must pass the validation without exception
     */
    public function testExecuteWithAuthorizedCustomer(): void
    {
        $this->contextExtensionMock->expects($this->once())->method('getIsCustomer')->willReturn(true);

        $this->validator->execute($this->contextMock);
    }

    /**
     * Non-customer request must throw the dedicated authorization exception
     */
    public function testExecuteWithUnauthorizedCustomer(): void
    {
        $this->contextExtensionMock->expects($this->once())->method('getIsCustomer')->willReturn(false);

        $this->expectException(GraphQlCustomerAuthorizationException::class);
        $this->expectExceptionMessage('The current customer isn\'t authorized.');

        $this->validator->execute($this->contextMock);
    }

    /**
     * The dedicated exception keeps the GraphQL authorization contract
     */
    public function testExceptionIsGraphQlAuthorizationException(): void
    {
        $exception = new GraphQlCustomerAuthorizationException();

        $this->assertInstanceOf(GraphQlAuthorizationException::class, $exception);
        $this->assertSame('The current customer isn\'t authorized.', $exception->getMessage());
        $this->assertSame(GraphQlAuthorizationException::EXCEPTION_CATEGORY, $exception->getCategory());
        $this->assertTrue($exception->isClientSafe());
    }
}
