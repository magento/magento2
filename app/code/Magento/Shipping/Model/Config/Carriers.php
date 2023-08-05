<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Shipping\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Carriers
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Returns carriers config by store
     *
     * @param null|string|int $storeId
     */
    public function getConfig($storeId = null): array
    {
        return $this->scopeConfig->getValue('carriers', ScopeInterface::SCOPE_STORE, $storeId) ?: [];
    }
}
