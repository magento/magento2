<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Query;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class for fetching the query length limit count in graphql request
 */
class QueryLengthConfiguration
{
    /**
     * Constant for query length limit allowed value config path
     */
    private const CONFIG_PATH_QUERY_LENGTH_LIMIT_ALLOWED = 'graphql/validation/query_length_limit_allowed';

    /**
     * Constant for query length limit enable config path
     */
    private const CONFIG_PATH_QUERY_LENGTH_LIMIT_ENABLED = 'graphql/validation/query_length_limit_enabled';

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
     * Check the environment config to get the query length limit in graphql request.
     *
     * @return int
     */
    public function getQueryLengthLimitAllowed(): int
    {
        return (int) $this->scopeConfigInterface->getValue(self::CONFIG_PATH_QUERY_LENGTH_LIMIT_ALLOWED);
    }

    /**
     * Check the environment config to check if query length limit is enabled.
     *
     * @return bool
     */
    public function isQueryLengthLimitEnabled(): bool
    {
        return (bool) $this->scopeConfigInterface->getValue(self::CONFIG_PATH_QUERY_LENGTH_LIMIT_ENABLED);
    }
}
