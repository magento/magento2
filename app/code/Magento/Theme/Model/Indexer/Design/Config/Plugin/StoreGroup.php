<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Indexer\Design\Config\Plugin;

use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Store\Model\Group;
use Magento\Theme\Model\Data\Design\Config;

class StoreGroup
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
     * Invalidate design config grid indexer on store group removal
     *
     * @param Group $subject
     * @param Group $result
     * @return Group
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDelete(Group $subject, $result)
    {
        $this->indexerRegistry->get(Config::DESIGN_CONFIG_GRID_INDEXER_ID)->invalidate();
        return $result;
    }
}
