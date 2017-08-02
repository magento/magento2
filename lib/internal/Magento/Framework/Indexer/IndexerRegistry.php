<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Indexer;

/**
 * @api Retrieve indexer by id, for example when indexer need to be invalidated
 * @since 2.0.0
 */
class IndexerRegistry
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $objectManager;

    /**
     * @var IndexerInterface[]
     * @since 2.0.0
     */
    protected $indexers = [];

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function get($indexerId)
    {
        if (!isset($this->indexers[$indexerId])) {
            $this->indexers[$indexerId] = $this->objectManager->create(
                \Magento\Framework\Indexer\IndexerInterface::class
            )->load($indexerId);
        }
        return $this->indexers[$indexerId];
    }
}
