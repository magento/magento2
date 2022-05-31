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
     */
    public function execute(ContextInterface $context, CartInterface $cart, array $billingAddressInput): void
    {
        $this->checkForInputExceptions($billingAddressInput);

        $customerAddressId = $billingAddressInput['customer_address_id'] ?? null;
        $addressInput = $billingAddressInput['address'] ?? null;
        $useForShipping = $billingAddressInput['use_for_shipping'] ?? false;
        $sameAsShipping = $billingAddressInput['same_as_shipping'] ?? false;

        if (!$customerAddressId && $addressInput && !isset($addressInput['save_in_address_book'])) {
            $addressInput['save_in_address_book'] = true;
        }

        if ($sameAsShipping) {
            $this->validateCanUseShippingForBilling($cart);
            $billingAddress = $this->quoteAddressFactory->createBasedOnShippingAddress($cart);
            $useForShipping = false;
        } elseif ($customerAddressId) {
            $this->validateCanUseCustomerAddress($context);
            $billingAddress = $this->quoteAddressFactory->createBasedOnCustomerAddress(
                (int)$customerAddressId,
                (int)$context->getUserId()
            );
        } else {
            $billingAddress = $this->quoteAddressFactory->createBasedOnInputData($addressInput);
        }

        if ($useForShipping) {
            $this->validateCanUseBillingForShipping($cart);
        }

        $this->validateBillingAddress($billingAddress);
        $this->assignBillingAddressToCart->execute($cart, $billingAddress, $useForShipping);
    }

    /**
     * Check for the input exceptions
     *
     * @param array|null $billingAddressInput
     * @throws GraphQlInputException
     */
    private function checkForInputExceptions(
        ?array $billingAddressInput
    ) {
        $customerAddressId = $billingAddressInput['customer_address_id'] ?? null;
        $addressInput = $billingAddressInput['address'] ?? null;
        $sameAsShipping = $billingAddressInput['same_as_shipping'] ?? null;

        if (null === $customerAddressId && null === $addressInput && empty($sameAsShipping)) {
            throw new GraphQlInputException(
                __('The billing address must contain either "customer_address_id", "address", or "same_as_shipping".')
            );
        }

        if ($customerAddressId && $addressInput) {
            throw new GraphQlInputException(
                __('The billing address cannot contain "customer_address_id" and "address" at the same time.')
            );
        }
    }

    /**
     * Validate that the quote is capable of using the shipping address as the billing address.
     *
     * @param CartInterface $quote
     * @throws GraphQlInputException
     */
    private function validateCanUseShippingForBilling(CartInterface $quote)
    {
        $shippingAddresses = $quote->getAllShippingAddresses();

        if (count($shippingAddresses) > 1) {
            throw new GraphQlInputException(
                __('Could not use the "same_as_shipping" option, because multiple shipping addresses have been set.')
            );
        }

        if (empty($shippingAddresses) || $shippingAddresses[0]->validate() !== true) {
            throw new GraphQlInputException(
                __('Could not use the "same_as_shipping" option, because the shipping address has not been set.')
            );
        }
    }

    /**
     * Validate that the quote is capable of using the billing address as the shipping address.
     *
     * @param CartInterface $quote
     * @throws GraphQlInputException
     */
    private function validateCanUseBillingForShipping(CartInterface $quote)
    {
        $shippingAddresses = $quote->getAllShippingAddresses();

        if (count($shippingAddresses) > 1) {
            throw new GraphQlInputException(
                __('Could not use the "use_for_shipping" option, because multiple shipping addresses have already been set.')
            );
        }
    }

    /**
     * Validate that the currently logged-in customer is authorized to use a customer address id as the billing address.
     *
     * @param ContextInterface $context
     * @throws GraphQlAuthorizationException
     */
    private function validateCanUseCustomerAddress(ContextInterface $context)
    {
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }
    }

    /**
     * Validate the billing address to be set on the cart.
     *
     * @param Address $billingAddress
     * @return Address
     * @throws GraphQlInputException
     */
    private function validateBillingAddress(Address $billingAddress)
    {
        $errors = $billingAddress->validate();

        if (true !== $errors) {
            $e = new GraphQlInputException(__('An error occurred while processing the billing address.'));

            foreach ($errors as $error) {
                $e->addError(new GraphQlInputException($error));
            }

            throw $e;
        }

        return $billingAddress;
    }
}
