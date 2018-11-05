<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\ShippingAddressManagementInterface;
use Magento\Customer\Api\AddressRepositoryInterface;

/**
 * Class SetShippingAddressOnCart
 *
 * Set shipping address for a specified shopping cart
 */
class SetShippingAddressOnCart
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
     * @param ShippingAddressManagementInterface $shippingAddressManagement
     * @param AddressRepositoryInterface $addressRepository
     * @param Address $addressModel
     */
    public function __construct(
        ShippingAddressManagementInterface $shippingAddressManagement,
        AddressRepositoryInterface $addressRepository,
        Address $addressModel
    ) {
        $this->shippingAddressManagement = $shippingAddressManagement;
        $this->addressRepository = $addressRepository;
        $this->addressModel = $addressModel;
    }

    /**
     * @param ContextInterface $context
     * @param int $cartId
     * @param array $shippingAddresses
     * @return void
     */
    public function setAddresses(ContextInterface $context, int $cartId, array $shippingAddresses): void
    {
        if (count($shippingAddresses) > 1) {
            throw new GraphQlInputException(
                __('Multiple address does not allowed here!')
            );
        }
        $shippingAddress = current($shippingAddresses);
        $customerAddressId = $shippingAddress['customer_address_id'] ?? null;
        $addressInput = $shippingAddress['address'] ?? null;

        if (!$customerAddressId && !$addressInput) {
            throw new GraphQlInputException(
                __('Shipping address should contain either "customer_address_id" or "address" input.')
            );
        }
        if ($customerAddressId && $addressInput) {
            throw new GraphQlInputException(
                __('Shipping address can\'t contain "customer_address_id" and "address" input at the same time.')
            );
        }
        if ($customerAddressId) {
            if ((!$context->getUserId()) || $context->getUserType() == UserContextInterface::USER_TYPE_GUEST) {
                throw new GraphQlAuthorizationException(
                    __(
                        'Address management allowed only for authorized customers.'
                    )
                );
            }
            /** @var AddressInterface $customerAddress */
            $customerAddress = $this->addressRepository->getById($customerAddressId);
            $shippingAddress = $this->addressModel->importCustomerAddressData($customerAddress);
        } else {
            $shippingAddress = $this->addressModel->addData($addressInput);
        }

        $this->shippingAddressManagement->assign($cartId, $shippingAddress);
    }
}
