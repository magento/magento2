<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Search\EngineResolverInterface;
use Psr\Log\LoggerInterface;

/**
 * Search engine resolver model.
 *
 * @api
 * @since 100.1.0
 */
class EngineResolver implements EngineResolverInterface
{
    /**
     * MySQL search engine
     * @deprecated Use config.xml for default setting
     */
    const CATALOG_SEARCH_MYSQL_ENGINE = 'mysql';

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param array $engines Available engines
     * @param LoggerInterface $logger
     * @param string $path Path to catalog search engine
     * @param string $scopeType Scope type
     * @param string|null $scopeCode Scope code
     * @param string|null $defaultEngine
     */
    public function __construct(
        protected readonly ScopeConfigInterface $scopeConfig,
        private readonly array $engines,
        private readonly LoggerInterface $logger,
        protected $path,
        protected $scopeType,
        protected $scopeCode = null,
        private $defaultEngine = null
    ) {
    }

    /**
     * Returns Current Search Engine
     *
     * It returns string identifier of Search Engine that is currently chosen in configuration
     *
     * @return string
     * @since 100.1.0
     */
    public function getCurrentSearchEngine()
    {
        $engine = $this->scopeConfig->getValue(
            $this->path,
            $this->scopeType,
            $this->scopeCode
        );

        if (in_array($engine, $this->engines)) {
            return $engine;
        } else {
            //get default engine from default scope
            if ($this->defaultEngine && in_array($this->defaultEngine, $this->engines)) {
                $this->logger->error(
                    $engine . ' search engine doesn\'t exist. Falling back to ' . $this->defaultEngine
                );
            } else {
                $this->logger->error(
                    'Default search engine is not configured, fallback is not possible'
                );
            }
            return $this->defaultEngine;
        }
    }
}
