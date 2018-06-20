<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InstantPurchase\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Instant purchase configuration.
 */
class Config
{
    const ACTIVE = 'sales/instant_purchase/active';
    const BUTTON_TEXT = 'sales/instant_purchase/button_text';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Data constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Defines is feature enabled.
     *
     * @param int $storeId
     * @return bool
     */
    public function isModuleEnabled(int $storeId): bool
    {
        return $this->isSetFlag(self::ACTIVE, $storeId);
    }

    /**
     * Defines instant purchase trigger button title on product page.
     *
     * @param int $storeId
     * @return string
     */
    public function getButtonText(int $storeId): string
    {
        return $this->getValue(self::BUTTON_TEXT, $storeId);
    }

    /**
     * Fetches value from generic config.
     *
     * @param string $path
     * @param int $storeId
     * @return mixed
     */
    private function getValue(string $path, int $storeId)
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Fetches switcher value from generic config.
     *
     * @param string $path
     * @param int $storeId
     * @return bool
     */
    private function isSetFlag(string $path, int $storeId): bool
    {
        return $this->scopeConfig->isSetFlag(
            $path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
