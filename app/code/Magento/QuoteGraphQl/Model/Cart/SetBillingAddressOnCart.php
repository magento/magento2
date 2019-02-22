<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart;

use Magento\CustomerGraphQl\Model\Customer\CheckCustomerAccount;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Api\BillingAddressManagementInterface;
use Magento\Customer\Api\AddressRepositoryInterface;

/**
 * Set billing address for a specified shopping cart
 */
class SetBillingAddressOnCart
{
    /**
     * @var BillingAddressManagementInterface
     */
    private $billingAddressManagement;

    /**
     * @var Address
     */
    private $addressModel;

    /**
     * @var CheckCustomerAccount
     */
    private $checkCustomerAccount;

    /**
     * @var GetCustomerAddress
     */
    private $getCustomerAddress;

    /**
     * @param BillingAddressManagementInterface $billingAddressManagement
     * @param AddressRepositoryInterface $addressRepository
     * @param Address $addressModel
     * @param CheckCustomerAccount $checkCustomerAccount
     * @param GetCustomerAddress $getCustomerAddress
     */
    public function __construct(
        BillingAddressManagementInterface $billingAddressManagement,
        AddressRepositoryInterface $addressRepository,
        Address $addressModel,
        CheckCustomerAccount $checkCustomerAccount,
        GetCustomerAddress $getCustomerAddress
    ) {
        $this->billingAddressManagement = $billingAddressManagement;
        $this->addressRepository = $addressRepository;
        $this->addressModel = $addressModel;
        $this->checkCustomerAccount = $checkCustomerAccount;
        $this->getCustomerAddress = $getCustomerAddress;
    }

    /**
     * Set billing address for a specified shopping cart
     *
     * @param ContextInterface $context
     * @param CartInterface $cart
     * @param array $billingAddress
     * @return void
     * @throws GraphQlInputException
     */
    public function execute(ContextInterface $context, CartInterface $cart, array $billingAddress): void
    {
        $customerAddressId = $billingAddress['customer_address_id'] ?? null;
        $addressInput = $billingAddress['address'] ?? null;
        $useForShipping = $billingAddress['use_for_shipping'] ?? false;

        if (null === $customerAddressId && null === $addressInput) {
            throw new GraphQlInputException(
                __('The billing address must contain either "customer_address_id" or "address".')
            );
        }
        if ($customerAddressId && $addressInput) {
            throw new GraphQlInputException(
                __('The billing address cannot contain "customer_address_id" and "address" at the same time.')
            );
        }
        $addresses = $cart->getAllShippingAddresses();
        if ($useForShipping && count($addresses) > 1) {
            throw new GraphQlInputException(
                __('Using the "use_for_shipping" option with multishipping is not possible.')
            );
        }
        if (null === $customerAddressId) {
            $billingAddress = $this->addressModel->addData($addressInput);
        } else {
            $this->checkCustomerAccount->execute($context->getUserId(), $context->getUserType());
            $customerAddress = $this->getCustomerAddress->execute((int)$customerAddressId, (int)$context->getUserId());
            $billingAddress = $this->addressModel->importCustomerAddressData($customerAddress);
        }

        $this->billingAddressManagement->assign($cart->getId(), $billingAddress, $useForShipping);
    }
}
