<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
 * Fetches customer wishlist data
 */
class CustomerWishlistResolver implements ResolverInterface
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

        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }

        /** @var Wishlist $wishlist */
        $wishlist = $this->wishlistFactory->create();
        $wishlist->loadByCustomerId($context->getUserId(), true);

        return [
            'id' => (string)$wishlist->getId(),
            'sharing_code' => $wishlist->getSharingCode(),
            'updated_at' => $wishlist->getUpdatedAt(),
            'items_count' => $wishlist->getItemsCount(),
            'model' => $wishlist,
        ];
    }
}
