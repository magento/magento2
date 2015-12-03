<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model;

use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Theme\Api\Data\DesignConfigInterface;
use Magento\Theme\Api\DesignConfigRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Theme\Model\Data\Design\Config;
use Magento\Theme\Model\Design\Config\Storage as ConfigStorage;

class DesignConfigRepository implements DesignConfigRepositoryInterface
{
    /** @var ReinitableConfigInterface */
    protected $reinitableConfig;

    /** @var IndexerRegistry */
    protected $indexerRegistry;

    /** @var ConfigStorage */
    protected $configStorage;

    /**
     * @param ConfigStorage $configStorage
     * @param ReinitableConfigInterface $reinitableConfig
     * @param IndexerRegistry $indexerRegistry
     */
    public function __construct(
        ConfigStorage $configStorage,
        ReinitableConfigInterface $reinitableConfig,
        IndexerRegistry $indexerRegistry
    ) {
        $this->reinitableConfig = $reinitableConfig;
        $this->indexerRegistry = $indexerRegistry;
        $this->configStorage = $configStorage;
    }

    /**
     * @inheritDoc
     */
    public function save(DesignConfigInterface $designConfig)
    {
        if (!($designConfig->getExtensionAttributes() &&
            $designConfig->getExtensionAttributes()->getDesignConfigData())
        ) {
            throw new LocalizedException(__('Can not save empty config'));
        }

        $this->configStorage->process($designConfig);

        try {
            $this->configStorage->delete();
            $this->configStorage->save();
            $this->reinitableConfig->reinit();
            $this->reindexGrid();
        } catch (\Exception $e) {
            $this->reinitableConfig->reinit();
            throw $e;
        }

        return $designConfig;
    }

    /**
     * Synchronize design config grid
     *
     * @return void
     */
    protected function reindexGrid()
    {
        $indexer = $this->indexerRegistry->get(Config::DESIGN_CONFIG_GRID_INDEXER_ID);
        if ($indexer instanceof IndexerInterface) {
            $indexer->reindexAll();
        }
    }
}
