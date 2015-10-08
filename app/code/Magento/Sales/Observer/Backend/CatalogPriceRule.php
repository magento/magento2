<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Observer\Backend;

use Magento\Framework\Event\ObserverInterface;

class CatalogPriceRule implements ObserverInterface
{
    /**
     * @var \Magento\Quote\Model\Resource\Quote
     */
    protected $_quote;

    /**
     * @param \Magento\Quote\Model\Resource\Quote $quote
     */
    public function __construct(\Magento\Quote\Model\Resource\Quote $quote)
    {
        $this->_quote = $quote;
    }

    /**
     * When applying a catalog price rule, make related quotes recollect on demand
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->_quote->markQuotesRecollectOnCatalogRules();
    }
}
