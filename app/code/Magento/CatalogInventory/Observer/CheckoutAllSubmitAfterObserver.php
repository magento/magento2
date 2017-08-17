<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

/**
 * Class \Magento\CatalogInventory\Observer\CheckoutAllSubmitAfterObserver
 *
 */
class CheckoutAllSubmitAfterObserver implements ObserverInterface
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
    public function execute(EventObserver $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        if (!$quote->getInventoryProcessed()) {
            $this->subtractQuoteInventoryObserver->execute($observer);
            $this->reindexQuoteInventoryObserver->execute($observer);
        }
        return $this;
    }
}
