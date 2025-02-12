<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\WishlistGraphQl\Model\Resolver;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Wishlist\Model\ResourceModel\Wishlist\Collection as WishlistCollection;
use Magento\Wishlist\Model\ResourceModel\Wishlist\CollectionFactory as WishlistCollectionFactory;
use Magento\Wishlist\Model\Wishlist;
use Magento\Wishlist\Model\Wishlist\Config as WishlistConfig;
use Magento\Wishlist\Model\WishlistFactory;
use Magento\WishlistGraphQl\Mapper\WishlistDataMapper;

/**
 * Fetches customer wishlist list
 */
class CustomerWishlists implements ResolverInterface
{
    /**
     * @var WishlistDataMapper
     */
    private $wishlistDataMapper;

    /**
     * @var WishlistConfig
     */
    private $wishlistConfig;

    /**
     * @var WishlistCollectionFactory
     */
    private $wishlistCollectionFactory;

    /**
     * @var WishlistFactory
     */
    private $wishlistFactory;

    /**
     * @param WishlistDataMapper $wishlistDataMapper
     * @param WishlistConfig $wishlistConfig
     * @param WishlistCollectionFactory $wishlistCollectionFactory
     * @param WishlistFactory $wishlistFactory
     */
    public function __construct(
        WishlistDataMapper $wishlistDataMapper,
        WishlistConfig $wishlistConfig,
        WishlistCollectionFactory $wishlistCollectionFactory,
        ?WishlistFactory $wishlistFactory = null
    ) {
        $this->wishlistDataMapper = $wishlistDataMapper;
        $this->wishlistConfig = $wishlistConfig;
        $this->wishlistCollectionFactory = $wishlistCollectionFactory;
        $this->wishlistFactory = $wishlistFactory ?:
            ObjectManager::getInstance()->get(WishlistFactory::class);
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

        if (null === $customerId || 0 === $customerId) {
            throw new GraphQlAuthorizationException(
                __('The current user cannot perform operations on wishlist')
            );
        }

        $currentPage = $args['currentPage'] ?? 1;
        $pageSize = $args['pageSize'] ?? 20;

        /** @var WishlistCollection $collection */
        $collection = $this->wishlistCollectionFactory->create();
        $collection->filterByCustomerId($customerId);

        if ($currentPage > 0) {
            $collection->setCurPage($currentPage);
        }

        if ($pageSize > 0) {
            $collection->setPageSize($pageSize);
        }

        $wishlists = [];

        /** @var Wishlist $wishList */
        foreach ($collection->getItems() as $wishList) {
            array_push($wishlists, $this->wishlistDataMapper->map($wishList));
        }
        if (empty($wishlists)) {
            $newWishlist = $this->wishlistFactory->create();
            $newWishlist->loadByCustomerId($context->getUserId(), true);
            array_push($wishlists, $this->wishlistDataMapper->map($newWishlist));
        }
        return $wishlists;
    }
}
