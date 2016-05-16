<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Observer;

use Magento\Framework\Event\ObserverInterface;

class NoRouteObserver implements ObserverInterface
{
    /**
     * Modify No Route Forward object
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return self
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $observer->getEvent()->getStatus()->setLoaded(
            true
        )->setForwardModule(
            'cms'
        )->setForwardController(
            'index'
        )->setForwardAction(
            'noroute'
        );

        return $this;
    }
}
