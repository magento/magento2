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
 * @since 2.0.0
 */
interface InvokerInterface
{
    /**
     * Dispatch event
     *
     * @param array $configuration
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @since 2.0.0
     */
    public function dispatch(array $configuration, \Magento\Framework\Event\Observer $observer);
}
