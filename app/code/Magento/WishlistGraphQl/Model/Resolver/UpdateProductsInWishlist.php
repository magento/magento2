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
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Wishlist\Model\ResourceModel\Wishlist as WishlistResourceModel;
use Magento\Wishlist\Model\Wishlist;
use Magento\Wishlist\Model\Wishlist\Config as WishlistConfig;
use Magento\Wishlist\Model\Wishlist\Data\Error;
use Magento\Wishlist\Model\Wishlist\Data\WishlistItemFactory;
use Magento\Wishlist\Model\WishlistFactory;
use Magento\WishlistGraphQl\Mapper\WishlistDataMapper;
use Magento\WishlistGraphQl\Model\UpdateWishlistItem;

/**
 * Update wishlist items resolver
 */
class UpdateProductsInWishlist implements ResolverInterface
{
    /**
     * @var UpdateWishlistItem
     */
    private $updateWishlistItem;

    /**
     * @var WishlistDataMapper
     */
    private $wishlistDataMapper;

    /**
     * @var WishlistConfig
     */
    private $wishlistConfig;

    /**
     * @var WishlistResourceModel
     */
    private $wishlistResource;

    /**
     * @var WishlistFactory
     */
    private $wishlistFactory;

    /**
     * @param WishlistResourceModel $wishlistResource
     * @param WishlistFactory $wishlistFactory
     * @param WishlistConfig $wishlistConfig
     * @param UpdateWishlistItem $updateWishlistItem
     * @param WishlistDataMapper $wishlistDataMapper
     */
    public function __construct(
        WishlistResourceModel $wishlistResource,
        WishlistFactory $wishlistFactory,
        WishlistConfig $wishlistConfig,
        UpdateWishlistItem $updateWishlistItem,
        WishlistDataMapper $wishlistDataMapper
    ) {
        $this->wishlistResource = $wishlistResource;
        $this->wishlistFactory = $wishlistFactory;
        $this->wishlistConfig = $wishlistConfig;
        $this->updateWishlistItem = $updateWishlistItem;
        $this->wishlistDataMapper = $wishlistDataMapper;
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
            throw new GraphQlInputException(__('The wishlist configuration is currently disabled'));
        }

        $customerId = $context->getUserId();

        if (null === $customerId || $customerId === 0) {
            throw new GraphQlAuthorizationException(__('The current user cannot perform operations on wishlist'));
        }

        $wishlist = $this->getWishlist((int) $args['wishlistId'], $customerId);

        if (null === $wishlist->getId() || $customerId !== (int) $wishlist->getCustomerId()) {
            throw new GraphQlInputException(__('Could not find the specified wishlist'));
        }

        $wishlistItems  = $this->getWishlistItems($args['wishlistItems'], $wishlist);

        foreach ($wishlistItems as $wishlistItem) {
            $this->updateWishlistItem->execute($wishlistItem, $wishlist);
        }

        $wishlistOutput = $this->updateWishlistItem->prepareOutput($wishlist);

        return [
            'wishlist' => $this->wishlistDataMapper->map($wishlistOutput->getWishlist()),
            'user_errors' => array_map(
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
     * Get DTO wishlist items
     *
     * @param array $wishlistItemsData
     * @param Wishlist $wishlist
     *
     * @return array
     */
    private function getWishlistItems(array $wishlistItemsData, Wishlist $wishlist): array
    {
        $wishlistItems = [];
        foreach ($wishlistItemsData as $wishlistItemData) {
            if (!isset($wishlistItemData['quantity'])) {
                $wishlistItem = $wishlist->getItem($wishlistItemData['wishlist_item_id']);
                if ($wishlistItem !== null) {
                    $wishlistItemData['quantity'] = (float) $wishlistItem->getQty();
                }
            }
            if (!isset($wishlistItemData['description'])) {
                $wishlistItem = $wishlist->getItem($wishlistItemData['wishlist_item_id']);
                if ($wishlistItem !== null) {
                    $wishlistItemData['description'] = $wishlistItem->getDescription();
                }
            }
            $wishlistItems[] = (new WishlistItemFactory())->create($wishlistItemData);
        }
        return $wishlistItems;
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

        if (null !== $wishlistId && 0 < $wishlistId) {
            $this->wishlistResource->load($wishlist, $wishlistId);
        } elseif ($customerId !== null) {
            $wishlist->loadByCustomerId($customerId, true);
        }

        return $wishlist;
    }
}
