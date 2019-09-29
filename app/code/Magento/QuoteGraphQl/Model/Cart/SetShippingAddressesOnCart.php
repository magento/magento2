<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart;

use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote\Address;

/**
 * Set single shipping address for a specified shopping cart
 */
class SetShippingAddressesOnCart implements SetShippingAddressesOnCartInterface
{
    /**
     * @var QuoteAddressFactory
     */
    private $quoteAddressFactory;

    /**
     * @var AssignShippingAddressToCart
     */
    private $assignShippingAddressToCart;

    /**
     * @param QuoteAddressFactory $quoteAddressFactory
     * @param AssignShippingAddressToCart $assignShippingAddressToCart
     */
    public function __construct(
        QuoteAddressFactory $quoteAddressFactory,
        AssignShippingAddressToCart $assignShippingAddressToCart
    ) {
        $this->quoteAddressFactory = $quoteAddressFactory;
        $this->assignShippingAddressToCart = $assignShippingAddressToCart;
    }

    /**
     * @inheritdoc
     */
    public function execute(ContextInterface $context, CartInterface $cart, array $shippingAddressesInput): void
    {
        if (count($shippingAddressesInput) > 1) {
            throw new GraphQlInputException(
                __('You cannot specify multiple shipping addresses.')
            );
        }
        $shippingAddressInput = current($shippingAddressesInput);
        $customerAddressId = $shippingAddressInput['customer_address_id'] ?? null;
        $addressInput = $shippingAddressInput['address'] ?? null;

        if ($addressInput) {
            $addressInput['customer_notes'] = $shippingAddressInput['customer_notes'] ?? '';
        }

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
            $shippingAddress = $this->quoteAddressFactory->createBasedOnInputData($addressInput);
        } else {
            if (false === $context->getExtensionAttributes()->getIsCustomer()) {
                throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
            }

            $shippingAddress = $this->quoteAddressFactory->createBasedOnCustomerAddress(
                (int)$customerAddressId,
                $context->getUserId()
            );
        }

        $this->validateAddress($shippingAddress);

        $this->assignShippingAddressToCart->execute($cart, $shippingAddress);
    }

    /**
     * Validate quote address.
     *
     * @param Address $shippingAddress
     *
     * @throws GraphQlInputException
     */
    private function validateAddress(Address $shippingAddress)
    {
        $errors = $shippingAddress->validate();

        if (true !== $errors) {
            throw new GraphQlInputException(
                __('Shipping address error: %message', ['message' => $this->getAddressErrors($errors)])
            );
        }
    }

    /**
     * Collecting errors.
     *
     * @param array $errors
     * @return string
     */
    private function getAddressErrors(array $errors): string
    {
        $errorMessages = [];

        /** @var \Magento\Framework\Phrase $error */
        foreach ($errors as $error) {
            $errorMessages[] = $error->render();
        }

        return implode(PHP_EOL, $errorMessages);
    }
}
