<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Indexer\Design\Config\Plugin;

use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Store\Model\Website as StoreWebsite;
use Magento\Theme\Model\Data\Design\Config;

class Website
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
     * Invalidate design config grid indexer on website creation
     *
     * @param StoreWebsite $subject
     * @param \Closure $proceed
     * @return StoreWebsite
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSave(StoreWebsite $subject, \Closure $proceed)
    {
        $isObjectNew = $subject->getId() == 0;
        $result = $proceed();
        if ($isObjectNew) {
            $this->indexerRegistry->get(Config::DESIGN_CONFIG_GRID_INDEXER_ID)->invalidate();
        }
        return $result;
    }

    /**
     * Invalidate design config grid indexer on website removal
     *
     * @param StoreWebsite $subject
     * @param StoreWebsite $result
     * @return StoreWebsite
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDelete(StoreWebsite $subject, $result)
    {
        $this->indexerRegistry->get(Config::DESIGN_CONFIG_GRID_INDEXER_ID)->invalidate();
        return $result;
    }
}
