<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Console\Command;

use Symfony\Component\Console\Command\Command;
use Magento\Indexer\Model\IndexerFactory;
use Magento\Indexer\Model\Indexer\CollectionFactory;
use Magento\Indexer\Model\IndexerInterface;
use Magento\Indexer\Model\Indexer;
use Magento\Store\Model\StoreManager;
use Magento\Framework\App\ObjectManagerFactory;

/**
 * An Abstract class for Indexer related commands.
 */
abstract class AbstractIndexerCommand extends Command
{
    /**#@+
     * Names of input arguments or options
     */
    const INPUT_KEY_ALL = 'all';
    const INPUT_KEY_INDEXERS = 'index';
    /**#@- */

    /**
     * Collection of Indexers factory
     *
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * Indexer factory
     *
     * @var IndexerFactory
     */
    protected $indexerFactory;

    /**
     * Constructor
     * @param ObjectManagerFactory $objectManagerFactory
     */
    public function __construct(ObjectManagerFactory $objectManagerFactory)
    {
        $params = $_SERVER;
        $params[StoreManager::PARAM_RUN_CODE] = 'admin';
        $params[StoreManager::PARAM_RUN_TYPE] = 'store';
        $objectManager = $objectManagerFactory->create($params);


        /** @var \Magento\Framework\App\State $appState */
        $appState = $objectManager->get('Magento\Framework\App\State');
        $appState->setAreaCode('adminmhtml'); //TODO: temporary fix.
        $this->collectionFactory = $objectManager->create('Magento\Indexer\Model\Indexer\CollectionFactory');
        $this->indexerFactory = $objectManager->create('Magento\Indexer\Model\IndexerFactory');
        parent::__construct();
    }

    /**
     * Returns all indexers
     *
     * @return IndexerInterface[]
     */
    protected function getAllIndexers()
    {
        /** @var Indexer[] $indexers */
        return $this->collectionFactory->create()->getItems();
    }
}
