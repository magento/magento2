<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\GraphQl\Query\Resolver\Argument\Validator\IOLimit;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Provides configuration related to the GraphQL input limit validation
 */
class IOLimitConfigProvider
{
    /**
     * Path to the configuration setting for if the input limiting is enabled
     */
    public const CONFIG_PATH_INPUT_LIMIT_ENABLED = 'graphql/validation/input_limit_enabled';

    /**
     * Path to the configuration setting for maximum page size allowed
     */
    public const CONFIG_PATH_MAXIMUM_PAGE_SIZE = 'graphql/validation/maximum_page_size';

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
     * Get the stored configuration for if the input limiting is enabled
     */
    public function isInputLimitingEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::CONFIG_PATH_INPUT_LIMIT_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get the stored configuration for the maximum page size
     */
    public function getMaximumPageSize(): ?int
    {
        $value = $this->scopeConfig->getValue(
            self::CONFIG_PATH_MAXIMUM_PAGE_SIZE,
            ScopeInterface::SCOPE_STORE
        );
        return isset($value) ? (int)$value : null;
    }
}
