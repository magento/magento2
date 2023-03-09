<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Theme\Api\Data\DesignConfigInterface;
use Magento\Theme\Api\DesignConfigRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Theme\Model\Data\Design\Config as DesignConfig;
use Magento\Theme\Model\Design\Config\Storage as ConfigStorage;
use Magento\Theme\Model\Design\Config\Validator;

class DesignConfigRepository implements DesignConfigRepositoryInterface
{
    /**
     * Design config validator
     *
     * @var Validator
     */
    private $validator;

    /**
     * @param ConfigStorage $configStorage
     * @param ReinitableConfigInterface $reinitableConfig
     * @param IndexerRegistry $indexerRegistry
     */
    public function __construct(
        protected readonly ConfigStorage $configStorage,
        protected readonly ReinitableConfigInterface $reinitableConfig,
        protected readonly IndexerRegistry $indexerRegistry
    ) {
    }

    /**
     * Get config validator
     *
     * @return Design\Config\Validator
     *
     * @deprecated 100.1.0
     */
    private function getValidator()
    {
        if (null === $this->validator) {
            $this->validator = ObjectManager::getInstance()->get(
                Validator::class
            );
        }
        return $this->validator;
    }

    /**
     * @inheritDoc
     */
    public function getByScope($scope, $scopeId)
    {
        return $this->configStorage->load($scope, $scopeId);
    }

    /**
     * @inheritDoc
     */
    public function save(DesignConfigInterface $designConfig)
    {
        if (!($designConfig->getExtensionAttributes() &&
            $designConfig->getExtensionAttributes()->getDesignConfigData())
        ) {
            throw new LocalizedException(
                __("The config can't be saved because it's empty. Complete the config and try again.")
            );
        }

        $this->getValidator()->validate($designConfig);

        $this->configStorage->save($designConfig);
        $this->reinitableConfig->reinit();
        $this->reindexGrid();

        return $designConfig;
    }

    /**
     * @inheritDoc
     */
    public function delete(DesignConfigInterface $designConfig)
    {
        if (!($designConfig->getExtensionAttributes() &&
            $designConfig->getExtensionAttributes()->getDesignConfigData())
        ) {
            throw new LocalizedException(
                __("The config can't be saved because it's empty. Complete the config and try again.")
            );
        }

        $this->configStorage->delete($designConfig);
        $this->reinitableConfig->reinit();
        $this->reindexGrid();

        return $designConfig;
    }

    /**
     * Synchronize design config grid
     *
     * @return void
     */
    protected function reindexGrid()
    {
        $this->indexerRegistry->get(DesignConfig::DESIGN_CONFIG_GRID_INDEXER_ID)->reindexAll();
    }
}
