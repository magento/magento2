<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
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
