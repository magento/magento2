<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Observer\Backend;

use Magento\Framework\Event\ObserverInterface;

class SubtractQtyFromQuotesObserver implements ObserverInterface
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
     * When deleting product, subtract it from all quotes quantities
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        $this->_quote->substractProductFromQuotes($product);
    }
}
