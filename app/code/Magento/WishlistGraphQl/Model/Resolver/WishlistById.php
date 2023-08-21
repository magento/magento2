<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\WishlistGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Wishlist\Model\ResourceModel\Wishlist as WishlistResourceModel;
use Magento\Wishlist\Model\Wishlist;
use Magento\Wishlist\Model\Wishlist\Config as WishlistConfig;
use Magento\Wishlist\Model\WishlistFactory;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\WishlistGraphQl\Mapper\WishlistDataMapper;

/**
 * Fetches the Wishlist data by ID according to the GraphQL schema
 */
class WishlistById implements ResolverInterface
{
    /**
     * @var WishlistResourceModel
     */
    private $wishlistResource;

    /**
     * @var WishlistFactory
     */
    private $wishlistFactory;

    /**
     * @var WishlistDataMapper
     */
    private $wishlistDataMapper;

    /**
     * @var WishlistConfig
     */
    private $wishlistConfig;

    /**
     * @param WishlistResourceModel $wishlistResource
     * @param WishlistFactory $wishlistFactory
     * @param WishlistDataMapper $wishlistDataMapper
     * @param WishlistConfig $wishlistConfig
     */
    public function __construct(
        WishlistResourceModel $wishlistResource,
        WishlistFactory $wishlistFactory,
        WishlistDataMapper $wishlistDataMapper,
        WishlistConfig $wishlistConfig
    ) {
        $this->wishlistResource = $wishlistResource;
        $this->wishlistFactory = $wishlistFactory;
        $this->wishlistDataMapper = $wishlistDataMapper;
        $this->wishlistConfig = $wishlistConfig;
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
        if (!$this->wishlistConfig->isEnabled()) {
            throw new GraphQlInputException(__('The wishlist configuration is currently disabled.'));
        }

        $customerId = $context->getUserId();

        if (null === $customerId || 0 === $customerId) {
            throw new GraphQlAuthorizationException(
                __('The current user cannot perform operations on wishlist')
            );
        }

        $wishlist = $this->getWishlist((int) $args['id'], $customerId);

        if (null === $wishlist->getId() || (int) $wishlist->getCustomerId() !== $customerId) {
            return [];
        }

        return $this->wishlistDataMapper->map($wishlist);
    }

    /**
     * Get wishlist
     *
     * @param int $wishlistId
     * @param int $customerId
     *
     * @return Wishlist
     */
    private function getWishlist(int $wishlistId, int $customerId): Wishlist
    {
        $wishlist = $this->wishlistFactory->create();

        if ($wishlistId > 0) {
            $this->wishlistResource->load($wishlist, $wishlistId);
        } else {
            $this->wishlistResource->load($wishlist, $customerId, 'customer_id');
        }

        return $wishlist;
    }
}
