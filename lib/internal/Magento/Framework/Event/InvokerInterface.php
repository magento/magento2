<?php
/**
 * Event invoker interface
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Event;

/**
 * Interface \Magento\Framework\Event\InvokerInterface
 *
 * @api
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
