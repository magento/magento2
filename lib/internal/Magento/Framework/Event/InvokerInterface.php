<?php
/**
 * Event invoker interface
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Event;

/**
 * Interface \Magento\Framework\Event\InvokerInterface
 *
 */
interface InvokerInterface
{
    /**
     * Dispatch event
     *
     * @param array $configuration
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function dispatch(array $configuration, \Magento\Framework\Event\Observer $observer);
}
