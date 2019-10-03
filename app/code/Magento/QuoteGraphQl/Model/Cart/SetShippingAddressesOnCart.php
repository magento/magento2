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
     * @var AssignShippingAddressToCart
     */
    private $assignShippingAddressToCart;
    /**
     * @var GetShippingAddress
     */
    private $getShippingAddress;

    /**
     * @param AssignShippingAddressToCart $assignShippingAddressToCart
     * @param GetShippingAddress $getShippingAddress
     */
    public function __construct(
        AssignShippingAddressToCart $assignShippingAddressToCart,
        GetShippingAddress $getShippingAddress
    ) {
        $this->assignShippingAddressToCart = $assignShippingAddressToCart;
        $this->getShippingAddress = $getShippingAddress;
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

        $shippingAddress = $this->getShippingAddress->execute($context, $shippingAddressInput);

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
