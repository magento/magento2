<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Query;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class for fetching the maximum allowed alias count in graphql request
 */
class MaximumAliasConfiguration
{
    /**
     * Constant for maximum alias allowed value config path
     */
    private const CONFIG_PATH_MAXIMUM_ALIAS_ALLOWED = 'graphql/validation/maximum_alias_allowed';

    /**
     * Constant for maximum alias enable config path
     */
    private const CONFIG_PATH_MAXIMUM_ALIAS_ENABLED = 'graphql/validation/alias_limit_enabled';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfigInterface;

    /**
     * @param scopeConfigInterfacee $scopeConfigInterface
     */
    public function __construct(
        ScopeConfigInterface $scopeConfigInterface
    ) {
        $this->scopeConfigInterface = $scopeConfigInterface;
    }

    /**
     * Check the environment config to get the maximum allowed alias in graphql request.
     *
     * @return int
     */
    public function getMaximumAliasAllowed(): int
    {
        return (int) $this->scopeConfigInterface->getValue(self::CONFIG_PATH_MAXIMUM_ALIAS_ALLOWED);
    }

    /**
     * Check the environment config to check if maximum alias limit is enabled.
     *
     * @return bool
     */
    public function isMaximumAliasLimitEnabled(): bool
    {
        return (bool) $this->scopeConfigInterface->getValue(self::CONFIG_PATH_MAXIMUM_ALIAS_ENABLED);
    }
}
