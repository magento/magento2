<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Observer\Backend;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class \Magento\Sales\Observer\Backend\CatalogProductSaveAfterObserver
 *
 * @since 2.0.0
 */
class CatalogProductSaveAfterObserver implements ObserverInterface
{
    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote
     * @since 2.0.0
     */
    protected $_quote;

    /**
     * @param \Magento\Quote\Model\ResourceModel\Quote $quote
     * @since 2.0.0
     */
    public function __construct(\Magento\Quote\Model\ResourceModel\Quote $quote)
    {
        $this->_quote = $quote;
    }

    /**
     * Mark recollect contain product(s) quotes
     *
     * @param int $productId
     * @param int $status
     * @return void
     * @since 2.0.0
     */
    protected function _recollectQuotes($productId, $status)
    {
        if ($status != \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED) {
            $this->_quote->markQuotesRecollect($productId);
        }
    }

    /**
     * Catalog Product After Save (change status process)
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @since 2.0.0
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        $this->_recollectQuotes($product->getId(), $product->getStatus());
    }
}
