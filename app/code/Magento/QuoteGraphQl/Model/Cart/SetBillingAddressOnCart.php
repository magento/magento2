<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart;

use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote\Address;

/**
 * Set billing address for a specified shopping cart
 */
class SetBillingAddressOnCart
{
    /**
     * @var QuoteAddressFactory
     */
    private $quoteAddressFactory;

    /**
     * @var AssignBillingAddressToCart
     */
    private $assignBillingAddressToCart;

    /**
     * @param QuoteAddressFactory $quoteAddressFactory
     * @param AssignBillingAddressToCart $assignBillingAddressToCart
     */
    public function __construct(
        QuoteAddressFactory $quoteAddressFactory,
        AssignBillingAddressToCart $assignBillingAddressToCart
    ) {
        $this->quoteAddressFactory = $quoteAddressFactory;
        $this->assignBillingAddressToCart = $assignBillingAddressToCart;
    }

    /**
     * Set billing address for a specified shopping cart
     *
     * @param ContextInterface $context
     * @param CartInterface $cart
     * @param array $billingAddressInput
     * @return void
     * @throws GraphQlAuthorizationException
     * @throws GraphQlInputException
     * @throws GraphQlNoSuchEntityException
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

        $billingAddress = $this->createBillingAddress($context, $customerAddressId, $addressInput);

        $this->assignBillingAddressToCart->execute($cart, $billingAddress, $useForShipping);
    }

    /**
     * Create billing address
     *
     * @param ContextInterface $context
     * @param int|null $customerAddressId
     * @param array $addressInput
     * @return Address
     * @throws GraphQlAuthorizationException
     * @throws GraphQlInputException
     * @throws GraphQlNoSuchEntityException
     */
    private function createBillingAddress(
        ContextInterface $context,
        ?int $customerAddressId,
        ?array $addressInput
    ): Address {
        if (null === $customerAddressId) {
            $billingAddress = $this->quoteAddressFactory->createBasedOnInputData($addressInput);
        } else {
            if (false === $context->getExtensionAttributes()->getIsCustomer()) {
                throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
            }

            $billingAddress = $this->quoteAddressFactory->createBasedOnCustomerAddress(
                (int)$customerAddressId,
                (int)$context->getUserId()
            );
        }
        return $billingAddress;
    }
}
