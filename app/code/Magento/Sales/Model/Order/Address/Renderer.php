<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Order\Address;

use Magento\Customer\Model\Address\Config as AddressConfig;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Sales\Model\Order\Address;

/**
 * Class Renderer used for formatting an order address
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
     * Constructor
     *
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
     * @param Address $address
     * @param string $type
     * @return string|null
     */
    public function format(Address $address, $type)
    {
        $this->addressConfig->setStore($address->getOrder()->getStoreId());
        $formatType = $this->addressConfig->getFormatByCode($type);
        if (!$formatType || !$formatType->getRenderer()) {
            return null;
        }
        $this->eventManager->dispatch('customer_address_format', ['type' => $formatType, 'address' => $address]);
        return $formatType->getRenderer()->renderArray($address->getData());
    }
}
