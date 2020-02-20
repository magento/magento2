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
     * @var AssignShippingMethodToCart
     */
    private $assignShippingMethodToCart;

    /**
     * @param AssignShippingMethodToCart $assignShippingMethodToCart
     */
    public function __construct(
        AssignShippingMethodToCart $assignShippingMethodToCart
    ) {
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

        if (!isset($shippingMethodInput['carrier_code']) || empty($shippingMethodInput['carrier_code'])) {
            throw new GraphQlInputException(__('Required parameter "carrier_code" is missing.'));
        }
        $carrierCode = $shippingMethodInput['carrier_code'];

        if (!isset($shippingMethodInput['method_code']) || empty($shippingMethodInput['method_code'])) {
            throw new GraphQlInputException(__('Required parameter "method_code" is missing.'));
        }
        $methodCode = $shippingMethodInput['method_code'];

        $shippingAddress = $cart->getShippingAddress();
        $this->assignShippingMethodToCart->execute($cart, $shippingAddress, $carrierCode, $methodCode);
    }
}
