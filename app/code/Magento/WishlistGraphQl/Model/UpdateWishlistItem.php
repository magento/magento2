<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\WishlistGraphQl\Model;

use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Wishlist\Model\Item;
use Magento\Wishlist\Model\ResourceModel\Wishlist as WishlistResourceModel;
use Magento\Wishlist\Model\Wishlist;
use Magento\Wishlist\Model\Wishlist\BuyRequest\BuyRequestBuilder;
use Magento\Wishlist\Model\Wishlist\Data\Error as WishlistError;
use Magento\Wishlist\Model\Wishlist\Data\WishlistItem as WishlistItemData;
use Magento\Wishlist\Model\Wishlist\Data\WishlistOutput;

/**
 * Update wishlist items helper
 */
class UpdateWishlistItem
{
    private const ERROR_UNDEFINED = 'UNDEFINED';

    /**
     * @var WishlistResourceModel
     */
    private $wishlistResource;

    /**
     * @var BuyRequestBuilder
     */
    private $buyRequestBuilder;

    /**
     * @var array
     */
    private $errors = [];

    /**
     * @param WishlistResourceModel $wishlistResource
     * @param BuyRequestBuilder $buyRequestBuilder
     */
    public function __construct(
        WishlistResourceModel $wishlistResource,
        BuyRequestBuilder $buyRequestBuilder
    ) {
        $this->wishlistResource = $wishlistResource;
        $this->buyRequestBuilder = $buyRequestBuilder;
    }

    /**
     * Update wishlist item and set data from request
     *
     * @param WishlistItemData $wishlistItemData
     * @param Wishlist $wishlist
     *
     * @throws LocalizedException
     * @throws AlreadyExistsException
     */
    public function execute(WishlistItemData $wishlistItemData, Wishlist $wishlist)
    {
        $wishlistItemId = (int) $wishlistItemData->getId();
        $wishlistItemToUpdate = $wishlist->getItem($wishlistItemId);

        if (!$wishlistItemToUpdate) {
            $this->addError(
                __('The wishlist item with ID "%1" does not belong to the wishlist', $wishlistItemId)->render()
            );
        } elseif ((int) $wishlistItemData->getQuantity() === 0) {
            $this->addError(
                __('The quantity of a wishlist item cannot be 0')->render()
            );
        } else {
            $updatedOptions = $this->getUpdatedOptions($wishlistItemData, $wishlistItemToUpdate);

            $wishlistItemToUpdate->setOptions($updatedOptions);
            $wishlistItemToUpdate->setQty($wishlistItemData->getQuantity());
            if ($wishlistItemData->getDescription()) {
                $wishlistItemToUpdate->setDescription($wishlistItemData->getDescription());
            }

            $this->wishlistResource->save($wishlist);
        }
    }

    /**
     * Build the updated options for the specified wishlist item.
     *
     * @param WishlistItemData $wishlistItemData
     * @param Item $wishlistItemToUpdate
     * @return array
     * @throws LocalizedException
     */
    private function getUpdatedOptions(WishlistItemData $wishlistItemData, Item $wishlistItemToUpdate)
    {
        $wishlistItemId = $wishlistItemToUpdate->getId();
        $wishlistItemProduct = $wishlistItemToUpdate->getProduct();

        if (!$wishlistItemProduct->getId()) {
            throw new LocalizedException(
                __('Could not find product for the wishlist item with ID "%1"', $wishlistItemId)
            );
        }

        // Update the buy request using the wishlist item data. Use existing values for unspecified options.
        $newBuyRequest = $this->buyRequestBuilder
            ->build($wishlistItemData)
            ->setData('action', 'updateItem');
        $updatedBuyRequest = $wishlistItemToUpdate->getBuyRequest()->addData($newBuyRequest->toArray());

        // Get potential products to add to the cart for the product type using the updated buy request
        $wishlistItemProduct->setWishlistStoreId($wishlistItemToUpdate->getStoreId());
        $cartCandidates = $wishlistItemProduct->getTypeInstance()->processConfiguration(
            $updatedBuyRequest,
            clone $wishlistItemProduct
        );

        if (is_string($cartCandidates)) {
            throw new LocalizedException(
                __('Could not prepare product for the wishlist item with ID %1', $wishlistItemId)
            );
        }

        // Of the cart candidates, find the parent product and get its options
        if (!is_array($cartCandidates)) {
            $cartCandidates = [$cartCandidates];
        }
        $updatedOptions = [];
        foreach ($cartCandidates as $candidate) {
            if ($candidate->getParentProductId() === null) {
                $candidate->setWishlistStoreId($wishlistItemToUpdate->getStoreId());
                $updatedOptions = $candidate->getCustomOptions();
                break;
            }
        }

        return $updatedOptions;
    }

    /**
     * Add wishlist line item error
     *
     * @param string $message
     * @param string|null $code
     *
     * @return void
     */
    private function addError(string $message, ?string $code = null): void
    {
        $this->errors[] = new WishlistError(
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
    public function prepareOutput(Wishlist $wishlist): WishlistOutput
    {
        $output = new WishlistOutput($wishlist, $this->errors);
        $this->errors = [];

        return $output;
    }
}
