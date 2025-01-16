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
use Magento\QuoteGraphQl\Model\Cart\CheckCartCheckoutAllowance;

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
     * @var CheckCartCheckoutAllowance
     */
    private $checkCartCheckoutAllowance;

    /**
     * @param GetCartForUser $getCartForUser
     * @param SetBillingAddressOnCartModel $setBillingAddressOnCart
     * @param CheckCartCheckoutAllowance $checkCartCheckoutAllowance
     */
    public function __construct(
        GetCartForUser $getCartForUser,
        SetBillingAddressOnCartModel $setBillingAddressOnCart,
        CheckCartCheckoutAllowance $checkCartCheckoutAllowance
    ) {
        $this->getCartForUser = $getCartForUser;
        $this->setBillingAddressOnCart = $setBillingAddressOnCart;
        $this->checkCartCheckoutAllowance = $checkCartCheckoutAllowance;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null)
    {
        if (empty($args['input']['cart_id'])) {
            throw new GraphQlInputException(__('Required parameter "cart_id" is missing'));
        }
        $maskedCartId = $args['input']['cart_id'];

        if (empty($args['input']['billing_address'])) {
            throw new GraphQlInputException(__('Required parameter "billing_address" is missing'));
        }
        $billingAddress = $args['input']['billing_address'];

        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
        $cart = $this->getCartForUser->execute($maskedCartId, $context->getUserId(), $storeId);
        $this->checkCartCheckoutAllowance->execute($cart);
        $this->setBillingAddressOnCart->execute($context, $cart, $billingAddress);

        return [
            'cart' => [
                'model' => $cart,
            ],
        ];
    }
}
