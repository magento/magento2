<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Event regex observer object
 */
namespace Magento\Framework\Event\Observer;

class Regex extends \Magento\Framework\Event\Observer
{
    /**
     * Checkes the observer's event_regex against event's name
     *
     * @param \Magento\Framework\Event $event
     * @return boolean
     */
    public function isValidFor(\Magento\Framework\Event $event)
    {
        return $event->getName() !== null ? preg_match($this->getEventRegex(), $event->getName()) : false;
    }
}
