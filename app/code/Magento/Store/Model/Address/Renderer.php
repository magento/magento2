<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Model\Address;

use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Object;

/**
 * Class Renderer used for formatting a store address
 */
class Renderer
{
    /**
     * @var EventManager
     */
    protected $eventManager;

    /**
     * Constructor
     *
     * @param EventManager $eventManager
     */
    public function __construct(EventManager $eventManager)
    {
        $this->eventManager = $eventManager;
    }

    /**
     * Format address in a specific way
     *
     * @param Object $address
     * @param string $type
     * @return null|string
     */
    public function format(Object $address, $type)
    {
        $this->eventManager->dispatch('store_address_format', ['type' => $type, 'address' => $address]);
        return implode('<br />', $address->getData()); // todo: @davidalger; implement actual formatting
    }
}
