<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Model\Wishlist;

use Magento\Wishlist\Model\Item as WishlistItem;
use Magento\Wishlist\Model\ItemFactory as WishlistItemFactory;
use Magento\Wishlist\Model\ResourceModel\Item as WishlistItemResource;
use Magento\Wishlist\Model\Wishlist;
use Magento\Wishlist\Model\Wishlist\Data\WishlistOutput;

/**
 * Remove product items from wishlist
 */
class RemoveProductsFromWishlist
{
    /**#@+
     * Error message codes
     */
    private const ERROR_PRODUCT_NOT_FOUND = 'PRODUCT_NOT_FOUND';
    private const ERROR_UNDEFINED = 'UNDEFINED';
    /**#@-*/

    /**
     * @var array
     */
    private $errors = [];

    /**
     * @var WishlistItemFactory
     */
    private $wishlistItemFactory;

    /**
     * @var WishlistItemResource
     */
    private $wishlistItemResource;

    /**
     * @param WishlistItemFactory $wishlistItemFactory
     * @param WishlistItemResource $wishlistItemResource
     */
    public function __construct(
        WishlistItemFactory $wishlistItemFactory,
        WishlistItemResource $wishlistItemResource
    ) {
        $this->wishlistItemFactory = $wishlistItemFactory;
        $this->wishlistItemResource = $wishlistItemResource;
    }

    /**
     * Removing items from wishlist
     *
     * @param Wishlist $wishlist
     * @param array $wishlistItemsIds
     *
     * @return WishlistOutput
     */
    public function execute(Wishlist $wishlist, array $wishlistItemsIds): WishlistOutput
    {
        foreach ($wishlistItemsIds as $wishlistItemId) {
            $this->removeItemFromWishlist((int) $wishlistItemId);
        }

        return $this->prepareOutput($wishlist);
    }

    /**
     * Remove product item from wishlist
     *
     * @param int $wishlistItemId
     *
     * @return void
     */
    private function removeItemFromWishlist(int $wishlistItemId): void
    {
        try {
            /** @var WishlistItem $wishlistItem */
            $wishlistItem = $this->wishlistItemFactory->create();
            $this->wishlistItemResource->load($wishlistItem, $wishlistItemId);
            if (!$wishlistItem->getId()) {
                $this->addError(
                    __('Could not find a wishlist item with ID "%id"', ['id' => $wishlistItemId])->render(),
                    self::ERROR_PRODUCT_NOT_FOUND
                );
            }

            $this->wishlistItemResource->delete($wishlistItem);
        } catch (\Exception $e) {
            $this->addError(
                __(
                    'We can\'t delete the item with ID "%id" from the Wish List right now.',
                    ['id' => $wishlistItemId]
                )->render()
            );
        }
    }

    /**
     * Add wishlist line item error
     *
     * @param string $message
     * @param string|null $code
     *
     * @return void
     */
    private function addError(string $message, string $code = null): void
    {
        $this->errors[] = new Data\Error(
            $message,
            $code ?? self::ERROR_UNDEFINED
        );
    }

    /**
     * Prepare output
     *
     * @param Wishlist $wishlist
     *
     * @return WishlistOutput
     */
    private function prepareOutput(Wishlist $wishlist): WishlistOutput
    {
        $output = new WishlistOutput($wishlist, $this->errors);
        $this->errors = [];

        return $output;
    }
}
