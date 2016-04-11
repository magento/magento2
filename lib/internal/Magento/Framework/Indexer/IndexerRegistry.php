<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Indexer;

class IndexerRegistry
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var IndexerInterface[]
     */
    protected $indexers = [];

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Retrieve indexer instance by id
     *
     * @param string $indexerId
     * @return IndexerInterface
     */
    public function get($indexerId)
    {
        if (!isset($this->indexers[$indexerId])) {
            $this->indexers[$indexerId] = $this->objectManager->create('Magento\Framework\Indexer\IndexerInterface')
                ->load($indexerId);
        }
        return $this->indexers[$indexerId];
    }
}
