<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Indexer;

use Magento\CatalogSearch\Model\Indexer\Fulltext\Action\FullFactory;
use Magento\CatalogSearch\Model\Indexer\Scope\StateFactory;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext as FulltextResource;
use Magento\Framework\Indexer\Dimension\DimensionProviderInterface;
use Magento\Store\Model\StoreDimensionProvider;

/**
 * Provide functionality for Fulltext Search indexing.
 *
 * @api
 * @since 100.0.2
 */
class Fulltext implements
    \Magento\Framework\Indexer\ActionInterface,
    \Magento\Framework\Mview\ActionInterface,
    \Magento\Framework\Indexer\Dimension\DimensionalIndexerInterface
{
    /**
     * Indexer ID in configuration
     */
    const INDEXER_ID = 'catalogsearch_fulltext';

    /**
     * @var array index structure
     */
    protected $data;

    /**
     * @var IndexerHandlerFactory
     */
    private $indexerHandlerFactory;

    /**
     * @var \Magento\CatalogSearch\Model\Indexer\Fulltext\Action\Full
     */
    private $fullAction;

    /**
     * @var FulltextResource
     */
    private $fulltextResource;

    /**
     * @var IndexSwitcherInterface
     */
    private $indexSwitcher;

    /**
     * @var \Magento\CatalogSearch\Model\Indexer\Scope\State
     */
    private $indexScopeState;

    /**
     * @var DimensionProviderInterface
     */
    private $dimensionProvider;

    /**
     * @param FullFactory $fullActionFactory
     * @param IndexerHandlerFactory $indexerHandlerFactory
     * @param FulltextResource $fulltextResource
     * @param array $data
     * @param IndexSwitcherInterface $indexSwitcher
     * @param StateFactory $indexScopeStateFactory
     * @param DimensionProviderInterface $dimensionProvider
     */
    public function __construct(
        FullFactory $fullActionFactory,
        IndexerHandlerFactory $indexerHandlerFactory,
        FulltextResource $fulltextResource,
        IndexSwitcherInterface $indexSwitcher,
        StateFactory $indexScopeStateFactory,
        DimensionProviderInterface $dimensionProvider,
        array $data
    ) {
        $this->fullAction = $fullActionFactory->create(['data' => $data]);
        $this->indexerHandlerFactory = $indexerHandlerFactory;
        $this->fulltextResource = $fulltextResource;
        $this->data = $data;
        $this->indexSwitcher = $indexSwitcher;
        $this->indexScopeState = $indexScopeStateFactory->create();
        $this->dimensionProvider = $dimensionProvider;
    }

    /**
     * Execute materialization on ids entities
     *
     * @param int[] $entityIds
     * @return void
     * @throws \InvalidArgumentException
     */
    public function execute($entityIds)
    {
        foreach ($this->dimensionProvider->getIterator() as $dimension) {
            $this->executeByDimension($dimension, new \ArrayIterator($entityIds));
        }
    }

    /**
     * {@inheritdoc}
     * @throws \InvalidArgumentException
     */
    public function executeByDimension(array $dimensions, \Traversable $entityIds = null)
    {
        if (count($dimensions) > 1 || !isset($dimensions[StoreDimensionProvider::DIMENSION_NAME])) {
            throw new \InvalidArgumentException('Indexer "' . self::INDEXER_ID . '" support only Store dimension');
        }
        $storeId = $dimensions[StoreDimensionProvider::DIMENSION_NAME]->getValue();
        $saveHandler = $this->indexerHandlerFactory->create([
            'data' => $this->data
        ]);

        if (null === $entityIds) {
            $this->indexScopeState->useTemporaryIndex();
            $saveHandler->cleanIndex($dimensions);
            $saveHandler->saveIndex($dimensions, $this->fullAction->rebuildStoreIndex($storeId));

            $this->indexSwitcher->switchIndex($dimensions);
            $this->indexScopeState->useRegularIndex();

            $this->fulltextResource->resetSearchResultsByStore($storeId);
        } else {
            // internal implementation works only with array
            $entityIds = iterator_to_array($entityIds);
            $productIds = array_unique(
                array_merge($entityIds, $this->fulltextResource->getRelationsByChild($entityIds))
            );
            $saveHandler->deleteIndex($dimensions, new \ArrayIterator($productIds));
            $saveHandler->saveIndex($dimensions, $this->fullAction->rebuildStoreIndex($storeId, $productIds));
        }
    }

    /**
     * Execute full indexation
     *
     * @return void
     * @throws \InvalidArgumentException
     */
    public function executeFull()
    {
        foreach ($this->dimensionProvider->getIterator() as $dimension) {
            $this->executeByDimension($dimension);
        }
    }

    /**
     * Execute partial indexation by ID list
     *
     * @param int[] $ids
     * @return void
     */
    public function executeList(array $ids)
    {
        $this->execute($ids);
    }

    /**
     * Execute partial indexation by ID
     *
     * @param int $id
     * @return void
     */
    public function executeRow($id)
    {
        $this->execute([$id]);
    }
}
