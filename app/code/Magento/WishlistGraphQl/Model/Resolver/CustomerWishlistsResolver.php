<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\WishlistGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Wishlist\Model\ResourceModel\Wishlist\CollectionFactory;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;

/**
 * Fetches the Wishlists data according to the GraphQL schema
 */
class CustomerWishlistsResolver implements ResolverInterface
{
    /**
     * @var CollectionFactory
     */
    private $_wishlistCollectionFactory;

    /**
     * @param CollectionFactory $wishlistCollectionFactory
     */
    public function __construct(CollectionFactory $wishlistCollectionFactory)
    {
        $this->_wishlistCollectionFactory = $wishlistCollectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        /* Guest checking */
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The current user cannot perform operations on wishlist'));
        }
        $collection = $this->_wishlistCollectionFactory->create()->filterByCustomerId($context->getUserId());
        $wishlistsData = [];
        if (0 === $collection->getSize()) {
            return $wishlistsData;
        }
        $wishlists = $collection->getItems();

        foreach ($wishlists as $wishlist) {
            $wishlistsData [] = [
                'sharing_code' => $wishlist->getSharingCode(),
                'updated_at' => $wishlist->getUpdatedAt(),
                'items_count' => $wishlist->getItemsCount(),
                'model' => $wishlist,
            ];
        }
        return $wishlistsData;
    }
}
