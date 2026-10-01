<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\WishlistGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Wishlist\Model\Wishlist;
use Magento\Wishlist\Model\Wishlist\Config as WishlistConfig;
use Magento\Wishlist\Model\WishlistFactory;

/**
 * Fetches the Wishlist data according to the GraphQL schema
 */
class WishlistResolver implements ResolverInterface
{
    /**
     * @var WishlistFactory
     */
    private $wishlistFactory;

    /**
     * @var WishlistConfig
     */
    private $wishlistConfig;

    /**
     * @param WishlistFactory $wishlistFactory
     * @param WishlistConfig $wishlistConfig
     */
    public function __construct(
        WishlistFactory $wishlistFactory,
        WishlistConfig $wishlistConfig
    ) {
        $this->wishlistFactory = $wishlistFactory;
        $this->wishlistConfig = $wishlistConfig;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ) {
        if (!$this->wishlistConfig->isEnabled()) {
            throw new GraphQlInputException(__('The wishlist configuration is currently disabled.'));
        }

        $customerId = $context->getUserId();

        /* Guest checking */
        if (!$customerId && 0 === $customerId) {
            throw new GraphQlAuthorizationException(__('The current user cannot perform operations on wishlist'));
        }
        /** @var Wishlist $wishlist */
        $wishlist = $this->wishlistFactory->create();
        $wishlist->loadByCustomerId($customerId, true);

        return [
            'sharing_code' => $wishlist->getSharingCode(),
            'updated_at' => $wishlist->getUpdatedAt(),
            'items_count' => $wishlist->getItemsCount(),
            'name' => $wishlist->getName(),
            'model' => $wishlist,
        ];
    }
}
