<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\CustomerGraphQl\Model\Customer\CheckCustomerAccount;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\ShippingAddressManagementInterface;
use Magento\Customer\Api\AddressRepositoryInterface;

/**
 * Set single shipping address for a specified shopping cart
 */
class SetShippingAddressOnCart implements SetShippingAddressesOnCartInterface
{
    /**
     * @var ShippingAddressManagementInterface
     */
    private $shippingAddressManagement;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var Address
     */
    private $addressModel;

    /**
     * @var CheckCustomerAccount
     */
    private $checkCustomerAccount;

    /**
     * @param ShippingAddressManagementInterface $shippingAddressManagement
     * @param AddressRepositoryInterface $addressRepository
     * @param Address $addressModel
     * @param CheckCustomerAccount $checkCustomerAccount
     */
    public function __construct(
        ShippingAddressManagementInterface $shippingAddressManagement,
        AddressRepositoryInterface $addressRepository,
        Address $addressModel,
        CheckCustomerAccount $checkCustomerAccount
    ) {
        $this->shippingAddressManagement = $shippingAddressManagement;
        $this->addressRepository = $addressRepository;
        $this->addressModel = $addressModel;
        $this->checkCustomerAccount = $checkCustomerAccount;
    }

    /**
     * @inheritdoc
     */
    public function execute(ContextInterface $context, CartInterface $cart, array $shippingAddresses): void
    {
        if (count($shippingAddresses) > 1) {
            throw new GraphQlInputException(
                __('You cannot specify multiple shipping addresses.')
            );
        }
        $shippingAddress = current($shippingAddresses);
        $customerAddressId = $shippingAddress['customer_address_id'] ?? null;
        $addressInput = $shippingAddress['address'] ?? null;

        if (null === $customerAddressId && null === $addressInput) {
            throw new GraphQlInputException(
                __('The shipping address must contain either "customer_address_id" or "address".')
            );
        }
        if ($customerAddressId && $addressInput) {
            throw new GraphQlInputException(
                __('The shipping address cannot contain "customer_address_id" and "address" at the same time.')
            );
        }
        if (null === $customerAddressId) {
            $shippingAddress = $this->addressModel->addData($addressInput);
        } else {
            $this->checkCustomerAccount->execute($context->getUserId(), $context->getUserType());

            /** @var AddressInterface $customerAddress */
            $customerAddress = $this->addressRepository->getById($customerAddressId);
            $shippingAddress = $this->addressModel->importCustomerAddressData($customerAddress);
        }

        $this->shippingAddressManagement->assign($cart->getId(), $shippingAddress);
    }
}
