<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Observer;

use Magento\Framework\Event\Observer as EventObserver;

/**
 * Catalog inventory module observer
 */
class CheckoutAllSubmitAfterObserver
{
    /**
     * @var SubtractQuoteInventoryObserver
     */
    protected $subtractQuoteInventoryObserver;

    /**
     * @var ReindexQuoteInventoryObserver
     */
    protected $reindexQuoteInventoryObserver;

    /**
     * @param SubtractQuoteInventoryObserver $subtractQuoteInventoryObserver
     * @param ReindexQuoteInventoryObserver $reindexQuoteInventoryObserver
     */
    public function __construct(
        SubtractQuoteInventoryObserver $subtractQuoteInventoryObserver,
        ReindexQuoteInventoryObserver $reindexQuoteInventoryObserver
    ) {
        $this->subtractQuoteInventoryObserver = $subtractQuoteInventoryObserver;
        $this->reindexQuoteInventoryObserver = $reindexQuoteInventoryObserver;
    }

    /**
     * Subtract qtys of quote item products after multishipping checkout
     *
     * @param EventObserver $observer
     * @return $this
     */
    public function invoke(EventObserver $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        if (!$quote->getInventoryProcessed()) {
            $this->subtractQuoteInventoryObserver->invoke($observer);
            $this->reindexQuoteInventoryObserver->invoke($observer);
        }
        return $this;
    }
}
