<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * @api
 * @since 100.1.0
 */
class EngineResolver
{
    /**
     * MySQL search engine
     */
    const CATALOG_SEARCH_MYSQL_ENGINE = 'mysql';

    /**
     * @var ScopeConfigInterface
     * @since 100.1.0
     */
    protected $scopeConfig;

    /**
     * Path to catalog search engine
     * @var string
     * @since 100.1.0
     */
    protected $path;

    /**
     * Scope type
     * @var string
     * @since 100.1.0
     */
    protected $scopeType;

    /**
     * Scope code
     * @var null|string
     * @since 100.1.0
     */
    protected $scopeCode;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param string $path
     * @param string $scopeType
     * @param string $scopeCode
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        $path,
        $scopeType,
        $scopeCode = null
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->path = $path;
        $this->scopeType = $scopeType;
        $this->scopeCode = $scopeCode;
    }

    /**
     * Current Search Engine
     * @return string
     * @since 100.1.0
     */
    public function getCurrentSearchEngine()
    {
        return $this->scopeConfig->getValue(
            $this->path,
            $this->scopeType,
            $this->scopeCode
        );
    }
}
