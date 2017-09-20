<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OneTouchOrdering\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;

class Config
{
    const ONE_TOUCH_ORDERING_MODULE_ACTIVE = 'sales/one_touch/active';
    const ONE_TOUCH_ORDERING_MODULE_BUTTON_TEXT = 'sales/one_touch/button_text';
    const ONE_TOUCH_ORDERING_MODULE_ADDRESS_SELECT = 'sales/one_touch/address_select';

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Data constructor.
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return bool
     */
    public function isModuleEnabled(): bool
    {
        return $this->isSetFlag(self::ONE_TOUCH_ORDERING_MODULE_ACTIVE);
    }

    /**
     * @return bool
     */
    public function isSelectAddressEnabled(): string
    {
        return $this->isSetFlag(self::ONE_TOUCH_ORDERING_MODULE_ADDRESS_SELECT);
    }

    /**
     * @return string
     */
    public function getButtonText(): string
    {
        return $this->getValue(self::ONE_TOUCH_ORDERING_MODULE_BUTTON_TEXT);
    }

    /**
     * @param $path
     * @return mixed
     */
    private function getValue($path)
    {
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
        );
    }

    /**
     * @param $path
     * @return bool
     */
    private function isSetFlag($path)
    {
        return $this->scopeConfig->isSetFlag(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
        );
    }
}
