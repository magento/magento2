<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Shipping\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class CarrierStatus
{
    private const CONFIG_FIELD = 'active';

    private ScopeConfigInterface $scopeConfig;

    private string $configField;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        string $configField = self::CONFIG_FIELD
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->configField = $configField;
    }

    /**
     * @param null|string|int $store
     */
    public function isEnabled(string $carrierCode, $store = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            'carriers/' . $carrierCode . '/' . $this->configField,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }
}
