<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Reports Event observer model
 * @since 2.0.0
 */
class CheckoutCartAddProductObserver implements ObserverInterface
{
    /**
     * @var EventSaver
     * @since 2.0.0
     */
    protected $eventSaver;

    /**
     * @param EventSaver $eventSaver
     * @since 2.0.0
     */
    public function __construct(
        EventSaver $eventSaver
    ) {
        $this->eventSaver = $eventSaver;
    }

    /**
     * Add product to shopping cart action
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     * @since 2.0.0
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quoteItem = $observer->getEvent()->getItem();
        if (!$quoteItem->getId() && !$quoteItem->getParentItem()) {
            $productId = $quoteItem->getProductId();
            $this->eventSaver->save(\Magento\Reports\Model\Event::EVENT_PRODUCT_TO_CART, $productId);
        }

        return $this;
    }
}
