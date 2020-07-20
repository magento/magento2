<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Sales\Observer\Backend;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\ResourceModel\Quote;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Quote\Model\Product\QuoteItemsCleanerInterface;

/**
 * Change status process and delete disabled product from quote
 */
class CatalogProductSaveAfterObserver implements ObserverInterface
{
    /**
     * @var Quote
     */
    private $quote;

    /**
     * @var QuoteItemsCleanerInterface
     */
    private $quoteItemsCleaner;

    /**
     * @param Quote $quote
     */
    public function __construct(Quote $quote, QuoteItemsCleanerInterface $quoteItemsCleaner)
    {
        $this->quote = $quote;
        $this->quoteItemsCleaner = $quoteItemsCleaner;
    }

    /**
     * Catalog Product After Save (change status process) and delete disabled product from quote
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        $product = $observer->getEvent()->getProduct();

        $isProductDisabled = (int) $product->getStatus() === Status::STATUS_DISABLED;
        if ($product->dataHasChangedFor(ProductInterface::STATUS) && $isProductDisabled) {
            $this->quote->subtractProductFromQuotes($product);
            $this->quoteItemsCleaner->execute($product);

            $this->quote->markQuotesRecollect($product->getId());
        }
    }
}
