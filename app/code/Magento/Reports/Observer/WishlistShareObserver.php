<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Reports Event observer model
 */
class WishlistShareObserver implements ObserverInterface
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
     * Share customer wishlist action
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->eventSaver->save(
            \Magento\Reports\Model\Event::EVENT_WISHLIST_SHARE,
            $observer->getEvent()->getWishlist()->getId()
        );
    }
}
