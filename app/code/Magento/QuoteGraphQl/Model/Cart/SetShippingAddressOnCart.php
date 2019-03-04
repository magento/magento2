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

/**
 * Set single shipping address for a specified shopping cart
 */
class SetShippingAddressOnCart implements SetShippingAddressesOnCartInterface
{
    /**
     * @var QuoteAddressFactory
     */
    private $quoteAddressFactory;

    /**
     * @var CheckCustomerAccount
     */
    private $checkCustomerAccount;

    /**
     * @var GetCustomerAddress
     */
    private $getCustomerAddress;

    /**
     * @var AssignShippingAddressToCart
     */
    private $assignShippingAddressToCart;

    /**
     * @param QuoteAddressFactory $quoteAddressFactory
     * @param CheckCustomerAccount $checkCustomerAccount
     * @param GetCustomerAddress $getCustomerAddress
     * @param AssignShippingAddressToCart $assignShippingAddressToCart
     */
    public function __construct(
        QuoteAddressFactory $quoteAddressFactory,
        CheckCustomerAccount $checkCustomerAccount,
        GetCustomerAddress $getCustomerAddress,
        AssignShippingAddressToCart $assignShippingAddressToCart
    ) {
        $this->quoteAddressFactory = $quoteAddressFactory;
        $this->checkCustomerAccount = $checkCustomerAccount;
        $this->getCustomerAddress = $getCustomerAddress;
        $this->assignShippingAddressToCart = $assignShippingAddressToCart;
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
            $shippingAddress = $this->quoteAddressFactory->createBasedOnInputData($addressInput);
        } else {
            $this->checkCustomerAccount->execute($context->getUserId(), $context->getUserType());
            $customerAddress = $this->getCustomerAddress->execute((int)$customerAddressId, (int)$context->getUserId());
            $shippingAddress = $this->quoteAddressFactory->createBasedOnCustomerAddress($customerAddress);
        }

        $this->assignShippingAddressToCart->execute($cart, $shippingAddress);
    }
}
