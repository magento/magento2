<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * @api
 */
class EngineResolver
{
    /**
     * MySQL search engine
     */
    const CATALOG_SEARCH_MYSQL_ENGINE = 'mysql';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Path to catalog search engine
     * @var string
     */
    protected $path;

    /**
     * Scope type
     * @var string
     */
    protected $scopeType;

    /**
     * Scope code
     * @var null|string
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
