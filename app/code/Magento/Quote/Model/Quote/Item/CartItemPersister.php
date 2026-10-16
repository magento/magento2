<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */

namespace Magento\Quote\Model\Quote\Item;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartItemInterface;

/**
 * Cart item save handler
 */
class CartItemPersister
{
    private const CONFIGURATION_KEYS = [
        'options',
        'super_attribute',
        'super_group',
        'bundle_option',
        'bundle_option_qty',
        'links',
    ];

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
     * Save cart item into cart
     *
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
                        __('The %1 Cart doesn\'t contain the %2 item.', $cartId, $itemId)
                    );
                }
                $productType = $currentItem->getProduct()->getTypeId();
                $buyRequestData = $this->cartItemOptionProcessor->getBuyRequest($productType, $item);
                if (is_object($buyRequestData) && $this->isConfigurationChanged($currentItem, $buyRequestData)) {
                    /** Update item product options */
                    if ($quote->getIsActive()) {
                        $item = $quote->updateItem($itemId, $buyRequestData);
                    }
                } else {
                    if ($item->getQty() !== $currentItem->getQty()) {
                        $currentItem->clearMessage();
                        $currentItem->setQty($qty);
                        /**
                         * Qty validation errors are stored as items message
                         * @see \Magento\CatalogInventory\Model\Quote\Item\QuantityValidator::validate
                         */
                        if (!empty($currentItem->getMessage()) && $currentItem->getHasError()) {
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
            throw new CouldNotSaveException(__("The quote couldn't be saved."));
        }
        $itemId = $item->getId();
        foreach ($quote->getAllItems() as $quoteItem) {
            /** @var \Magento\Quote\Model\Quote\Item $quoteItem */
            if ($itemId == $quoteItem->getId()) {
                $item = $this->cartItemOptionProcessor->addProductOptions($productType, $quoteItem);
                return $this->cartItemOptionProcessor->applyCustomOptions($item);
            }
        }
        throw new CouldNotSaveException(__("The quote couldn't be saved."));
    }

    /**
     * Check whether the submitted buy request configures the item differently than it is configured now
     *
     * @param \Magento\Quote\Model\Quote\Item $currentItem
     * @param DataObject $buyRequest
     * @return bool
     */
    private function isConfigurationChanged($currentItem, DataObject $buyRequest): bool
    {
        $currentData = $currentItem->getBuyRequest()->getData();
        $newData = $buyRequest->getData();
        unset($newData['qty']);

        foreach (self::CONFIGURATION_KEYS as $key) {
            if (isset($currentData[$key]) !== isset($newData[$key])) {
                return true;
            }
        }
        foreach ($newData as $key => $value) {
            if (!array_key_exists($key, $currentData)
                || json_encode($this->stringify($value)) !== json_encode($this->stringify($currentData[$key]))
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Cast scalars to strings recursively so that "1" and 1 compare equal
     *
     * @param mixed $value
     * @return mixed
     */
    private function stringify($value)
    {
        if (is_array($value)) {
            ksort($value);
            return array_map([$this, 'stringify'], $value);
        }
        return is_scalar($value) ? (string)$value : $value;
    }
}
