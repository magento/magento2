<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Event regex observer object
 *
 * @author      Magento Core Team <core@magentocommerce.com>
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
        return preg_match($this->getEventRegex(), $event->getName());
    }
}
