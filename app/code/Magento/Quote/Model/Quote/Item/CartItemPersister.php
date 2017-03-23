<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Quote\Item;

use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Catalog\Api\ProductRepositoryInterface;

class CartItemPersister
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var CartItemOptionsProcessor
     */
    private $cartItemOptionProcessor;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param CartItemOptionsProcessor $cartItemOptionProcessor
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        CartItemOptionsProcessor $cartItemOptionProcessor
    ) {
        $this->productRepository = $productRepository;
        $this->cartItemOptionProcessor = $cartItemOptionProcessor;
    }

    /**
     * @param CartInterface $quote
     * @param CartItemInterface $item
     * @return CartItemInterface
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function save(CartInterface $quote, CartItemInterface $item)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $qty = $item->getQty();
        if (!is_numeric($qty) || $qty <= 0) {
            throw InputException::invalidFieldValue('qty', $qty);
        }
        $cartId = $item->getQuoteId();
        $itemId = $item->getItemId();
        try {
            /** Update existing item */
            if (isset($itemId)) {
                $currentItem = $quote->getItemById($itemId);
                if (!$currentItem) {
                    throw new NoSuchEntityException(
                        __('Cart %1 does not contain item %2', $cartId, $itemId)
                    );
                }
                $productType = $currentItem->getProduct()->getTypeId();
                $buyRequestData = $this->cartItemOptionProcessor->getBuyRequest($productType, $item);
                if (is_object($buyRequestData)) {
                    /** Update item product options */
                    $item = $quote->updateItem($itemId, $buyRequestData);
                } else {
                    if ($item->getQty() !== $currentItem->getQty()) {
                        $currentItem->setQty($qty);
                        /**
                         * Qty validation errors are stored as items message
                         * @see \Magento\CatalogInventory\Model\Quote\Item\QuantityValidator::validate
                         */
                        if (!empty($currentItem->getMessage())) {
                            throw new LocalizedException(__($currentItem->getMessage()));
                        }
                    }
                }
            } else {
                /** add new item to shopping cart */
                $product = $this->productRepository->get($item->getSku());
                $productType = $product->getTypeId();
                $item = $quote->addProduct(
                    $product,
                    $this->cartItemOptionProcessor->getBuyRequest($productType, $item)
                );
                if (is_string($item)) {
                    throw new LocalizedException(__($item));
                }
            }
        } catch (NoSuchEntityException $e) {
            throw $e;
        } catch (LocalizedException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Could not save quote'));
        }
        $itemId = $item->getId();
        foreach ($quote->getAllItems() as $quoteItem) {
            /** @var \Magento\Quote\Model\Quote\Item $quoteItem */
            if ($itemId == $quoteItem->getId()) {
                $item = $this->cartItemOptionProcessor->addProductOptions($productType, $quoteItem);
                return $this->cartItemOptionProcessor->applyCustomOptions($item);
            }
        }
        throw new CouldNotSaveException(__('Could not save quote'));
    }
}
