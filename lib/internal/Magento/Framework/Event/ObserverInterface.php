<?php
/**
 * Observer interface
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Event;

/**
 * Interface \Magento\Framework\Event\ObserverInterface
 *
 * @api
 * @since 100.0.2
 */
interface ObserverInterface
{
    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer);
}
