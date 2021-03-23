<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Order\Address;

use Magento\Customer\Model\Address\Config as AddressConfig;
use Magento\Directory\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
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
    private $scopeConfig;

    /**
     * Constructor
     *
     * @param AddressConfig $addressConfig
     * @param EventManager $eventManager
     * @param ScopeConfigInterface|null $scopeConfig
     */
    public function __construct(
        AddressConfig $addressConfig,
        EventManager $eventManager,
        ?ScopeConfigInterface $scopeConfig = null
    ) {
        $this->addressConfig = $addressConfig;
        $this->eventManager = $eventManager;
        $this->scopeConfig = $scopeConfig ?: ObjectManager::getInstance()->get(ScopeConfigInterface::class);
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
        $storeId = $address->getOrder()->getStoreId();
        $this->addressConfig->setStore($storeId);
        $formatType = $this->addressConfig->getFormatByCode($type);
        if (!$formatType || !$formatType->getRenderer()) {
            return null;
        }
        $this->eventManager->dispatch('customer_address_format', ['type' => $formatType, 'address' => $address]);
        $addressData = $address->getData();
        $addressData['locale'] = $this->getLocaleByStoreId((int) $storeId);

        return $formatType->getRenderer()->renderArray($addressData);
    }

    /**
     * Returns locale by storeId
     *
     * @param int $storeId
     * @return string
     */
    private function getLocaleByStoreId(int $storeId): string
    {
        return $this->scopeConfig->getValue(Data::XML_PATH_DEFAULT_LOCALE, ScopeInterface::SCOPE_STORE, $storeId);
    }
}
