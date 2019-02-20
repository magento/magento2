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
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Quote\Model\ShippingAddressManagementInterface;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Magento\QuoteGraphQl\Model\Cart\SetShippingAddressesOnCartInterface;

/**
 * Class SetShippingAddressesOnCart
 *
 * Mutation resolver for setting shipping addresses for shopping cart
 */
class SetShippingAddressesOnCart implements ResolverInterface
{
    /**
     * @var ShippingAddressManagementInterface
     */
    private $shippingAddressManagement;

    /**
     * @var GetCartForUser
     */
    private $getCartForUser;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var SetShippingAddressesOnCartInterface
     */
    private $setShippingAddressesOnCart;

    /**
     * @param ShippingAddressManagementInterface $shippingAddressManagement
     * @param GetCartForUser $getCartForUser
     * @param ArrayManager $arrayManager
     * @param SetShippingAddressesOnCartInterface $setShippingAddressesOnCart
     */
    public function __construct(
        ShippingAddressManagementInterface $shippingAddressManagement,
        GetCartForUser $getCartForUser,
        ArrayManager $arrayManager,
        SetShippingAddressesOnCartInterface $setShippingAddressesOnCart
    ) {
        $this->shippingAddressManagement = $shippingAddressManagement;
        $this->getCartForUser = $getCartForUser;
        $this->arrayManager = $arrayManager;
        $this->setShippingAddressesOnCart = $setShippingAddressesOnCart;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $shippingAddresses = $this->arrayManager->get('input/shipping_addresses', $args);
        $maskedCartId = (string) $this->arrayManager->get('input/cart_id', $args);

        if (!$maskedCartId) {
            throw new GraphQlInputException(__('Required parameter "cart_id" is missing'));
        }

        if (!$shippingAddresses) {
            throw new GraphQlInputException(__('Required parameter "shipping_addresses" is missing'));
        }

        $cart = $this->getCartForUser->execute($maskedCartId, $context->getUserId());

        $this->setShippingAddressesOnCart->execute($context, $cart, $shippingAddresses);

        return [
            'cart' => [
                'cart_id' => $maskedCartId,
                'model' => $cart,
            ]
        ];
    }
}
