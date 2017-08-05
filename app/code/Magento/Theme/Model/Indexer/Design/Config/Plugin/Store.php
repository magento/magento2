<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Indexer\Design\Config\Plugin;

use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Store\Model\Store as StoreStore;
use Magento\Theme\Model\Data\Design\Config;

/**
 * Class \Magento\Theme\Model\Indexer\Design\Config\Plugin\Store
 *
 */
class Store
{
    /**
     * @var IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * @param IndexerRegistry $indexerRegistry
     */
    public function __construct(
        IndexerRegistry $indexerRegistry
    ) {
        $this->indexerRegistry = $indexerRegistry;
    }

    /**
     * Invalidate design config grid indexer on store creation
     *
     * @param StoreStore $subject
     * @param \Closure $proceed
     * @return StoreStore
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSave(StoreStore $subject, \Closure $proceed)
    {
        $isObjectNew = $subject->getId() == 0;
        $result = $proceed();
        if ($isObjectNew) {
            $this->indexerRegistry->get(Config::DESIGN_CONFIG_GRID_INDEXER_ID)->invalidate();
        }
        return $result;
    }

    /**
     * Invalidate design config grid indexer on store removal
     *
     * @param StoreStore $subject
     * @param StoreStore $result
     * @return StoreStore
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDelete(StoreStore $subject, $result)
    {
        $this->indexerRegistry->get(Config::DESIGN_CONFIG_GRID_INDEXER_ID)->invalidate();
        return $result;
    }
}
