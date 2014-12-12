<?php
/**
 * Event configuration model interface
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Event;

interface ConfigInterface
{
    /**
     * Get observers by event name
     *
     * @param string $eventName
     * @return array
     */
    public function getObservers($eventName);
}
