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
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Magento\QuoteGraphQl\Model\Cart\SetShippingMethodsOnCartInterface;

/**
 * Mutation resolver for setting shipping methods for shopping cart
 */
class SetShippingMethodsOnCart implements ResolverInterface
{
    /**
     * @var GetCartForUser
     */
    private $getCartForUser;

    /**
     * @var SetShippingMethodsOnCartInterface
     */
    private $setShippingMethodsOnCart;

    /**
     * @param GetCartForUser $getCartForUser
     * @param SetShippingMethodsOnCartInterface $setShippingMethodsOnCart
     */
    public function __construct(
        GetCartForUser $getCartForUser,
        SetShippingMethodsOnCartInterface $setShippingMethodsOnCart
    ) {
        $this->getCartForUser = $getCartForUser;
        $this->setShippingMethodsOnCart = $setShippingMethodsOnCart;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($args['input']['cart_id']) || empty($args['input']['cart_id'])) {
            throw new GraphQlInputException(__('Required parameter "cart_id" is missing'));
        }
        $maskedCartId = $args['input']['cart_id'];

        if (!isset($args['input']['shipping_methods']) || empty($args['input']['shipping_methods'])) {
            throw new GraphQlInputException(__('Required parameter "shipping_methods" is missing'));
        }
        $shippingMethods = $args['input']['shipping_methods'];

        $cart = $this->getCartForUser->execute($maskedCartId, $context->getUserId());
        $this->setShippingMethodsOnCart->execute($context, $cart, $shippingMethods);

        return [
            'cart' => [
                'model' => $cart,
            ],
        ];
    }
}
