<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Setup\Patch\Data;

use Magento\Catalog\Model\ResourceModel\Config as CatalogConfig;
use Magento\CatalogSearch\Model\Indexer\Fulltext as FulltextIndexer;
use Magento\CatalogSearch\Model\Indexer\IndexerHandlerFactory;
use Magento\Elasticsearch\Model\Config;
use Magento\Elasticsearch\Model\Indexer\IndexerHandler;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchInterface;
use Magento\Store\Model\StoreDimensionProvider;

/**
 * Add the own-value sort fields of sortable attributes to the live index mapping and invalidate the fulltext index.
 *
 * Without an explicit mapping, a partial reindex before the full reindex would create the field through the
 * engine's dynamic string template as a text field, which cannot be sorted on and is copied into the search field.
 * Engine errors are not caught: setup:install and setup:upgrade validate the engine connection before data patches
 * run, and a failed patch is applied again by the next upgrade. A store without an index yet is skipped.
 */
class AddOwnValueSortFieldsToIndex implements DataPatchInterface
{
    /**
     * @var IndexerRegistry
     */
    private $indexerRegistry;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var IndexerHandlerFactory
     */
    private $indexerHandlerFactory;

    /**
     * @var StoreDimensionProvider
     */
    private $dimensionProvider;

    /**
     * @var CatalogConfig
     */
    private $catalogConfig;

    /**
     * @param IndexerRegistry $indexerRegistry
     * @param Config $config
     * @param IndexerHandlerFactory $indexerHandlerFactory
     * @param StoreDimensionProvider $dimensionProvider
     * @param CatalogConfig $catalogConfig
     */
    public function __construct(
        IndexerRegistry $indexerRegistry,
        Config $config,
        IndexerHandlerFactory $indexerHandlerFactory,
        StoreDimensionProvider $dimensionProvider,
        CatalogConfig $catalogConfig
    ) {
        $this->indexerRegistry = $indexerRegistry;
        $this->config = $config;
        $this->indexerHandlerFactory = $indexerHandlerFactory;
        $this->dimensionProvider = $dimensionProvider;
        $this->catalogConfig = $catalogConfig;
    }

    /**
     * @inheritDoc
     */
    public function apply(): PatchInterface
    {
        $this->indexerRegistry->get(FulltextIndexer::INDEXER_ID)->invalidate();

        if (!$this->config->isElasticsearchEnabled()) {
            return $this;
        }

        $attributeCodes = array_column($this->catalogConfig->getAttributesUsedForSortBy(), 'attribute_code');
        if (!$attributeCodes) {
            return $this;
        }
        $indexerHandler = $this->indexerHandlerFactory->create(
            ['data' => ['indexer_id' => FulltextIndexer::INDEXER_ID]]
        );
        if (!$indexerHandler instanceof IndexerHandler) {
            return $this;
        }
        foreach ($this->dimensionProvider->getIterator() as $dimensions) {
            foreach ($attributeCodes as $attributeCode) {
                $indexerHandler->updateIndex($dimensions, $attributeCode);
            }
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getAliases(): array
    {
        return [];
    }
}
