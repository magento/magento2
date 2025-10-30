<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart\MergeCarts;

use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Model\Stock;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartItemRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\Checkout\Model\Config;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Api\Data\ProductInterface;

class CartQuantityValidator implements CartQuantityValidatorInterface
{
    /**
     * Array to hold cumulative quantities for each SKU
     *
     * @var array
     */
    private array $cumulativeQty = [];

    /**
     * CartQuantityValidator Constructor
     *
     * @param CartItemRepositoryInterface $cartItemRepository
     * @param StockRegistryInterface $stockRegistry
     * @param Config $config
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly CartItemRepositoryInterface $cartItemRepository,
        private readonly StockRegistryInterface $stockRegistry,
        private readonly Config $config,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Validate combined cart quantities to ensure they are within available stock
     *
     * @param CartInterface $customerCart
     * @param CartInterface $guestCart
     * @return bool
     */
    public function validateFinalCartQuantities(CartInterface $customerCart, CartInterface $guestCart): bool
    {
        $modified = false;
        $this->cumulativeQty = [];

        /** @var \Magento\Quote\Model\Quote $guestCart */
        /** @var \Magento\Quote\Model\Quote $customerCart */
        /** @var \Magento\Quote\Model\Quote\Item $guestCartItem */
        foreach ($guestCart->getAllVisibleItems() as $guestCartItem) {
            foreach ($customerCart->getAllItems() as $customerCartItem) {
                if (!$customerCartItem->compare($guestCartItem)) {
                    continue;
                }

                $mergePreference = $this->config->getCartMergePreference();

                if ($mergePreference === Config::CART_PREFERENCE_CUSTOMER) {
                    $this->safeDeleteCartItem((int)$guestCart->getId(), (int)$guestCartItem->getItemId());
                    $modified = true;
                    break;
                }

                $product = $customerCartItem->getProduct();
                $sku = $product->getSku();
                $websiteId = (int) $product->getStore()->getWebsiteId();

                $isQtyValid = $customerCartItem->getChildren()
                    ? $this->validateCompositeProductQty($guestCartItem, $customerCartItem)
                    : $this->validateProductQty(
                        $product,
                        $sku,
                        $guestCartItem->getQty(),
                        $customerCartItem->getQty(),
                        $websiteId
                    );

                if ($mergePreference === Config::CART_PREFERENCE_GUEST) {
                    $this->safeDeleteCartItem((int)$customerCart->getId(), (int)$customerCartItem->getItemId());
                    $modified = true;
                }

                if (!$isQtyValid) {
                    $this->safeDeleteCartItem((int)$guestCart->getId(), (int)$guestCartItem->getItemId());
                    $modified = true;
                }

                break;
            }
        }

        $this->cumulativeQty = [];

        return $modified;
    }

    /**
     * Validate product quantity against available stock
     *
     * @param ProductInterface $product
     * @param string $sku
     * @param float $guestItemQty
     * @param float $customerItemQty
     * @param int $websiteId
     * @return bool
     */
    private function validateProductQty(
        ProductInterface $product,
        string $sku,
        float $guestItemQty,
        float $customerItemQty,
        int $websiteId
    ): bool {
        $salableQty = $this->stockRegistry->getStockStatus($product->getId(), $websiteId)->getQty();

        $this->cumulativeQty[$sku] ??= 0;
        $this->cumulativeQty[$sku] += $this->getCurrentCartItemQty($guestItemQty, $customerItemQty);

        // If backorders are enabled, allow quantities beyond available stock
        if ($this->isBackordersEnabled($product)) {
            return true;
        }

        return $salableQty >= $this->cumulativeQty[$sku];
    }

    /**
     * Validate composite product quantities against available stock
     *
     * @param Item $guestItem
     * @param Item $customerItem
     * @return bool
     */
    private function validateCompositeProductQty(Item $guestItem, Item $customerItem): bool
    {
        $guestChildren = $guestItem->getChildren();
        $customerChildren = $customerItem->getChildren();

        foreach ($customerChildren as $customerChild) {
            $sku = $customerChild->getProduct()->getSku();
            $guestChild = $this->retrieveChildItem($guestChildren, $sku);

            $guestQty = $guestChild ? $guestItem->getQty() * $guestChild->getQty() : 0;
            $customerQty = $customerItem->getQty() * $customerChild->getQty();

            $product = $customerChild->getProduct();
            $websiteId = (int) $product->getStore()->getWebsiteId();

            // If backorders are enabled for this product, skip quantity validation
            if ($this->isBackordersEnabled($product)) {
                continue;
            }

            if (!$this->validateProductQty($product, $sku, $guestQty, $customerQty, $websiteId)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Find a child item by SKU in the list of children
     *
     * @param CartItemInterface[] $children
     * @param string $sku
     * @return CartItemInterface|null
     */
    private function retrieveChildItem(array $children, string $sku): ?CartItemInterface
    {
        foreach ($children as $child) {
            /** @var \Magento\Quote\Model\Quote\Item $child */
            if ($child->getProduct()->getSku() === $sku) {
                return $child;
            }
        }

        return null;
    }

    /**
     * Get the current cart item quantity based on the merge preference
     *
     * @param float $guestCartItemQty
     * @param float $customerCartItemQty
     * @return float
     */
    private function getCurrentCartItemQty(float $guestCartItemQty, float $customerCartItemQty): float
    {
        return match ($this->config->getCartMergePreference()) {
            Config::CART_PREFERENCE_CUSTOMER => $customerCartItemQty,
            Config::CART_PREFERENCE_GUEST => $guestCartItemQty,
            default => $guestCartItemQty + $customerCartItemQty
        };
    }

    /**
     * Safely delete a cart item by ID, logging any exceptions
     *
     * @param int $cartId
     * @param int $itemId
     * @return void
     */
    private function safeDeleteCartItem(int $cartId, int $itemId): void
    {
        try {
            $this->cartItemRepository->deleteById($cartId, $itemId);
        } catch (NoSuchEntityException | CouldNotSaveException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Check if backorders are enabled for the stock item
     *
     * @param Product $product
     * @return bool
     */
    private function isBackordersEnabled(Product $product): bool
    {
        $backorders = $this->stockRegistry->getStockItem(
            $product->getId(),
            $product->getStore()->getWebsiteId()
        )->getBackorders();
        return $backorders == Stock::BACKORDERS_YES_NONOTIFY ||
            $backorders == Stock::BACKORDERS_YES_NOTIFY;
    }
}
