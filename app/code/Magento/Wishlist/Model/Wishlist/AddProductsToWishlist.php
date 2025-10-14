<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Model\Wishlist;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Wishlist\Model\ResourceModel\Wishlist as WishlistResourceModel;
use Magento\Wishlist\Model\Wishlist;
use Magento\Wishlist\Model\Wishlist\BuyRequest\BuyRequestBuilder;
use Magento\Wishlist\Model\Wishlist\Data\WishlistItem;
use Magento\Wishlist\Model\Wishlist\Data\WishlistOutput;

/**
 * Adding products to wishlist
 */
class AddProductsToWishlist
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
     * @var BuyRequestBuilder
     */
    private $buyRequestBuilder;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var WishlistResourceModel
     */
    private $wishlistResource;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param BuyRequestBuilder $buyRequestBuilder
     * @param WishlistResourceModel $wishlistResource
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        BuyRequestBuilder $buyRequestBuilder,
        WishlistResourceModel $wishlistResource
    ) {
        $this->productRepository = $productRepository;
        $this->buyRequestBuilder = $buyRequestBuilder;
        $this->wishlistResource = $wishlistResource;
    }

    /**
     * Adding products to wishlist
     *
     * @param Wishlist $wishlist
     * @param array $wishlistItems
     *
     * @return WishlistOutput
     *
     * @throws AlreadyExistsException
     */
    public function execute(Wishlist $wishlist, array $wishlistItems): WishlistOutput
    {
        foreach ($wishlistItems as $wishlistItem) {
            $this->addItemToWishlist($wishlist, $wishlistItem);
        }

        $wishlistOutput = $this->prepareOutput($wishlist);

        if ($wishlist->isObjectNew() || count($wishlistOutput->getErrors()) !== count($wishlistItems)) {
            $this->wishlistResource->save($wishlist);
        }

        return $wishlistOutput;
    }

    /**
     * Add product item to wishlist
     *
     * @param Wishlist $wishlist
     * @param WishlistItem $wishlistItem
     *
     * @return void
     */
    private function addItemToWishlist(Wishlist $wishlist, WishlistItem $wishlistItem): void
    {
        $sku = $wishlistItem->getParentSku() ?? $wishlistItem->getSku();

        try {
            $product = $this->productRepository->get($sku, false, null, true);
        } catch (NoSuchEntityException $e) {
            $this->addError(
                __('Could not find a product with SKU "%sku"', ['sku' => $sku])->render(),
                self::ERROR_PRODUCT_NOT_FOUND
            );

            return;
        }

        try {
            if ((int)$wishlistItem->getQuantity() === 0) {
                throw new LocalizedException(__("The quantity of a wish list item cannot be 0"));
            }
            $options = $this->buyRequestBuilder->build($wishlistItem, (int) $product->getId());
            $result = $wishlist->addNewItem($product, $options, true);

            if (is_string($result)) {
                $this->addError($result);
            }
        } catch (LocalizedException $exception) {
            $this->addError($exception->getMessage());
        } catch (\Throwable $e) {
            $this->addError(
                __(
                    'Could not add the product with SKU "%sku" to the wishlist:: %message',
                    ['sku' => $sku, 'message' => $e->getMessage()]
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
    private function addError(string $message, ?string $code = null): void
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
