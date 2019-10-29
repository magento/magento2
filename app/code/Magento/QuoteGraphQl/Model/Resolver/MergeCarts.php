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

/**
 * Merge Carts Resolver
 */
class MergeCarts implements ResolverInterface
{
    /**
     * @var GetCartForUser
     */
    private $getCartForUser;

    /**
     * @param GetCartForUser $getCartForUser
     */
    public function __construct(
        GetCartForUser $getCartForUser
    ) {
        $this->getCartForUser = $getCartForUser;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (empty($args['source_cart_id'])) {
            throw new GraphQlInputException(__('Required parameter "cart_id" is missing'));
        }

        if (empty($args['destination_cart_id'])) {
            throw new GraphQlInputException(__('Required parameter "destination_cart_id" is missing'));
        }

        $guestMaskedCartId = $args['source_cart_id'];
        $customerMaskedCartId = $args['destination_cart_id'];

        $currentUserId = $context->getUserId();
        $storeId = $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
        // passing customerId as null enforces source cart should always be a guestcart
        $guestCart = $this->getCartForUser->execute($guestMaskedCartId, null, $storeId);
        $customerCart = $this->getCartForUser->execute($customerMaskedCartId, $currentUserId, $storeId);
        $customerCart->merge($guestCart);

        return [
            'model' => $customerCart,
        ];
    }
}
