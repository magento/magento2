<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Event;

/**
 * Interface \Magento\Framework\Event\ManagerInterface
 *
 * @since 2.0.0
 */
interface ManagerInterface
{
    /**
     * Dispatch event
     *
     * Calls all observer callbacks registered for this event
     * and multiple observers matching event name pattern
     *
     * @param string $eventName
     * @param array $data
     * @return void
     * @since 2.0.0
     */
    public function dispatch($eventName, array $data = []);
}
