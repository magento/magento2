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
 * @since 2.0.0
 */
interface ObserverInterface
{
    /**
     * @param Observer $observer
     * @return void
     * @since 2.0.0
     */
    public function execute(Observer $observer);
}
