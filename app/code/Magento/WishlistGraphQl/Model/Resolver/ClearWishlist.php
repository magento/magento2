<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\WishlistGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Wishlist\Model\ResourceModel\ClearWishlist as ClearWishlistResource;
use Magento\Wishlist\Model\ResourceModel\Wishlist as WishlistResource;
use Magento\Wishlist\Model\Wishlist;
use Magento\Wishlist\Model\Wishlist\Config as WishlistConfig;
use Magento\Wishlist\Model\Wishlist\Data\Error;
use Magento\Wishlist\Model\WishlistFactory;
use Magento\WishlistGraphQl\Mapper\WishlistDataMapper;

/**
 * Resolver to clear all the products from the specified wishlist
 */
class ClearWishlist implements ResolverInterface
{
    /**
     * ClearWishlist Constructor
     *
     * @param WishlistConfig $wishlistConfig
     * @param ClearWishlistResource $clearWishlistResource
     * @param WishlistFactory $wishlistFactory
     * @param WishlistResource $wishlistResource
     * @param WishlistDataMapper $wishlistDataMapper
     */
    public function __construct(
        private readonly WishlistConfig        $wishlistConfig,
        private readonly ClearWishlistResource $clearWishlistResource,
        private readonly WishlistFactory       $wishlistFactory,
        private readonly WishlistResource      $wishlistResource,
        private readonly WishlistDataMapper    $wishlistDataMapper
    ) {
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
    ): array {
        if (!$this->wishlistConfig->isEnabled()) {
            throw new GraphQlInputException(__('The wishlist configuration is currently disabled.'));
        }

        $customerId = (int)$context->getUserId();
        if (!$customerId) {
            throw new GraphQlAuthorizationException(__('The current user cannot perform operations on wishlist'));
        }

        $wishlistId = ((int)$args['wishlistId']) ?: null;
        if (!$wishlistId) {
            throw new GraphQlInputException(__('The wishlistId is required.'));
        }

        $wishlist = $this->getWishlist($wishlistId);
        if (!$wishlist->getId() || $customerId !== (int)$wishlist->getCustomerId()) {
            throw new GraphQlInputException(__('The wishlist was not found.'));
        }

        $wishlistOutput = $this->clearWishlistResource->execute($wishlist);

        return [
            'user_errors' => \array_map(
                function (Error $error) {
                    return [
                        'code' => $error->getCode(),
                        'message' => $error->getMessage(),
                    ];
                },
                $wishlistOutput->getErrors()
            ),
            'wishlist' => $this->wishlistDataMapper->map($wishlistOutput->getWishlist())
        ];
    }

    /**
     * Get customer wishlist
     *
     * @param int $wishlistId
     * @return Wishlist
     */
    private function getWishlist(int $wishlistId): Wishlist
    {
        $wishlist = $this->wishlistFactory->create();
        $this->wishlistResource->load($wishlist, $wishlistId);

        return $wishlist;
    }
}
