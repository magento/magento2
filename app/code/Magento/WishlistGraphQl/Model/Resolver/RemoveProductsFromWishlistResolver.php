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
use Magento\Wishlist\Model\Wishlist\Config as WishlistConfig;
use Magento\Wishlist\Model\Wishlist\Data\Error;
use Magento\Wishlist\Model\Wishlist\RemoveProductsFromWishlist;
use Magento\Wishlist\Model\WishlistFactory;
use Magento\WishlistGraphQl\Mapper\WishlistDataMapper;

/**
 * Removing products from wishlist resolver
 */
class RemoveProductsFromWishlistResolver implements ResolverInterface
{
    /**
     * @var WishlistDataMapper
     */
    private $wishlistDataMapper;

    /**
     * @var RemoveProductsFromWishlist
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
     * @param RemoveProductsFromWishlist $removeProductsFromWishlist
     */
    public function __construct(
        WishlistFactory $wishlistFactory,
        WishlistResourceModel $wishlistResource,
        WishlistConfig $wishlistConfig,
        WishlistDataMapper $wishlistDataMapper,
        RemoveProductsFromWishlist $removeProductsFromWishlist
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
        array $value = null,
        array $args = null
    ) {
        if (!$this->wishlistConfig->isEnabled()) {
            throw new GraphQlInputException(__('The wishlist is not currently available.'));
        }

        $customerId = $context->getUserId();

        /* Guest checking */
        if (!$customerId && 0 === $customerId) {
            throw new GraphQlAuthorizationException(__('The current user cannot perform operations on wishlist'));
        }

        $wishlistId = $args['wishlist_id'];

        if (!$wishlistId) {
            throw new GraphQlInputException(__('The wishlist id is missing.'));
        }

        $wishlist = $this->wishlistFactory->create();
        $this->wishlistResource->load($wishlist, $wishlistId);

        if (!$wishlist) {
            throw new GraphQlInputException(__('The wishlist was not found.'));
        }

        $wishlistItemsIds = $args['wishlist_items_ids'];
        $wishlistOutput = $this->removeProductsFromWishlist->execute($wishlist, $wishlistItemsIds);

        if (!empty($wishlistItemsIds)) {
            $this->wishlistResource->save($wishlist);
        }

        return [
            'wishlist' => $this->wishlistDataMapper->map($wishlistOutput->getWishlist()),
            'userInputErrors' => \array_map(
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
}
