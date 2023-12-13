<?php
/************************************************************************
 *
 * Copyright 2023 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 * ************************************************************************
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\CatalogInventory\Api\StockStatusRepositoryInterface;
use Magento\Quote\Model\Quote\Item;

/**
 * @inheritdoc
 */
class CheckAvailability implements ResolverInterface
{
    /**
     * Product type code
     */
    private const PRODUCT_TYPE_BUNDLE = "bundle";

    /**
     * @var StockStatusRepositoryInterface
     */
    private StockStatusRepositoryInterface $stockStatusRepository;

    /**
     * CheckAvailability constructor
     *
     * @param StockStatusRepositoryInterface $stockStatusRepository
     */
    public function __construct(
        StockStatusRepositoryInterface $stockStatusRepository
    ) {
        $this->stockStatusRepository = $stockStatusRepository;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
        /** @var Item $cartItem */
        $cartItem = $value['model'];

        return $this->checkProductQtyStatus($cartItem) ? "available" : "unavailable";
    }

    /**
     * Check item status available or unavailable
     *
     * @param Item $cartItem
     * @return bool
     */
    private function checkProductQtyStatus(Item $cartItem): bool
    {
        $requestedQty = 0;
        $previousQty = 0;

        if (!$cartItem->getQuote()->getItems()) {
            return false;
        }

        foreach ($cartItem->getQuote()->getItems() as $item) {
            if ($item->getItemId() == $cartItem->getItemId()) {
                $requestedQty = $item->getQtyToAdd() ?? $item->getQty();
                $previousQty = $item->getPreviousQty() ?? 0;
            }
        }

        if ($cartItem->getProductType() == self::PRODUCT_TYPE_BUNDLE) {
            $qtyOptions = $cartItem->getQtyOptions();
            $totalRequestedQty = $previousQty + $requestedQty;
            foreach ($qtyOptions as $qtyOption) {
                $productId = (int) $qtyOption->getProductId();
                $requiredItemQty = (float) $qtyOption->getValue();
                if ($totalRequestedQty) {
                    $requiredItemQty = $requiredItemQty * $totalRequestedQty;
                }
                if (!$this->isRequiredStockAvailable($productId, $requiredItemQty)) {
                    return false;
                }
            }
        } else {
            $requiredItemQty =  $requestedQty + $previousQty;
            $productId = (int) $cartItem->getProduct()->getId();
            return $this->isRequiredStockAvailable($productId, $requiredItemQty);
        }
        return true;
    }

    /**
     * Check if is required product available in stock
     *
     * @param int $productId
     * @param float $requiredQuantity
     * @return bool
     */
    private function isRequiredStockAvailable(int $productId, float $requiredQuantity): bool
    {
        $stock = $this->stockStatusRepository->get($productId);
        return ($stock->getQty() >= $requiredQuantity);
    }
}
