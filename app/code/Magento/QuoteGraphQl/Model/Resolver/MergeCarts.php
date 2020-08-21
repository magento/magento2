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
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;

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
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @param GetCartForUser $getCartForUser
     * @param CartRepositoryInterface $cartRepository
     */
    public function __construct(
        GetCartForUser $getCartForUser,
        CartRepositoryInterface $cartRepository
    ) {
        $this->getCartForUser = $getCartForUser;
        $this->cartRepository = $cartRepository;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (empty($args['source_cart_id'])) {
            throw new GraphQlInputException(__('Required parameter "source_cart_id" is missing'));
        }

        if (empty($args['destination_cart_id'])) {
            throw new GraphQlInputException(__('Required parameter "destination_cart_id" is missing'));
        }

        /** @var ContextInterface $context */
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }

        $guestMaskedCartId = $args['source_cart_id'];
        $customerMaskedCartId = $args['destination_cart_id'];

        $currentUserId = $context->getUserId();
        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
        // passing customerId as null enforces source cart should always be a guestcart
        $guestCart = $this->getCartForUser->execute($guestMaskedCartId, null, $storeId);
        $customerCart = $this->getCartForUser->execute($customerMaskedCartId, $currentUserId, $storeId);
        $customerCart->merge($guestCart);
        $guestCart->setIsActive(false);
        $this->cartRepository->save($customerCart);
        $this->cartRepository->save($guestCart);
        return [
            'model' => $customerCart,
        ];
    }
}
