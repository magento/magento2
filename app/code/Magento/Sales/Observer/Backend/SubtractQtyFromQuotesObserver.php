<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Observer\Backend;

use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\ResourceModel\Quote;
use Magento\Framework\Event\Observer;

class SubtractQtyFromQuotesObserver implements ObserverInterface
{
    /**
     * @var Quote
     */
    protected $_quote;

    /**
     * @param Quote $quote
     */
    public function __construct(Quote $quote)
    {
        $this->_quote = $quote;
    }

    /**
     * When deleting product, subtract it from all quotes quantities
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        $this->_quote->subtractProductFromQuotes($product);
        $this->_quote->markQuotesRecollect([$product->getId()]);
    }
}
