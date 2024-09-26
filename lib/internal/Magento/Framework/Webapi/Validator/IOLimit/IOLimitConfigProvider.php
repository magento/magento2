<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Webapi\Validator\IOLimit;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Provides configuration related to the WebApi input limit validation
 */
class IOLimitConfigProvider
{
    /**
     * Path to the configuration setting for if the input limiting is enabled
     */
    public const CONFIG_PATH_INPUT_LIMIT_ENABLED = 'webapi/validation/input_limit_enabled';

    /**
     * Path to the configuration setting for maximum complex array items
     */
    public const CONFIG_PATH_COMPLEX_ARRAY_ITEM = 'webapi/validation/complex_array_limit';

    /**
     * Path to the configuration setting for maximum page size allowed
     */
    public const CONFIG_PATH_MAXIMUM_PAGE_SIZE = 'webapi/validation/maximum_page_size';

    /**
     * Path to the configuration setting for default page size
     */
    public const CONFIG_PATH_DEFAULT_PAGE_SIZE = 'webapi/validation/default_page_size';

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
     * @inheritDoc
     */
    public function isInputLimitingEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::CONFIG_PATH_INPUT_LIMIT_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get the stored configuration for the maximum number of items allowed in an array property of an entity
     */
    public function getComplexArrayItemLimit(): ?int
    {
        $value = $this->scopeConfig->getValue(
            self::CONFIG_PATH_COMPLEX_ARRAY_ITEM,
            ScopeInterface::SCOPE_STORE
        );
        return isset($value) ? (int)$value : null;
    }

    /**
     * Get the stored configuration for the maxmimum page size
     */
    public function getMaximumPageSize(): ?int
    {
        $value = $this->scopeConfig->getValue(
            self::CONFIG_PATH_MAXIMUM_PAGE_SIZE,
            ScopeInterface::SCOPE_STORE
        );
        return isset($value) ? (int)$value : null;
    }

    /**
     * Get the stored configuration for the default page size
     */
    public function getDefaultPageSize(): ?int
    {
        $value = $this->scopeConfig->getValue(
            self::CONFIG_PATH_DEFAULT_PAGE_SIZE,
            ScopeInterface::SCOPE_STORE
        );
        return isset($value) ? (int)$value : null;
    }
}
