<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Observer\Backend;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class \Magento\Sales\Observer\Backend\SubtractQtyFromQuotesObserver
 *
 * @since 2.0.0
 */
class SubtractQtyFromQuotesObserver implements ObserverInterface
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
     * When deleting product, subtract it from all quotes quantities
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @since 2.0.0
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        $this->_quote->substractProductFromQuotes($product);
    }
}
