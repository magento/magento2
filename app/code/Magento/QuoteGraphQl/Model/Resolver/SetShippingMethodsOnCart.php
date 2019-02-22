<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Magento\QuoteGraphQl\Model\Cart\SetShippingMethodOnCart;

/**
 * Class SetShippingMethodsOnCart
 *
 * Mutation resolver for setting shipping methods for shopping cart
 */
class SetShippingMethodsOnCart implements ResolverInterface
{
    /**
     * @var SetShippingMethodOnCart
     */
    private $setShippingMethodOnCart;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var GetCartForUser
     */
    private $getCartForUser;

    /**
     * @param ArrayManager $arrayManager
     * @param GetCartForUser $getCartForUser
     * @param SetShippingMethodOnCart $setShippingMethodOnCart
     */
    public function __construct(
        ArrayManager $arrayManager,
        GetCartForUser $getCartForUser,
        SetShippingMethodOnCart $setShippingMethodOnCart
    ) {
        $this->arrayManager = $arrayManager;
        $this->getCartForUser = $getCartForUser;
        $this->setShippingMethodOnCart = $setShippingMethodOnCart;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $shippingMethods = $this->arrayManager->get('input/shipping_methods', $args);
        $maskedCartId = $this->arrayManager->get('input/cart_id', $args);

        if (!$maskedCartId) {
            throw new GraphQlInputException(__('Required parameter "cart_id" is missing'));
        }
        if (!$shippingMethods) {
            throw new GraphQlInputException(__('Required parameter "shipping_methods" is missing'));
        }

        $shippingMethod = reset($shippingMethods); // This point can be extended for multishipping

        if (!$shippingMethod['cart_address_id']) {
            throw new GraphQlInputException(__('Required parameter "cart_address_id" is missing'));
        }
        if (!$shippingMethod['shipping_carrier_code']) {
            throw new GraphQlInputException(__('Required parameter "shipping_carrier_code" is missing'));
        }
        if (!$shippingMethod['shipping_method_code']) {
            throw new GraphQlInputException(__('Required parameter "shipping_method_code" is missing'));
        }

        $userId = $context->getUserId();
        $cart = $this->getCartForUser->execute((string) $maskedCartId, $userId);

        $this->setShippingMethodOnCart->execute(
            $cart,
            $shippingMethod['cart_address_id'],
            $shippingMethod['shipping_carrier_code'],
            $shippingMethod['shipping_method_code']
        );

        return [
            'cart' => [
                'cart_id' => $maskedCartId,
                'model' => $cart
            ]
        ];
    }
}
