<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\Order\Address;

use Magento\Customer\Model\Address\Config as AddressConfig;
use Magento\Directory\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Sales\Model\Order\Address;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Renderer used for formatting an order address
 *
 * @api
 * @since 100.0.2
 */
class Renderer
{
    protected AddressConfig $addressConfig;

    protected EventManager $eventManager;

    private ScopeConfigInterface $scopeConfig;

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
     */
    public function format(Address $address, string $type): ?string
    {
        $orderStore = $address->getOrder()->getStore();
        $originalStore = $this->addressConfig->getStore();
        $this->addressConfig->setStore($orderStore);
        $formatType = $this->addressConfig->getFormatByCode($type);
        if (!$formatType || !$formatType->getRenderer()) {
            return null;
        }
        $this->eventManager->dispatch('customer_address_format', ['type' => $formatType, 'address' => $address]);
        $addressData = $address->getData();
        $addressData['locale'] = $this->getLocaleByStoreId((int) $orderStore->getId());

        $this->addressConfig->setStore($originalStore);

        return $formatType->getRenderer()->renderArray($addressData);
    }

    /**
     * Returns locale by storeId
     */
    private function getLocaleByStoreId(int $storeId): string
    {
        return $this->scopeConfig->getValue(Data::XML_PATH_DEFAULT_LOCALE, ScopeInterface::SCOPE_STORE, $storeId);
    }
}
