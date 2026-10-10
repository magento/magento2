<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\Catalog\Helper\Data as CatalogHelper;

class SetBasePriceObserver implements ObserverInterface
{
    /**
     * @var CatalogHelper
     */
    private $catalogHelper;

    /**
     * @param CatalogHelper $catalogHelper
     */
    public function __construct(
        CatalogHelper $catalogHelper
    ) {
        $this->catalogHelper = $catalogHelper;
    }

    /**
     * Set base price and base price including tax
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @param Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.EmptyCatchBlock)
     */
    public function execute(Observer $observer)
    {
        $quoteItem = $observer->getEvent()->getQuoteItem();

        if (!$quoteItem instanceof Item) {
            return;
        }

        try {
            $product = $quoteItem->getProduct();
        } catch (\Throwable $e) {
            return;
        }

        if (!$product) {
            return;
        }

        $basePrice = $quoteItem->getBasePrice();
        $price = $quoteItem->getPrice();

        if (($basePrice !== null && $basePrice > 0) || ($price !== null && $price > 0)) {
            return;
        }

        if ($basePrice === null || $basePrice <= 0) {
            try {
                $basePrice = $quoteItem->getProduct()
                    ->getPriceInfo()
                    ->getPrice('base_price')
                    ->getValue();
                $quoteItem->setBasePrice($basePrice);
                // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedCatch
            } catch (\Exception $e) {
            }
        }
    }
}
