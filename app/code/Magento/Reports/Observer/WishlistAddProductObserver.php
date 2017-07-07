<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Reports Event observer model
 */
class WishlistAddProductObserver implements ObserverInterface
{
    /**
     * @var EventSaver
     */
    protected $eventSaver;

    /**
     * @param EventSaver $eventSaver
     */
    public function __construct(
        EventSaver $eventSaver
    ) {
        $this->eventSaver = $eventSaver;
    }

    /**
     * Add product to wishlist action
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->eventSaver->save(
            \Magento\Reports\Model\Event::EVENT_PRODUCT_TO_WISHLIST,
            $observer->getEvent()->getProduct()->getId()
        );
    }
}
