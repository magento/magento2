<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart\MergeCarts;


use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartItemRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartItemInterface;

class CartQuantityValidator implements CartQuantityValidatorInterface
{
    /**
     * @var CartItemRepositoryInterface
     */
    private $cartItemRepository;

    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @param CartItemRepositoryInterface $cartItemRepository
     * @param StockRegistryInterface $stockRegistry
     */
    public function __construct(
        CartItemRepositoryInterface $cartItemRepository,
        StockRegistryInterface $stockRegistry
    ) {
        $this->cartItemRepository = $cartItemRepository;
        $this->stockRegistry = $stockRegistry;
    }

    /**
     * Validate combined cart quantities to make sure they are within available stock
     *
     * @param CartInterface $customerCart
     * @param CartInterface $guestCart
     * @return bool
     */
    public function validateFinalCartQuantities(CartInterface $customerCart, CartInterface $guestCart): bool
    {
        $modified = false;
        /** @var CartItemInterface $guestCartItem */
        foreach ($guestCart->getAllVisibleItems() as $guestCartItem) {
            foreach ($customerCart->getAllItems() as $customerCartItem) {
                if ($customerCartItem->compare($guestCartItem)) {
                    $product = $customerCartItem->getProduct();
                    $stockCurrentQty = $this->stockRegistry->getStockStatus(
                        $product->getId(),
                        $product->getStore()->getWebsiteId()
                    )->getQty();
                    if ($stockCurrentQty < $guestCartItem->getQty() + $customerCartItem->getQty()) {
                        try {
                            $this->cartItemRepository->deleteById($guestCart->getId(), $guestCartItem->getItemId());
                            $modified = true;
                        } catch (NoSuchEntityException $e) {
                            continue;
                        } catch (CouldNotSaveException $e) {
                            continue;
                        }
                    }
                }
            }
        }
        return $modified;
    }
}
