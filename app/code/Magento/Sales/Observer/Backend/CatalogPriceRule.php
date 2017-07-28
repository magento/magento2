<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Observer\Backend;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class \Magento\Sales\Observer\Backend\CatalogPriceRule
 *
 * @since 2.0.0
 */
class CatalogPriceRule implements ObserverInterface
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
     * When applying a catalog price rule, make related quotes recollect on demand
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->_quote->markQuotesRecollectOnCatalogRules();
    }
}
