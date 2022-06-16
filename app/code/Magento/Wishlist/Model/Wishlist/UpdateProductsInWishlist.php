<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Model\Wishlist;

use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Framework\Exception\LocalizedException;
use Magento\Wishlist\Model\Item as WishlistItem;
use Magento\Wishlist\Model\ItemFactory as WishlistItemFactory;
use Magento\Wishlist\Model\ResourceModel\Item as WishlistItemResource;
use Magento\Wishlist\Model\Wishlist;
use Magento\Wishlist\Model\Wishlist\BuyRequest\BuyRequestBuilder;
use Magento\Wishlist\Model\Wishlist\Data\WishlistItem as WishlistItemData;
use Magento\Wishlist\Model\Wishlist\Data\WishlistOutput;

/**
 * Updating product items in wishlist
 */
class UpdateProductsInWishlist
{
    /**#@+
     * Error message codes
     */
    private const ERROR_UNDEFINED = 'UNDEFINED';
    /**#@-*/

    /**
     * @var array
     */
    private $errors = [];

    /**
     * @var BuyRequestBuilder
     */
    private $buyRequestBuilder;

    /**
     * @var WishlistItemFactory
     */
    private $wishlistItemFactory;

    /**
     * @var WishlistItemResource
     */
    private $wishlistItemResource;

    /**
     * @param BuyRequestBuilder $buyRequestBuilder
     * @param WishlistItemFactory $wishlistItemFactory
     * @param WishlistItemResource $wishlistItemResource
     */
    public function __construct(
        BuyRequestBuilder $buyRequestBuilder,
        WishlistItemFactory $wishlistItemFactory,
        WishlistItemResource $wishlistItemResource
    ) {
        $this->buyRequestBuilder = $buyRequestBuilder;
        $this->wishlistItemFactory = $wishlistItemFactory;
        $this->wishlistItemResource = $wishlistItemResource;
    }

    /**
     * Adding products to wishlist
     *
     * @param Wishlist $wishlist
     * @param array $wishlistItems
     *
     * @return WishlistOutput
     */
    public function execute(Wishlist $wishlist, array $wishlistItems): WishlistOutput
    {
        foreach ($wishlistItems as $wishlistItem) {
            $this->updateItemInWishlist($wishlist, $wishlistItem);
        }

        return $this->prepareOutput($wishlist);
    }

    /**
     * Update product item in wishlist
     *
     * @param Wishlist $wishlist
     * @param WishlistItemData $wishlistItemData
     *
     * @return void
     */
    private function updateItemInWishlist(Wishlist $wishlist, WishlistItemData $wishlistItemData): void
    {
        try {
            if ($wishlist->getItem($wishlistItemData->getId()) == null) {
                throw new LocalizedException(
                    __(
                        'The wishlist item with ID "%id" does not belong to the wishlist',
                        ['id' => $wishlistItemData->getId()]
                    )
                );
            }
            $wishlist->getItemCollection()->clear();
            $options = $this->buyRequestBuilder->build($wishlistItemData);
            /** @var WishlistItem $wishlistItem */
            $wishlistItem = $this->wishlistItemFactory->create();
            $this->wishlistItemResource->load($wishlistItem, $wishlistItemData->getId());
            $wishlistItem->setDescription($wishlistItemData->getDescription());
            if ((int)$wishlistItemData->getQuantity() === 0) {
                throw new LocalizedException(__("The quantity of a wish list item cannot be 0"));
            }
            if ($wishlistItem->getProduct()->getStatus() == Status::STATUS_DISABLED) {
                throw new LocalizedException(__("The product is disabled"));
            }
            $resultItem = $wishlist->updateItem($wishlistItem, $options);

            if (is_string($resultItem)) {
                $this->addError($resultItem);
            }
        } catch (LocalizedException $exception) {
            $this->addError($exception->getMessage());
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
