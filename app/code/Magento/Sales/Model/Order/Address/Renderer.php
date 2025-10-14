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
     * @var StoreManagerInterface|null
     */
    private $storeManager;

    /**
     * @param AddressConfig $addressConfig
     * @param EventManager $eventManager
     * @param ScopeConfigInterface|null $scopeConfig
     * @param StoreManagerInterface|null $storeManager
     */
    public function __construct(
        AddressConfig $addressConfig,
        EventManager $eventManager,
        ?ScopeConfigInterface $scopeConfig = null,
        ?StoreManagerInterface $storeManager = null
    ) {
        $this->addressConfig = $addressConfig;
        $this->eventManager = $eventManager;
        $this->scopeConfig = $scopeConfig ?: ObjectManager::getInstance()->get(ScopeConfigInterface::class);
        $this->storeManager = $storeManager ?: ObjectManager::getInstance()->get(StoreManagerInterface::class);
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
        $orderStore = $address->getOrder()->getStore();
        $originalStore = $this->storeManager->getStore();
        $this->storeManager->setCurrentStore($orderStore);
        $this->addressConfig->setStore($orderStore);
        $formatType = $this->addressConfig->getFormatByCode($type);
        if (!$formatType || !$formatType->getRenderer()) {
            return null;
        }
        $this->eventManager->dispatch('customer_address_format', ['type' => $formatType, 'address' => $address]);
        $addressData = $address->getData();
        $addressData['locale'] = $this->getLocaleByStoreId((int) $orderStore->getId());
        $rendered = $formatType->getRenderer()->renderArray($addressData);

        $this->addressConfig->setStore($originalStore);
        $this->storeManager->setCurrentStore($originalStore);

        return $rendered;
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
