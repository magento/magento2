<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Sales\Model\Observer\Backend;

class CatalogPriceRule
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
     * @return void
     */
    public function dispatch()
    {
        $this->_quote->markQuotesRecollectOnCatalogRules();
    }
}
