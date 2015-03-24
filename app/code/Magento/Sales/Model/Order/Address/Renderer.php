<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Order\Address;

use Magento\Customer\Model\Address\Config as AddressConfig;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Sales\Model\Order\Address;
/**
 * Class Renderer
 */
class Renderer
{
    /**
     * @var AddressConfig
     */
    protected $addressConfig;

    /**
     * @var EventManager
     */
    protected $eventManager;

    /**
     * @param AddressConfig $addressConfig
     * @param EventManager $eventManager
     */
    public function __construct(
        AddressConfig $addressConfig,
        EventManager $eventManager
    ) {
        $this->addressConfig = $addressConfig;
        $this->eventManager = $eventManager;
    }

    /**
     * Format address in a specific way
     *
     * @param string $type
     * @return string|null
     */
    public function format(Address $address, $type)
    {
        if (!($formatType = $this->addressConfig->getFormatByCode($type)) || !$formatType->getRenderer()) {
            return null;
        }
        $this->eventManager->dispatch('customer_address_format', ['type' => $formatType, 'address' => $address]);
        return $formatType->getRenderer()->render($address);
    }
}