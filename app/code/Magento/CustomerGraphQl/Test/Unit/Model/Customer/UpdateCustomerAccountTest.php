<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Test\Unit\Model\Customer;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\CustomerGraphQl\Model\Customer\CheckCustomerPassword;
use Magento\CustomerGraphQl\Model\Customer\SaveCustomer;
use Magento\CustomerGraphQl\Model\Customer\UpdateCustomerAccount;
use Magento\CustomerGraphQl\Model\Customer\ValidateCustomerData;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Newsletter\Model\SubscriptionManagerInterface;
use Magento\Store\Api\Data\StoreInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for the customer account update flow behind updateCustomerV2.
 */
class UpdateCustomerAccountTest extends TestCase
{
    /**
     * @var SaveCustomer|MockObject
     */
    private $saveCustomer;

    /**
     * @var CheckCustomerPassword|MockObject
     */
    private $checkCustomerPassword;

    /**
     * @var DataObjectHelper|MockObject
     */
    private $dataObjectHelper;

    /**
     * @var ValidateCustomerData|MockObject
     */
    private $validateCustomerData;

    /**
     * @var SubscriptionManagerInterface|MockObject
     */
    private $subscriptionManager;

    /**
     * @var UpdateCustomerAccount
     */
    private $model;

    protected function setUp(): void
    {
        $this->saveCustomer = $this->createMock(SaveCustomer::class);
        $this->checkCustomerPassword = $this->createMock(CheckCustomerPassword::class);
        $this->dataObjectHelper = $this->createMock(DataObjectHelper::class);
        $this->validateCustomerData = $this->createMock(ValidateCustomerData::class);
        $this->subscriptionManager = $this->createMock(SubscriptionManagerInterface::class);

        $this->model = new UpdateCustomerAccount(
            $this->saveCustomer,
            $this->checkCustomerPassword,
            $this->dataObjectHelper,
            $this->validateCustomerData,
            $this->subscriptionManager
        );
    }

    /**
     * The addresses must be detached (set to null) while the customer is saved and restored afterwards, so the
     * repository never re-saves the address book during an account-only update.
     */
    public function testExecuteDetachesAndRestoresAddressesAroundSave(): void
    {
        $originalAddresses = [$this->createMock(AddressInterface::class)];
        $addressesState = $originalAddresses;

        $customer = $this->createMock(CustomerInterface::class);
        $customer->method('getId')->willReturn(1);
        $customer->method('getEmail')->willReturn('customer@example.com');
        $customer->method('getAddresses')->willReturnCallback(
            static function () use (&$addressesState) {
                return $addressesState;
            }
        );
        $customer->method('setAddresses')->willReturnCallback(
            static function ($value) use (&$addressesState, $customer) {
                $addressesState = $value;
                return $customer;
            }
        );

        $store = $this->createMock(StoreInterface::class);
        $store->method('getId')->willReturn(1);

        // Capture the addresses value at the exact moment the customer is handed to the repository.
        $addressesDuringSave = 'not-called';
        $this->saveCustomer->expects($this->once())
            ->method('execute')
            ->with($customer)
            ->willReturnCallback(
                static function () use (&$addressesState, &$addressesDuringSave) {
                    $addressesDuringSave = $addressesState;
                }
            );

        $this->model->execute($customer, ['lastname' => 'Rowe'], $store);

        $this->assertNull($addressesDuringSave, 'Addresses must be detached (null) while the customer is saved.');
        $this->assertSame($originalAddresses, $addressesState, 'Original addresses must be restored after save.');
    }
}
