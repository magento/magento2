<?php
/**
 * Event invoker interface
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Event;

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
