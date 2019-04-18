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
use Magento\QuoteGraphQl\Model\Cart\SetBillingAddressOnCart as SetBillingAddressOnCartModel;

/**
 * Mutation resolver for setting billing address for shopping cart
 */
class SetBillingAddressOnCart implements ResolverInterface
{
    /**
     * @var GetCartForUser
     */
    private $getCartForUser;

    /**
     * @var SetBillingAddressOnCartModel
     */
    private $setBillingAddressOnCart;

    /**
     * @param GetCartForUser $getCartForUser
     * @param SetBillingAddressOnCartModel $setBillingAddressOnCart
     */
    public function __construct(
        GetCartForUser $getCartForUser,
        SetBillingAddressOnCartModel $setBillingAddressOnCart
    ) {
        $this->getCartForUser = $getCartForUser;
        $this->setBillingAddressOnCart = $setBillingAddressOnCart;
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

        if (!isset($args['input']['billing_address']) || empty($args['input']['billing_address'])) {
            throw new GraphQlInputException(__('Required parameter "billing_address" is missing'));
        }
        $billingAddress = $args['input']['billing_address'];

        $cart = $this->getCartForUser->execute($maskedCartId, $context->getUserId());
        $this->setBillingAddressOnCart->execute($context, $cart, $billingAddress);

        return [
            'cart' => [
                'model' => $cart,
            ],
        ];
    }
}
