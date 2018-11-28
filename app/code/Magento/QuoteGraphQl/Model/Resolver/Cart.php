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
use Magento\QuoteGraphQl\Model\Cart\ExtractDataFromCart;

/**
 * @inheritdoc
 */
class Cart implements ResolverInterface
{
    /**
     * @var ExtractDataFromCart
     */
    private $extractDataFromCart;

    /**
     * @var GetCartForUser
     */
    private $getCartForUser;

    /**
     * @param GetCartForUser $getCartForUser
     * @param ExtractDataFromCart $extractDataFromCart
     */
    public function __construct(
        GetCartForUser $getCartForUser,
        ExtractDataFromCart $extractDataFromCart
    ) {
        $this->getCartForUser = $getCartForUser;
        $this->extractDataFromCart = $extractDataFromCart;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($args['cart_id'])) {
            throw new GraphQlInputException(__('Required parameter "cart_id" is missing'));
        }
        $maskedCartId = $args['cart_id'];

        $currentUserId = $context->getUserId();
        $cart = $this->getCartForUser->execute($maskedCartId, $currentUserId);

        $data = array_merge(
            [
                'cart_id' => $maskedCartId,
                'model' => $cart
            ],
            $this->extractDataFromCart->execute($cart)
        );

        return $data;
    }
}
