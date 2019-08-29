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
use Magento\QuoteGraphQl\Model\Cart\Address\SaveQuoteAddressToCustomerAddressBook;

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
     * @var SaveQuoteAddressToCustomerAddressBook
     */
    private $saveQuoteAddressToCustomerAddressBook;

    /**
     * @param QuoteAddressFactory $quoteAddressFactory
     * @param AssignShippingAddressToCart $assignShippingAddressToCart
     * @param SaveQuoteAddressToCustomerAddressBook $saveQuoteAddressToCustomerAddressBook
     */
    public function __construct(
        QuoteAddressFactory $quoteAddressFactory,
        AssignShippingAddressToCart $assignShippingAddressToCart,
        SaveQuoteAddressToCustomerAddressBook $saveQuoteAddressToCustomerAddressBook
    ) {
        $this->quoteAddressFactory = $quoteAddressFactory;
        $this->assignShippingAddressToCart = $assignShippingAddressToCart;
        $this->saveQuoteAddressToCustomerAddressBook = $saveQuoteAddressToCustomerAddressBook;
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

        $customerId = $context->getUserId();

        if (null === $customerAddressId) {
            $shippingAddress = $this->quoteAddressFactory->createBasedOnInputData($addressInput);

            // need to save address only for registered user and if save_in_address_book = true
            if (0 !== $customerId && !empty($addressInput['save_in_address_book'])) {
                $this->saveQuoteAddressToCustomerAddressBook->execute($shippingAddress, $customerId);
            }
        } else {
            if (false === $context->getExtensionAttributes()->getIsCustomer()) {
                throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
            }

            $shippingAddress = $this->quoteAddressFactory->createBasedOnCustomerAddress(
                (int)$customerAddressId,
                $customerId
            );
        }

        $this->assignShippingAddressToCart->execute($cart, $shippingAddress);
    }
}
