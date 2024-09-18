<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\OrderCancellation\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface as StoreScopeInterface;
use Magento\Store\Model\Store;

/**
 * Config Model for order cancellation module
 */
class Config
{
    private const SETTING_ENABLED = '1';

    private const SALES_CANCELLATION_ENABLED = 'sales/cancellation/enabled';

    private const SALES_CANCELLATION_REASONS = 'sales/cancellation/reasons';

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Check if order cancellation is enabled for a given store.
     *
     * @param int $storeId
     * @return bool
     */
    public function isOrderCancellationEnabledForStore(int $storeId): bool
    {
        return $this->scopeConfig->getValue(
            self::SALES_CANCELLATION_ENABLED,
            StoreScopeInterface::SCOPE_STORE,
            $storeId
        ) === self::SETTING_ENABLED;
    }

    /**
     * Returns order cancellation reasons.
     *
     * @param Store $store
     * @return array
     */
    public function getCancellationReasons(Store $store): array
    {
        $reasons = $this->scopeConfig->getValue(
            self::SALES_CANCELLATION_REASONS,
            StoreScopeInterface::SCOPE_STORE,
            $store
        );
        return array_map(function ($reason) {
            return $reason['description'];
        }, is_array($reasons) ? $reasons : json_decode($reasons, true));
    }
}
