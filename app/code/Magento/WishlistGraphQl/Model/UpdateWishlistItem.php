<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\WishlistGraphQl\Model;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Wishlist\Model\ResourceModel\Wishlist as WishlistResourceModel;
use Magento\Wishlist\Model\Wishlist;
use Magento\Framework\DataObject;
use Magento\Wishlist\Model\Wishlist\Data\WishlistOutput;

/**
 * Update wishlist items helper
 */
class UpdateWishlistItem
{
    /**
     * @var WishlistResourceModel
     */
    private $wishlistResource;

    /**
     * @var array
     */
    private $errors = [];

    /**
     * @param WishlistResourceModel $wishlistResource
     */
    public function __construct(
        WishlistResourceModel $wishlistResource
    ) {
        $this->wishlistResource = $wishlistResource;
    }

    /**
     * Update wishlist Item and set data from request
     *
     * @param int $itemId
     * @param DataObject $buyRequest
     * @param Wishlist $wishlist
     *
     * @return WishlistOutput
     * @throws GraphQlInputException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(int $itemId, DataObject $buyRequest, Wishlist $wishlist)
    {
        $item = $wishlist->getItem((int)$itemId);

        if (!$item) {
            throw new GraphQlInputException(__('We can\'t specify a wish list item.'));
        }

        $product = $item->getProduct();
        $productId = $product->getId();

        if ($productId) {
            $buyRequest->setData('action', 'updateItem');
            $product->setWishlistStoreId($item->getStoreId());
            $cartCandidates = $product->getTypeInstance()->processConfiguration($buyRequest, clone $product);

            /**
             * If the product with options existed or not
             */
            if (is_string($cartCandidates)) {
                throw new GraphQlInputException(__('The product with options does not exist.'));
            }

            /**
             * If prepare process return one object
             */
            if (!is_array($cartCandidates)) {
                $cartCandidates = [$cartCandidates];
            }

            foreach ($cartCandidates as $candidate) {
                if ($candidate->getParentProductId()) {
                    continue;
                }
                $candidate->setWishlistStoreId($item->getStoreId());
                $qty = $buyRequest->getData('qty') ? $buyRequest->getData('qty') : 1;
                $item->setOptions($candidate->getCustomOptions());
                $item->setQty($qty);
            }
            $this->wishlistResource->save($wishlist);
        } else {
            throw new GraphQlInputException(__('The product does not exist.'));
        }
        return $this->prepareOutput($wishlist);
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
