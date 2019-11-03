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
     * Available engines
     * @var array
     */
    private $engines = [];

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param array $engines
     * @param LoggerInterface $logger
     * @param string $path
     * @param string $scopeType
     * @param string $scopeCode
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        array $engines,
        LoggerInterface $logger,
        $path,
        $scopeType,
        $scopeCode = null
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->path = $path;
        $this->scopeType = $scopeType;
        $this->scopeCode = $scopeCode;
        $this->engines = $engines;
        $this->logger = $logger;
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
            $this->logger->error(
                $engine . ' search engine doesn\'t exists. Falling back to ' . self::CATALOG_SEARCH_MYSQL_ENGINE
            );
            return self::CATALOG_SEARCH_MYSQL_ENGINE;
        }
    }
}
