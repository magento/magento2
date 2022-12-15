<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Webapi;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Customer\Model\Webapi\ParamOverriderCustomerGroupId;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for Magento\Webapi\Controller\Rest\ParamOverriderCustomerGroupId class.
 */
class ParamOverriderCustomerGroupIdTest extends TestCase
{
    /**
     * @var ParamOverriderCustomerGroupId
     */
    private $model;

    /**
     * @var UserContextInterface|MockObject
     */
    private $userContextMock;

    /**
     * @var CustomerRepositoryInterface|MockObject
     */
    private $customerRepositoryMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->userContextMock = $this->createMock(UserContextInterface::class);
        $this->customerRepositoryMock = $this->createMock(CustomerRepositoryInterface::class);
        $this->model = (new ObjectManager($this))->getObject(
            ParamOverriderCustomerGroupId::class,
            [
                'userContext' => $this->userContextMock,
                'customerRepository' => $this->customerRepositoryMock,
            ]
        );
    }

    /**
     * @return void
     */
    public function testGetOverriddenValueIsCustomer(): void
    {
        $userId = 1;
        $groupId = 1;
        $customerMock = $this->createMock(CustomerInterface::class);

        $this->userContextMock->expects($this->once())
            ->method('getUserType')
            ->willReturn(UserContextInterface::USER_TYPE_CUSTOMER);
        $this->userContextMock->expects($this->once())
            ->method('getUserId')
            ->willReturn($userId);
        $this->customerRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($userId)
            ->willReturn($customerMock);
        $customerMock->expects($this->once())->method('getGroupId')->willReturn($groupId);

        $this->assertSame($groupId, $this->model->getOverriddenValue());
    }

    /**
     * @return void
     */
    public function testGetOverriddenValueIsNotCustomer(): void
    {
        $this->userContextMock->expects($this->once())
            ->method('getUserType')
            ->willReturn(UserContextInterface::USER_TYPE_ADMIN);

        $this->assertNull($this->model->getOverriddenValue());
    }
}
