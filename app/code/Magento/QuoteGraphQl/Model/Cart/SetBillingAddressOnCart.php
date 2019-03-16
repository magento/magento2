<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart;

use Magento\Framework\GraphQl\Exception\GraphQlAuthenticationException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Quote\Api\Data\CartInterface;

/**
 * Set billing address for a specified shopping cart
 */
class SetBillingAddressOnCart
{
    /**
     * @var AssignBillingAddressToCart
     */
    private $assignBillingAddressToCart;

    /**
     * @var CreateQuoteAddressByCustomerAddress
     */
    private $createQuoteAddressByCustomerAddress;

    /**
     * @param AssignBillingAddressToCart $assignBillingAddressToCart
     * @param CreateQuoteAddressByCustomerAddress $createQuoteAddressByCustomerAddress
     */
    public function __construct(
        AssignBillingAddressToCart $assignBillingAddressToCart,
        CreateQuoteAddressByCustomerAddress $createQuoteAddressByCustomerAddress
    ) {
        $this->assignBillingAddressToCart = $assignBillingAddressToCart;
        $this->createQuoteAddressByCustomerAddress = $createQuoteAddressByCustomerAddress;
    }

    /**
     * Set billing address for a specified shopping cart
     *
     * @param ContextInterface $context
     * @param CartInterface $cart
     * @param array $billingAddressInput
     * @return void
     * @throws GraphQlInputException
     * @throws GraphQlNoSuchEntityException
     * @throws GraphQlAuthorizationException
     * @throws GraphQlAuthenticationException
     */
    public function execute(ContextInterface $context, CartInterface $cart, array $billingAddressInput): void
    {
        $customerAddressId = $billingAddressInput['customer_address_id'] ?? null;
        $addressInput = $billingAddressInput['address'] ?? null;
        $useForShipping = isset($billingAddressInput['use_for_shipping'])
            ? (bool)$billingAddressInput['use_for_shipping'] : false;

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

        $billingAddress = $this->createQuoteAddressByCustomerAddress->execute(
            $context,
            $customerAddressId,
            $addressInput
        );

        $this->assignBillingAddressToCart->execute($cart, $billingAddress, $useForShipping);
    }
}
