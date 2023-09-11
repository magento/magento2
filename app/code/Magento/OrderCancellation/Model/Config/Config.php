<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\OrderCancellation\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface as StoreScopeInterface;

/**
 * Config Model for order cancellation module
 */
class Config
{
    private const SETTING_ENABLED = '1';

    private const SETTING_ENABLE_PATH = 'sales/cancellation/enabled';

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Is order cancellation enabled for requested store
     *
     * @param int $storeId
     * @return bool
     */
    public function isOrderCancellationEnabledForStore(int $storeId): bool
    {
        return $this->scopeConfig->getValue(
            self::SETTING_ENABLE_PATH,
            StoreScopeInterface::SCOPE_STORE,
            $storeId
        ) === self::SETTING_ENABLED;
    }
}
