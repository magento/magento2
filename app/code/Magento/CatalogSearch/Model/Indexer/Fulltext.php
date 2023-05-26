<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Indexer;

use Magento\CatalogSearch\Model\Indexer\Fulltext\Action\FullFactory;
use Magento\CatalogSearch\Model\Indexer\Scope\State;
use Magento\CatalogSearch\Model\Indexer\Scope\StateFactory;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext as FulltextResource;
use Magento\Elasticsearch\Model\Indexer\EnhancedIndexerHandler;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Indexer\DimensionProviderInterface;
use Magento\Framework\Indexer\SaveHandler\IndexerInterface;
use Magento\Store\Model\StoreDimensionProvider;
use Magento\Indexer\Model\ProcessManager;
use Magento\Framework\App\DeploymentConfig;

/**
 * Provide functionality for Fulltext Search indexing.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @api
 * @since 100.0.2
 */
class Fulltext implements
    \Magento\Framework\Indexer\ActionInterface,
    \Magento\Framework\Mview\ActionInterface,
    \Magento\Framework\Indexer\DimensionalIndexerInterface
{
    /**
     * Indexer ID in configuration
     */
    public const INDEXER_ID = 'catalogsearch_fulltext';

    /**
     * Default batch size
     */
    private const BATCH_SIZE = 1000;

    /**
     * Deployment config path
     *
     * @var string
     */
    private const DEPLOYMENT_CONFIG_INDEXER_BATCHES = 'indexer/batch_size/';

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
     * @deprecated
     * @see \Magento\Elasticsearch
     */
    private $indexSwitcher;

    /**
     * @var \Magento\CatalogSearch\Model\Indexer\Scope\State
     * @deprecated
     * @see \Magento\Elasticsearch
     */
    private $indexScopeState;

    /**
     * @var DimensionProviderInterface
     */
    private $dimensionProvider;

    /**
     * @var ProcessManager
     */
    private $processManager;

    /**
     * @var int
     */
    private $batchSize;

    /**
     * @var DeploymentConfig|null
     */
    private $deploymentConfig;

    /**
     * @param FullFactory $fullActionFactory
     * @param IndexerHandlerFactory $indexerHandlerFactory
     * @param FulltextResource $fulltextResource
     * @param IndexSwitcherInterface $indexSwitcher
     * @param StateFactory $indexScopeStateFactory
     * @param DimensionProviderInterface $dimensionProvider
     * @param array $data
     * @param ProcessManager|null $processManager
     * @param int|null $batchSize
     * @param DeploymentConfig|null $deploymentConfig
     * @param EnhancedIndexerHandler|null $enhancedIndexerHandler
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        FullFactory $fullActionFactory,
        IndexerHandlerFactory $indexerHandlerFactory,
        FulltextResource $fulltextResource,
        IndexSwitcherInterface $indexSwitcher,
        StateFactory $indexScopeStateFactory,
        DimensionProviderInterface $dimensionProvider,
        array $data,
        ProcessManager $processManager = null,
        ?int $batchSize = null,
        ?DeploymentConfig $deploymentConfig = null,
        ?EnhancedIndexerHandler $enhancedIndexerHandler = null
    ) {
        $this->fullAction = $fullActionFactory->create(['data' => $data]);
        $this->indexerHandlerFactory = $indexerHandlerFactory;
        $this->fulltextResource = $fulltextResource;
        $this->data = $data;
        $this->indexSwitcher = $indexSwitcher;
        $this->indexScopeState = ObjectManager::getInstance()->get(State::class);
        $this->dimensionProvider = $dimensionProvider;
        $this->processManager = $processManager ?: ObjectManager::getInstance()->get(ProcessManager::class);
        $this->batchSize = $batchSize ?? self::BATCH_SIZE;
        $this->deploymentConfig = $deploymentConfig ?: ObjectManager::getInstance()->get(DeploymentConfig::class);
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
            $this->executeByDimensions($dimension, new \ArrayIterator($entityIds));
        }
    }

    /**
     * @inheritdoc
     *
     * @throws \InvalidArgumentException|\Exception
     * @since 101.0.0
     */
    public function executeByDimensions(array $dimensions, \Traversable $entityIds = null)
    {
        if (count($dimensions) > 1 || !isset($dimensions[StoreDimensionProvider::DIMENSION_NAME])) {
            throw new \InvalidArgumentException('Indexer "' . self::INDEXER_ID . '" support only Store dimension');
        }
        $storeId = $dimensions[StoreDimensionProvider::DIMENSION_NAME]->getValue();
        $saveHandler = $this->indexerHandlerFactory->create(
            [
                'data' => $this->data,
            ]
        );
        $enhancedIndexerHandler = $this->indexerHandlerFactory->createSpecificHandler(
            [
                'data' => $this->data,
            ],
            EnhancedIndexerHandler::class
        );

        if (null === $entityIds) {
            $saveHandler->cleanIndex($dimensions);
            $saveHandler->saveIndex($dimensions, $this->fullAction->rebuildStoreIndex($storeId));

            $this->fulltextResource->resetSearchResultsByStore($storeId);
        } else {
            // internal implementation works only with array
            $entityIds = iterator_to_array($entityIds);
            $currentBatch = [];
            $i = 0;

            $this->batchSize = $this->deploymentConfig->get(
                self::DEPLOYMENT_CONFIG_INDEXER_BATCHES . self::INDEXER_ID . '/partial_reindex'
            ) ?? $this->batchSize;

            foreach ($entityIds as $entityId) {
                $currentBatch[] = $entityId;
                if (++$i === $this->batchSize) {
                    $this->processBatch($enhancedIndexerHandler, $dimensions, $currentBatch);
                    $i = 0;
                    $currentBatch = [];
                }
            }
            if (!empty($currentBatch)) {
                $this->processBatch($enhancedIndexerHandler, $dimensions, $currentBatch);
            }
        }
    }

    /**
     * Process batch
     *
     * @param EnhancedIndexerHandler $saveHandler
     * @param array $dimensions
     * @param array $entityIds
     * @throws \Exception
     */
    private function processBatch(
        EnhancedIndexerHandler $saveHandler,
        array $dimensions,
        array $entityIds
    ) : void {
        $storeId = $dimensions[StoreDimensionProvider::DIMENSION_NAME]->getValue();
        $productIds = array_unique(
            array_merge($entityIds, $this->fulltextResource->getRelationsByChild($entityIds))
        );
        if ($saveHandler->isAvailable($dimensions)) {
            $saveHandler->enableStackedActions();
            $saveHandler->deleteIndex($dimensions, new \ArrayIterator($productIds));
            $saveHandler->saveIndex($dimensions, $this->fullAction->rebuildStoreIndex($storeId, $productIds));
            $saveHandler->triggerStackedActions();
            $saveHandler->disableStackedActions();
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
        $userFunctions = [];
        foreach ($this->dimensionProvider->getIterator() as $dimension) {
            $userFunctions[] = function () use ($dimension) {
                $this->executeByDimensions($dimension);
            };
        }
        $this->processManager->execute($userFunctions);
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
