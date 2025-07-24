<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Indexer;

use Magento\Framework\ObjectManager\ResetAfterRequestInterface;

/**
 * @api Retrieve indexer by id, for example when indexer need to be invalidated
 * @since 100.0.2
 */
class IndexerRegistry implements ResetAfterRequestInterface
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
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->indexers = [];
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
            $this->indexers[$indexerId] = $this->objectManager->create(
                \Magento\Framework\Indexer\IndexerInterface::class
            )->load($indexerId);
        }
        return $this->indexers[$indexerId];
    }
}
