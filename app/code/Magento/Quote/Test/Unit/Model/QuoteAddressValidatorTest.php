<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface as CustomerAddress;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\QuoteAddressValidator;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for QuoteAddressValidator.
 */
class QuoteAddressValidatorTest extends TestCase
{
    /**
     * @var QuoteAddressValidator
     */
    private QuoteAddressValidator $model;

    /**
     * @var AddressRepositoryInterface
     */
    private AddressRepositoryInterface $addressRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    private CustomerRepositoryInterface $customerRepository;

    protected function setUp(): void
    {
        $this->addressRepository = $this->createStub(
            AddressRepositoryInterface::class
        );
        $this->customerRepository = $this->createStub(
            CustomerRepositoryInterface::class
        );
        $customerSession = $this->createStub(Session::class);

        $this->model = new QuoteAddressValidator(
            $this->addressRepository,
            $this->customerRepository,
            $customerSession
        );
    }

    /**
     * Verify that validateForCart rejects an address whose
     * customer_address_id belongs to a different customer.
     */
    public function testValidateForCartRejectsAddressOwnedByAnotherCustomer(): void
    {
        $cartOwnerId = 1;
        $otherCustomerAddressId = 42;

        $cart = $this->createStub(CartInterface::class);
        $cart->method('getCustomerIsGuest')->willReturn(false);
        $customer = $this->createStub(CustomerInterface::class);
        $customer->method('getId')->willReturn($cartOwnerId);
        $cart->method('getCustomer')->willReturn($customer);

        $address = $this->createStub(AddressInterface::class);
        $address->method('getCustomerAddressId')
            ->willReturn($otherCustomerAddressId);

        // The cart owner has one address, but not the one referenced.
        $ownerAddress = $this->createStub(CustomerAddress::class);
        $ownerAddress->method('getId')->willReturn(100);

        $ownerCustomer = $this->createStub(CustomerInterface::class);
        $ownerCustomer->method('getAddresses')
            ->willReturn([$ownerAddress]);

        $this->customerRepository->method('getById')
            ->willReturn($ownerCustomer);

        // The address ID itself exists (belongs to another customer).
        $this->addressRepository->method('getById')
            ->willReturn($this->createStub(CustomerAddress::class));

        $this->expectException(NoSuchEntityException::class);
        $this->expectExceptionMessage(
            'Invalid customer address id ' . $otherCustomerAddressId
        );

        $this->model->validateForCart($cart, $address);
    }

    /**
     * Verify that validateForCart accepts an address that
     * belongs to the cart owner.
     */
    public function testValidateForCartAcceptsOwnAddress(): void
    {
        $cartOwnerId = 1;
        $ownAddressId = 100;

        $cart = $this->createStub(CartInterface::class);
        $cart->method('getCustomerIsGuest')->willReturn(false);
        $customer = $this->createStub(CustomerInterface::class);
        $customer->method('getId')->willReturn($cartOwnerId);
        $cart->method('getCustomer')->willReturn($customer);

        $address = $this->createStub(AddressInterface::class);
        $address->method('getCustomerAddressId')
            ->willReturn($ownAddressId);

        $ownerAddress = $this->createStub(CustomerAddress::class);
        $ownerAddress->method('getId')->willReturn($ownAddressId);

        $ownerCustomer = $this->createStub(CustomerInterface::class);
        $ownerCustomer->method('getAddresses')
            ->willReturn([$ownerAddress]);

        $this->customerRepository->method('getById')
            ->willReturn($ownerCustomer);

        $this->addressRepository->method('getById')
            ->willReturn($this->createStub(CustomerAddress::class));

        // No exception expected.
        $this->model->validateForCart($cart, $address);
        $this->addToAssertionCount(1);
    }

    /**
     * Verify that validateForCart rejects a customer_address_id
     * when the cart belongs to a guest.
     */
    public function testValidateForCartRejectsAddressIdForGuest(): void
    {
        $addressId = 42;

        $cart = $this->createStub(CartInterface::class);
        $cart->method('getCustomerIsGuest')->willReturn(true);

        $address = $this->createStub(AddressInterface::class);
        $address->method('getCustomerAddressId')
            ->willReturn($addressId);

        $this->expectException(NoSuchEntityException::class);
        $this->expectExceptionMessage(
            'Invalid customer address id ' . $addressId
        );

        $this->model->validateForCart($cart, $address);
    }

    /**
     * Verify that validateForCart passes when no
     * customer_address_id is set.
     */
    public function testValidateForCartSkipsWhenNoCustomerAddressId(): void
    {
        $cart = $this->createStub(CartInterface::class);
        $cart->method('getCustomerIsGuest')->willReturn(true);

        $address = $this->createStub(AddressInterface::class);
        $address->method('getCustomerAddressId')->willReturn(null);

        // No exception expected — nothing to validate.
        $this->model->validateForCart($cart, $address);
        $this->addToAssertionCount(1);
    }
}
