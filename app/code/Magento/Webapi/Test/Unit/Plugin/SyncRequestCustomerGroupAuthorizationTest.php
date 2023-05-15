<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Webapi\Test\Unit\Plugin;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\AccountManagementApi;
use Magento\Framework\Authorization;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Webapi\Plugin\SyncRequestCustomerGroupAuthorization;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for validating anonymous request for synchronous operations containing group id.
 */
class SyncRequestCustomerGroupAuthorizationTest extends TestCase
{
    /**
     * @var Authorization|MockObject
     */
    private $authorizationMock;

    /**
     * @var SyncRequestCustomerGroupAuthorization
     */
    private $plugin;

    /**
     * @var AccountManagementApi
     */
    private $accountManagementApiMock;

    /**
     * @var CustomerRepositoryInterface|MockObject
     */
    private $customerRepository;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->authorizationMock = $this->createMock(Authorization::class);
        $this->plugin = $objectManager->getObject(SyncRequestCustomerGroupAuthorization::class, [
            'authorization' => $this->authorizationMock
        ]);
        $this->accountManagementApiMock = $this->createMock(AccountManagementApi::class);
        $this->customerRepository = $this->getMockForAbstractClass(CustomerRepositoryInterface::class);
    }

    /**
     * Verify that only authorized request will be able to change groupId
     *
     * @param int $groupId
     * @param int $customerId
     * @param bool $isAllowed
     * @param int $willThrowException
     * @return void
     * @throws AuthorizationException
     * @dataProvider customerDataProvider
     */
    public function testBeforeCreateAccount(
        int $groupId,
        int $customerId,
        bool $isAllowed,
        int $willThrowException
    ): void {
        if ($willThrowException) {
            $this->expectException(AuthorizationException::class);
        } else {
            $this->expectNotToPerformAssertions();
        }
        $this->authorizationMock
            ->expects($this->once())
            ->method('isAllowed')
            ->with('Magento_Customer::manage')
            ->willReturn($isAllowed);
        $customer = $this->getMockForAbstractClass(CustomerInterface::class);
        $customer->method('getGroupId')->willReturn($groupId);
        $customer->method('getId')->willReturn($customerId);
        $this->customerRepository->method('getById')->with($customerId)->willReturn($customer);
        $this->plugin->beforeCreateAccount($this->accountManagementApiMock, $customer, '', '');
    }

    /**
     * @return array
     */
    public function customerDataProvider(): array
    {
        return [
            [3, 1, false, 1],
            [3, 1, true, 0]
        ];
    }
}
