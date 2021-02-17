<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Order\Address;

use Magento\Customer\Model\Address\Config as AddressConfig;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Sales\Model\Order\Address;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Renderer used for formatting an order address
 * @api
 * @since 100.0.2
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
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Constructor
     *
     * @param AddressConfig $addressConfig
     * @param EventManager $eventManager
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        AddressConfig $addressConfig,
        EventManager $eventManager,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->addressConfig = $addressConfig;
        $this->eventManager = $eventManager;
        $this->scopeConfig = $scopeConfig;
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
        $locale = $this->scopeConfig->getValue(
            DirectoryHelper::XML_PATH_DEFAULT_LOCALE,
            ScopeInterface::SCOPE_STORES,
            $address->getOrder()->getStoreId()
        );
        return $formatType->getRenderer()->renderArray($address->getData(), null, $locale);
    }
}
