<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Quote\Api\Data\CartInterface;

/**
 * Set single shipping method for a specified shopping cart
 */
class SetShippingMethodsOnCart implements SetShippingMethodsOnCartInterface
{
    /**
     * @var GetQuoteAddress
     */
    private $getQuoteAddress;

    /**
     * @var AssignShippingMethodToCart
     */
    private $assignShippingMethodToCart;

    /**
     * @param GetQuoteAddress $getQuoteAddress
     * @param AssignShippingMethodToCart $assignShippingMethodToCart
     */
    public function __construct(
        GetQuoteAddress $getQuoteAddress,
        AssignShippingMethodToCart $assignShippingMethodToCart
    ) {
        $this->getQuoteAddress = $getQuoteAddress;
        $this->assignShippingMethodToCart = $assignShippingMethodToCart;
    }

    /**
     * @inheritdoc
     */
    public function execute(ContextInterface $context, CartInterface $cart, array $shippingMethodsInput): void
    {
        if (count($shippingMethodsInput) > 1) {
            throw new GraphQlInputException(
                __('You cannot specify multiple shipping methods.')
            );
        }
        $shippingMethodInput = current($shippingMethodsInput);

        if (!isset($shippingMethodInput['cart_address_id']) || empty($shippingMethodInput['cart_address_id'])) {
            throw new GraphQlInputException(__('Required parameter "cart_address_id" is missing.'));
        }
        $cartAddressId = $shippingMethodInput['cart_address_id'];

        if (!isset($shippingMethodInput['carrier_code']) || empty($shippingMethodInput['carrier_code'])) {
            throw new GraphQlInputException(__('Required parameter "carrier_code" is missing.'));
        }
        $carrierCode = $shippingMethodInput['carrier_code'];

        if (!isset($shippingMethodInput['method_code']) || empty($shippingMethodInput['method_code'])) {
            throw new GraphQlInputException(__('Required parameter "method_code" is missing.'));
        }
        $methodCode = $shippingMethodInput['method_code'];

        $quoteAddress = $this->getQuoteAddress->execute($cart, $cartAddressId, $context->getUserId());
        $this->assignShippingMethodToCart->execute($cart, $quoteAddress, $carrierCode, $methodCode);
    }
}
