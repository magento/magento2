<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Console\Command;

use Magento\Framework\ObjectManagerInterface;
use Symfony\Component\Console\Command\Command;
use Magento\Indexer\Model\IndexerInterface;
use Magento\Store\Model\StoreManager;
use Magento\Framework\App\ObjectManagerFactory;

/**
 * An Abstract class for Indexer related commands.
 */
abstract class AbstractIndexerCommand extends Command
{
    /**
     * @var ObjectManagerFactory
     */
    private $objectManagerFactory;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Constructor
     * @param ObjectManagerFactory $objectManagerFactory
     */
    public function __construct(ObjectManagerFactory $objectManagerFactory)
    {
        $this->objectManagerFactory = $objectManagerFactory;
        parent::__construct();
    }

    /**
     * Returns all indexers
     *
     * @return IndexerInterface[]
     */
    protected function getAllIndexers()
    {
        $collectionFactory = $this->getObjectManager()->create('Magento\Indexer\Model\Indexer\CollectionFactory');
        return $collectionFactory->create()->getItems();
    }

    /**
     * Gets initialized object manager
     *
     * @return ObjectManagerInterface
     */
    protected function getObjectManager()
    {
        if (null == $this->objectManager) {
            $params = $_SERVER;
            $params[StoreManager::PARAM_RUN_CODE] = 'admin';
            $params[StoreManager::PARAM_RUN_TYPE] = 'store';
            $this->objectManager = $this->objectManagerFactory->create($params);
            /** @var \Magento\Framework\App\State $appState */
            $appState = $this->objectManager->get('Magento\Framework\App\State');
            $appState->setAreaCode('adminmhtml');
        }
        return $this->objectManager;
    }
}
