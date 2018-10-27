<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Indexer;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Proxy for adapter-specific index switcher
 */
class IndexSwitcherProxy implements IndexSwitcherInterface
{
    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     */
    private $objectManager = null;

    /**
     * Instance name to create
     *
     * @var string
     */
    private $handlers;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Configuration path by which current indexer handler stored
     *
     * @var string
     */
    private $configPath;

    /**
     * Factory constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @param ScopeConfigInterface $scopeConfig
     * @param string $configPath
     * @param string[] $handlers
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ScopeConfigInterface $scopeConfig,
        $configPath,
        array $handlers = []
    ) {
        $this->objectManager = $objectManager;
        $this->scopeConfig = $scopeConfig;
        $this->configPath = $configPath;
        $this->handlers = $handlers;
    }

    /**
     * {@inheritDoc}
     *
     * As index switcher is an optional part of the search SPI, it may be not defined by a search engine.
     * It is especially reasonable for search engines with pre-defined indexes declaration (like old SOLR and Sphinx)
     * which cannot create temporary indexes on the fly.
     * That's the reason why this method do nothing for the case
     * when switcher is not defined for a specific search engine.
     */
    public function switchIndex(array $dimensions)
    {
        $currentHandler = $this->scopeConfig->getValue($this->configPath, ScopeInterface::SCOPE_STORE);
        if (!isset($this->handlers[$currentHandler])) {
            return;
        }
        $this->create($currentHandler)->switchIndex($dimensions);
    }

    /**
     * Create indexer handler
     *
     * @param string $handler
     * @return IndexSwitcherInterface
     */
    private function create($handler)
    {
        $indexSwitcher = $this->objectManager->create($this->handlers[$handler]);

        if (!$indexSwitcher instanceof IndexSwitcherInterface) {
            throw new \InvalidArgumentException(
                $handler . ' index switcher doesn\'t implement ' . IndexSwitcherInterface::class
            );
        }

        return $indexSwitcher;
    }
}
