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
use Magento\Wishlist\Model\Wishlist\Data\WishlistOutput;
use Magento\Wishlist\Model\Wishlist\BuyRequest\BuyRequestBuilder;
use Magento\Wishlist\Model\Wishlist\Data\Error;

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
     * BuyRequestBuilder
     * @var BuyRequestBuilder $buyRequestBuilder
     */
    private $buyRequestBuilder;

    private const ERROR_UNDEFINED = 'UNDEFINED';

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
     * Update wishlist Item and set data from request
     *
     * @param object $options
     * @param Wishlist $wishlist
     *
     * @return WishlistOutput
     * @throws GraphQlInputException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(object $options, Wishlist $wishlist)
    {
        $itemId = $options->getId();
        if ($wishlist->getItem($itemId) == null) {
            $this->addError(
                __(
                    'The wishlist item with ID "%id" does not belong to the wishlist',
                    ['id' => $itemId]
                )->render()
            );
        } else {
            $buyRequest = $this->buyRequestBuilder->build($options);
            $item = $wishlist->getItem((int)$itemId);
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
                    if($options->getDescription()) {
                        $item->setDescription($options->getDescription());
                    }
                }
                $this->wishlistResource->save($wishlist);
            } else {
                throw new GraphQlInputException(__('The product does not exist.'));
            }
        }
        return $this->prepareOutput($wishlist);
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
        $this->errors[] = new Error(
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

