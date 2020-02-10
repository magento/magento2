<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Setup\Patch\Data;

use Magento\CatalogSearch\Model\Indexer\IndexerHandlerFactory;
use Magento\Framework\Indexer\DimensionProviderInterface;
use Magento\Framework\Search\EngineResolverInterface;
use Magento\CatalogSearch\Model\Indexer\Fulltext;
use Magento\Framework\Indexer\ConfigInterface;

/**
 * This patch will create catalogsearch_fulltext tables in MySQL DB.
 *
 * @package Magento\CatalogSearch\Setup\Patch\Data
 */
class MySQLCatalogSearchTableCreation implements \Magento\Framework\Setup\Patch\DataPatchInterface
{
    /**
     * @var IndexerHandlerFactory
     */
    private $indexerHandlerFactory;

    /**
     * @var DimensionProviderInterface
     */
    private $dimensionProvider;

    /**
     * @var EngineResolverInterface
     */
    private $searchEngineResolver;

    /**
     * @var array index structure
     */
    protected $data;

    /**
     * MySQLCatalogSearchTableCreation constructor.
     * @param IndexerHandlerFactory $indexerHandlerFactory
     * @param DimensionProviderInterface $dimensionProvider
     * @param EngineResolverInterface $searchEngineResolver
     * @param ConfigInterface $indexerConfig
     * @return void
     */
    public function __construct(
        IndexerHandlerFactory $indexerHandlerFactory,
        DimensionProviderInterface $dimensionProvider,
        EngineResolverInterface $searchEngineResolver,
        ConfigInterface $indexerConfig
    ) {
        $this->indexerHandlerFactory = $indexerHandlerFactory;
        $this->dimensionProvider = $dimensionProvider;
        $this->searchEngineResolver = $searchEngineResolver;
        $this->data = $indexerConfig->getIndexer(Fulltext::INDEXER_ID);
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        if ($this->searchEngineResolver->getCurrentSearchEngine() === 'mysql') {
            $saveHandler = $this->indexerHandlerFactory->create(
                [
                    'data' => $this->data,
                ]
            );

            foreach ($this->dimensionProvider->getIterator() as $dimension) {
                $saveHandler->indexStructure->create($this->data['indexer_id'], [], $dimension);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }
}
