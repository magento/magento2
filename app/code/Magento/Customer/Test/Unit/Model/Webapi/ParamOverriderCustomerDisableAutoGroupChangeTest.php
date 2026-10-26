<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Webapi;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Webapi\ParamOverriderCustomerDisableAutoGroupChange;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for Magento\Customer\Model\Webapi\ParamOverriderCustomerDisableAutoGroupChange class.
 */
class ParamOverriderCustomerDisableAutoGroupChangeTest extends TestCase
{
    /**
     * @var UserContextInterface|MockObject
     */
    private $userContextMock;

    /**
     * @var CustomerRepositoryInterface|MockObject
     */
    private $customerRepositoryMock;

    /**
     * @var ParamOverriderCustomerDisableAutoGroupChange
     */
    private $model;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->userContextMock = $this->createMock(UserContextInterface::class);
        $this->customerRepositoryMock = $this->createMock(CustomerRepositoryInterface::class);
        $this->model = new ParamOverriderCustomerDisableAutoGroupChange(
            $this->userContextMock,
            $this->customerRepositoryMock
        );
    }

    /**
     * A customer always gets the value stored on their own account, whatever the request sent.
     *
     * @param mixed $stored
     * @param int $expected
     * @return void
     */
    #[DataProvider('storedValueDataProvider')]
    public function testGetOverriddenValueIsCustomer($stored, int $expected): void
    {
        $customerMock = $this->createMock(CustomerInterface::class);
        $this->userContextMock->expects($this->once())
            ->method('getUserType')
            ->willReturn(UserContextInterface::USER_TYPE_CUSTOMER);
        $this->userContextMock->expects($this->once())
            ->method('getUserId')
            ->willReturn(7);
        $this->customerRepositoryMock->expects($this->once())
            ->method('getById')
            ->with(7)
            ->willReturn($customerMock);
        $customerMock->expects($this->once())->method('getDisableAutoGroupChange')->willReturn($stored);

        $this->assertSame($expected, $this->model->getOverriddenValue());
    }

    /**
     * @return array
     */
    public static function storedValueDataProvider(): array
    {
        return [
            'disabled' => [0, 0],
            'enabled' => [1, 1],
            'stored as string' => ['1', 1],
            'not set' => [null, 0],
        ];
    }

    /**
     * Other callers, such as an admin or integration token, get no override, like the sibling overriders.
     *
     * @return void
     */
    public function testGetOverriddenValueIsNotCustomer(): void
    {
        $this->userContextMock->expects($this->once())
            ->method('getUserType')
            ->willReturn(UserContextInterface::USER_TYPE_ADMIN);
        $this->customerRepositoryMock->expects($this->never())->method('getById');

        $this->assertNull($this->model->getOverriddenValue());
    }
}
