<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model\SalesQuoteItem;

use Magento\Framework\Event\Observer;
use Magento\CatalogInventory\Helper\Data;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\IsProductSalableForRequestedQtyInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;

/**
 * @inheritdoc
 */
class QuantityValidator implements QuantityValidatorInterface
{
    /**
     * @var IsProductSalableForRequestedQtyInterface
     */
    private $isProductSalableForRequestedQty;
    /**
     * @var StockResolverInterface
     */
    private $stockResolver;

    /**
     * @param IsProductSalableForRequestedQtyInterface $isProductSalableForRequestedQty
     * @param StockResolverInterface $stockResolver
     */
    public function __construct(
        IsProductSalableForRequestedQtyInterface $isProductSalableForRequestedQty,
        StockResolverInterface $stockResolver
    ) {
        $this->isProductSalableForRequestedQty = $isProductSalableForRequestedQty;
        $this->stockResolver = $stockResolver;
    }

    /**
     * @inheritdoc
     */
    public function validate(Observer $observer)
    {
        /* @var $quoteItem \Magento\Quote\Model\Quote\Item */
        $quoteItem = $observer->getEvent()->getItem();
        $websiteCode = $quoteItem->getQuote()->getStore()->getWebsite()->getCode();
        $stock = $this->stockResolver->get(SalesChannelInterface::TYPE_WEBSITE, $websiteCode);

        $isSalable = $this->isProductSalableForRequestedQty->execute(
            $quoteItem->getSku(),
            (int)$stock->getStockId(),
            $quoteItem->getQty()
        );
        if ($isSalable) {
            return;
        }
        $quoteItem->addErrorInfo(
            'cataloginventory',
            Data::ERROR_QTY,
            __('This product is out of stock.')
        );
    }
}
