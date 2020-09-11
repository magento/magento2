<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CardinalCommerce\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * CardinalCommerce integration configuration.
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
     * Returns CardinalCommerce API Key used for authentication.
     *
     * A shared secret value between the merchant and Cardinal. This value should never be exposed to the public.
     *
     * @param int|null $storeId
     * @return string
     */
    public function getApiKey(?int $storeId = null): string
    {
        $apiKey = $this->scopeConfig->getValue(
            'three_d_secure/cardinal/api_key',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        return $apiKey;
    }

    /**
     * Returns CardinalCommerce API Identifier.
     *
     * GUID used to identify the specific API Key.
     *
     * @param int|null $storeId
     * @return string
     */
    public function getApiIdentifier(?int $storeId = null): string
    {
        $apiIdentifier = $this->scopeConfig->getValue(
            'three_d_secure/cardinal/api_identifier',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        return $apiIdentifier;
    }

    /**
     * Returns CardinalCommerce Org Unit Id.
     *
     * GUID to identify the merchant organization within Cardinal systems.
     *
     * @param int|null $storeId
     * @return string
     */
    public function getOrgUnitId(?int $storeId = null): string
    {
        $orgUnitId = $this->scopeConfig->getValue(
            'three_d_secure/cardinal/org_unit_id',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        return $orgUnitId;
    }

    /**
     * Returns CardinalCommerce environment.
     *
     * Sandbox or production.
     *
     * @param int|null $storeId
     * @return string
     */
    public function getEnvironment(?int $storeId = null): string
    {
        $environment = $this->scopeConfig->getValue(
            'three_d_secure/cardinal/environment',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        return $environment;
    }

    /**
     * If is "true" extra information about interaction with CardinalCommerce API are written to payment.log file
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isDebugModeEnabled(?int $storeId = null): bool
    {
        $debugModeEnabled = $this->scopeConfig->isSetFlag(
            'three_d_secure/cardinal/debug',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        return $debugModeEnabled;
    }
}
