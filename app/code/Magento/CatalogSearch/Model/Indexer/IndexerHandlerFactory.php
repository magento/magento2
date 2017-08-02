<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Indexer;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Indexer\SaveHandler\IndexerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * @api
 * @since 2.0.0
 */
class IndexerHandlerFactory
{
    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     * @since 2.0.0
     */
    protected $_objectManager = null;

    /**
     * Instance name to create
     *
     * @var string
     * @since 2.0.0
     */
    protected $handlers = null;

    /**
     * @var ScopeConfigInterface
     * @since 2.0.0
     */
    private $scopeConfig;

    /**
     * Configuration path by which current indexer handler stored
     *
     * @var string
     * @since 2.0.0
     */
    private $configPath;

    /**
     * Factory constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @param ScopeConfigInterface $scopeConfig
     * @param string $configPath
     * @param string[] $handlers
     * @since 2.0.0
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ScopeConfigInterface $scopeConfig,
        $configPath,
        array $handlers = []
    ) {
        $this->_objectManager = $objectManager;
        $this->scopeConfig = $scopeConfig;
        $this->configPath = $configPath;
        $this->handlers = $handlers;
    }

    /**
     * Create indexer handler
     *
     * @param array $data
     * @return IndexerInterface
     * @since 2.0.0
     */
    public function create(array $data = [])
    {
        $currentHandler = $this->scopeConfig->getValue($this->configPath, ScopeInterface::SCOPE_STORE);
        if (!isset($this->handlers[$currentHandler])) {
            throw new \LogicException(
                'There is no such indexer handler: ' . $currentHandler
            );
        }
        $indexer = $this->_objectManager->create($this->handlers[$currentHandler], $data);

        if (!$indexer instanceof IndexerInterface) {
            throw new \InvalidArgumentException(
                $currentHandler . ' indexer handler doesn\'t implement ' . IndexerInterface::class
            );
        }

        if ($indexer && !$indexer->isAvailable()) {
            throw new \LogicException(
                'Indexer handler is not available: ' . $currentHandler
            );
        }
        return $indexer;
    }
}
