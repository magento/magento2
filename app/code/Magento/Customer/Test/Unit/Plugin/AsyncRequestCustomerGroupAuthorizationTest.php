<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Plugin;

use Magento\AsynchronousOperations\Model\MassSchedule;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Authorization;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Customer\Plugin\AsyncRequestCustomerGroupAuthorization;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for validating anonymous request for asynchronous operations containing group id.
 */
class AsyncRequestCustomerGroupAuthorizationTest extends TestCase
{
    /**
     * @var Authorization|MockObject
     */
    private $authorizationMock;

    /**
     * @var AsyncRequestCustomerGroupAuthorization
     */
    private $plugin;

    /**
     * @var MassSchedule|MockObject
     */
    private $massScheduleMock;

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
        $this->plugin = $objectManager->getObject(AsyncRequestCustomerGroupAuthorization::class, [
            'authorization' => $this->authorizationMock
        ]);
        $this->massScheduleMock = $this->createMock(MassSchedule::class);
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
    public function testBeforePublishMass(
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
        $customer = $this->getMockForAbstractClass(CustomerInterface::class);
        $customer->method('getGroupId')->willReturn($groupId);
        $customer->method('getId')->willReturn($customerId);
        $this->customerRepository->method('getById')->with($customerId)->willReturn($customer);
        $entitiesArray = [
            [$customer, 'Password1', '']
        ];
        $this->authorizationMock
            ->method('isAllowed')
            ->with('Magento_Customer::manage')
            ->willReturn($isAllowed);
        $this->plugin->beforePublishMass(
            $this->massScheduleMock,
            'async.magento.customer.api.accountmanagementinterface.createaccount.post',
            $entitiesArray,
            '',
            ''
        );
    }

    /**
     * @return array
     */
    public static function customerDataProvider(): array
    {
        return [
            [3, 1, false, 1],
            [3, 1, true, 0]
        ];
    }
}
