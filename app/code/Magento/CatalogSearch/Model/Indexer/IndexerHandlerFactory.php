<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Indexer;

use Magento\CatalogSearch\Model\Resource\EngineProvider;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Resource;
use Magento\Framework\IndexerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\ScopeInterface;

class IndexerHandlerFactory
{
    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     */
    protected $_objectManager = null;

    /**
     * Instance name to create
     *
     * @var string
     */
    protected $handlers = null;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Factory constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @param ScopeConfigInterface $scopeConfig
     * @param string[] $handlers
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ScopeConfigInterface $scopeConfig,
        array $handlers = []
    ) {
        $this->_objectManager = $objectManager;
        $this->handlers = $handlers;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return IndexerInterface
     */
    public function create(array $data = array())
    {
        $currentEngine = $this->scopeConfig->getValue(EngineProvider::CONFIG_ENGINE_PATH, ScopeInterface::SCOPE_STORE);
        $object = $this->_objectManager->create($this->handlers[$currentEngine], $data);

        if (!$object instanceof IndexerInterface) {
            throw new \InvalidArgumentException($object . ' doesn\'t implement ' . IndexerInterface::class);
        }

        return $object;
    }
}
