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
use Magento\Wishlist\Model\ResourceModel\Wishlist as WishlistResourceModel;
use Magento\Wishlist\Model\Wishlist;
use Magento\Wishlist\Model\Wishlist\Config as WishlistConfig;
use Magento\Wishlist\Model\Wishlist\Data\Error;
use Magento\Wishlist\Model\Wishlist\Data\WishlistItemFactory;
use Magento\Wishlist\Model\Wishlist\RemoveProductsFromWishlist as RemoveProductsFromWishlistModel;
use Magento\Wishlist\Model\WishlistFactory;
use Magento\WishlistGraphQl\Mapper\WishlistDataMapper;

/**
 * Removing products from wishlist resolver
 */
class RemoveProductsFromWishlist implements ResolverInterface
{
    /**
     * @var WishlistDataMapper
     */
    private $wishlistDataMapper;

    /**
     * @var RemoveProductsFromWishlistModel
     */
    private $removeProductsFromWishlist;

    /**
     * @var WishlistFactory
     */
    private $wishlistFactory;

    /**
     * @var WishlistConfig
     */
    private $wishlistConfig;

    /**
     * @var WishlistResourceModel
     */
    private $wishlistResource;

    /**
     * @param WishlistFactory $wishlistFactory
     * @param WishlistResourceModel $wishlistResource
     * @param WishlistConfig $wishlistConfig
     * @param WishlistDataMapper $wishlistDataMapper
     * @param RemoveProductsFromWishlistModel $removeProductsFromWishlist
     */
    public function __construct(
        WishlistFactory $wishlistFactory,
        WishlistResourceModel $wishlistResource,
        WishlistConfig $wishlistConfig,
        WishlistDataMapper $wishlistDataMapper,
        RemoveProductsFromWishlistModel $removeProductsFromWishlist
    ) {
        $this->wishlistResource = $wishlistResource;
        $this->wishlistConfig = $wishlistConfig;
        $this->wishlistFactory = $wishlistFactory;
        $this->wishlistDataMapper = $wishlistDataMapper;
        $this->removeProductsFromWishlist = $removeProductsFromWishlist;
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
        if ($customerId === null || 0 === $customerId) {
            throw new GraphQlAuthorizationException(__('The current user cannot perform operations on wishlist'));
        }

        $wishlistId = ((int) $args['wishlistId']) ?: null;
        $wishlist = $this->getWishlist($wishlistId, $customerId);

        if (null === $wishlist->getId() || $customerId !== (int) $wishlist->getCustomerId()) {
            throw new GraphQlInputException(__('The wishlist was not found.'));
        }

        $wishlistItemsIds = $args['wishlistItemsIds'];
        $wishlistOutput = $this->removeProductsFromWishlist->execute($wishlist, $wishlistItemsIds);

        if (!empty($wishlistItemsIds)) {
            $this->wishlistResource->save($wishlist);
        }

        return [
            'wishlist' => $this->wishlistDataMapper->map($wishlistOutput->getWishlist()),
            'user_errors' => \array_map(
                function (Error $error) {
                    return [
                        'code' => $error->getCode(),
                        'message' => $error->getMessage(),
                    ];
                },
                $wishlistOutput->getErrors()
            )
        ];
    }

    /**
     * Get customer wishlist
     *
     * @param int|null $wishlistId
     * @param int|null $customerId
     *
     * @return Wishlist
     */
    private function getWishlist(?int $wishlistId, ?int $customerId): Wishlist
    {
        $wishlist = $this->wishlistFactory->create();

        if ($wishlistId !== null && $wishlistId > 0) {
            $this->wishlistResource->load($wishlist, $wishlistId);
        } elseif ($customerId !== null) {
            $wishlist->loadByCustomerId($customerId, true);
        }

        return $wishlist;
    }
}
