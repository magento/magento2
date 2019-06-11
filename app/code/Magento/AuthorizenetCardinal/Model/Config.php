<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AuthorizenetCardinal\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * AuthorizenetCardinal integration configuration.
 *
 * Class is a proxy service for retrieving configuration settings.
 */
class Config
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * If this config option set to false no AuthorizenetCardinal integration should be available
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isActive(?int $storeId = null): bool
    {
        $enabled = $this->scopeConfig->isSetFlag(
            'three_d_secure/cardinal/enabled_authorizenet',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        return $enabled;
    }
}
