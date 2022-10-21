<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Integration\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Represents configuration related to WebAPI Authorization
 */
class AuthorizationConfig
{
    /**
     * XML Path for Enable Integration as Bearer
     */
    private const CONFIG_PATH_INTEGRATION_BEARER = 'oauth/consumer/enable_integration_as_bearer';

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
     * Return if integration access tokens can be used as bearer tokens
     *
     * @return bool
     */
    public function isIntegrationAsBearerEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::CONFIG_PATH_INTEGRATION_BEARER,
            ScopeInterface::SCOPE_STORE
        );
    }
}
