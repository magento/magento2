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
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Magento\QuoteGraphQl\Model\Cart\SetBillingAddressOnCart as SetBillingAddressOnCartModel;

/**
 * Class SetBillingAddressOnCart
 *
 * Mutation resolver for setting billing address for shopping cart
 */
class SetBillingAddressOnCart implements ResolverInterface
{
    /**
     * @var GetCartForUser
     */
    private $getCartForUser;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var SetBillingAddressOnCartModel
     */
    private $setBillingAddressOnCart;

    /**
     * @param GetCartForUser $getCartForUser
     * @param ArrayManager $arrayManager
     * @param SetBillingAddressOnCartModel $setBillingAddressOnCart
     */
    public function __construct(
        GetCartForUser $getCartForUser,
        ArrayManager $arrayManager,
        SetBillingAddressOnCartModel $setBillingAddressOnCart
    ) {
        $this->getCartForUser = $getCartForUser;
        $this->arrayManager = $arrayManager;
        $this->setBillingAddressOnCart = $setBillingAddressOnCart;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $billingAddress = $this->arrayManager->get('input/billing_address', $args);
        $maskedCartId = $this->arrayManager->get('input/cart_id', $args);

        if (!$maskedCartId) {
            throw new GraphQlInputException(__('Required parameter "cart_id" is missing'));
        }
        if (!$billingAddress) {
            throw new GraphQlInputException(__('Required parameter "billing_address" is missing'));
        }

        $maskedCartId = $args['input']['cart_id'];
        $cart = $this->getCartForUser->execute($maskedCartId, $context->getUserId());

        $this->setBillingAddressOnCart->execute($context, $cart, $billingAddress);

        return [
            'cart' => [
                'cart_id' => $maskedCartId,
                'model' => $cart,
            ]
        ];
    }
}
