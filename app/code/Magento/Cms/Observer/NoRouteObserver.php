<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class \Magento\Cms\Observer\NoRouteObserver
 *
 * @since 2.0.0
 */
class NoRouteObserver implements ObserverInterface
{
    /**
     * Modify No Route Forward object
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return self
     * @since 2.0.0
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
