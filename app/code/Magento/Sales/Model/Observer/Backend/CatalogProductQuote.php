<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Sales\Model\Observer\Backend;

class CatalogProductQuote
{
    /**
     * @var \Magento\Sales\Model\Resource\Quote
     */
    protected $_quote;

    /**
     * @param \Magento\Sales\Model\Resource\Quote $quote
     */
    public function __construct(\Magento\Sales\Model\Resource\Quote $quote)
    {
        $this->_quote = $quote;
    }

    /**
     * Mark recollect contain product(s) quotes
     *
     * @param int $productId
     * @param int $status
     */
    protected function _recollectQuotes($productId, $status)
    {
        if ($status != \Magento\Catalog\Model\Product\Status::STATUS_ENABLED) {
            $this->_quote->markQuotesRecollect($productId);
        }
    }

    /**
     * Catalog Product After Save (change status process)
     *
     * @param \Magento\Event\Observer $observer
     */
    public function catalogProductSaveAfter(\Magento\Event\Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        $this->_recollectQuotes($product->getId(), $product->getStatus());
    }

    /**
     * Catalog Mass Status update process
     *
     * @param \Magento\Event\Observer $observer
     */
    public function catalogProductStatusUpdate(\Magento\Event\Observer $observer)
    {
        $status = $observer->getEvent()->getStatus();
        $productId  = $observer->getEvent()->getProductId();
        $this->_recollectQuotes($productId, $status);
    }

    /**
     * When deleting product, subtract it from all quotes quantities
     *
     * @param \Magento\Event\Observer
     */
    public function subtractQtyFromQuotes($observer)
    {
        $product = $observer->getEvent()->getProduct();
        $this->_quote->substractProductFromQuotes($product);
    }
}
