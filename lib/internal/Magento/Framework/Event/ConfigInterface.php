<?php
/**
 * Event configuration model interface
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Event;

interface ConfigInterface
{
    /**#@+
     * Event types
     */
    const TYPE_CORE = 'core';
    const TYPE_CUSTOM = 'custom';
    /**#@-*/

    /**
     * Get observers by event name
     *
     * @param string $eventName
     * @return array
     */
    public function getObservers($eventName);
}
