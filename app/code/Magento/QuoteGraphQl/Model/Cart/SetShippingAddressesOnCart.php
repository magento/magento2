<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart;

use Magento\CustomerGraphQl\Model\Customer\GetCustomer;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Quote\Api\Data\CartInterface;

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
     * @var GetCustomer
     */
    private $getCustomer;

    /**
     * @var AssignShippingAddressToCart
     */
    private $assignShippingAddressToCart;

    /**
     * @param QuoteAddressFactory $quoteAddressFactory
     * @param GetCustomer $getCustomer
     * @param AssignShippingAddressToCart $assignShippingAddressToCart
     */
    public function __construct(
        QuoteAddressFactory $quoteAddressFactory,
        GetCustomer $getCustomer,
        AssignShippingAddressToCart $assignShippingAddressToCart
    ) {
        $this->quoteAddressFactory = $quoteAddressFactory;
        $this->getCustomer = $getCustomer;
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
            $customer = $this->getCustomer->execute($context);
            $shippingAddress = $this->quoteAddressFactory->createBasedOnCustomerAddress(
                (int)$customerAddressId,
                (int)$customer->getId()
            );
        }

        $this->assignShippingAddressToCart->execute($cart, $shippingAddress);
    }
}
