<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Magento\QuoteGraphQl\Model\Cart\SetShippingAddressesOnCartInterface;

/**
 * Mutation resolver for setting shipping addresses for shopping cart
 */
class SetShippingAddressesOnCart implements ResolverInterface
{
    /**
     * @var GetCartForUser
     */
    private $getCartForUser;

    /**
     * @var SetShippingAddressesOnCartInterface
     */
    private $setShippingAddressesOnCart;

    /**
     * @param GetCartForUser $getCartForUser
     * @param SetShippingAddressesOnCartInterface $setShippingAddressesOnCart
     */
    public function __construct(
        GetCartForUser $getCartForUser,
        SetShippingAddressesOnCartInterface $setShippingAddressesOnCart
    ) {
        $this->getCartForUser = $getCartForUser;
        $this->setShippingAddressesOnCart = $setShippingAddressesOnCart;
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

        if (!isset($args['input']['shipping_addresses']) || empty($args['input']['shipping_addresses'])) {
            throw new GraphQlInputException(__('Required parameter "shipping_addresses" is missing'));
        }
        $shippingAddresses = $args['input']['shipping_addresses'];

        $cart = $this->getCartForUser->execute($maskedCartId, $context->getUserId());
        $this->setShippingAddressesOnCart->execute($context, $cart, $shippingAddresses);

        return [
            'cart' => [
                'model' => $cart,
            ],
        ];
    }
}
