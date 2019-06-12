<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Indexer;

use Magento\CatalogSearch\Model\Indexer\Fulltext\Action\FullFactory;
use Magento\CatalogSearch\Model\Indexer\Scope\StateFactory;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext as FulltextResource;
<<<<<<< HEAD
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Search\Request\Config as SearchRequestConfig;
use Magento\Framework\Search\Request\DimensionFactory;
use Magento\Store\Model\StoreManagerInterface;
=======
use Magento\Framework\Indexer\DimensionProviderInterface;
use Magento\Store\Model\StoreDimensionProvider;
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
use Magento\Indexer\Model\ProcessManager;

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
<<<<<<< HEAD
=======
     * @var DimensionProviderInterface
     */
    private $dimensionProvider;

    /**
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     * @var ProcessManager
     */
    private $processManager;

    /**
     * @param FullFactory $fullActionFactory
     * @param IndexerHandlerFactory $indexerHandlerFactory
     * @param FulltextResource $fulltextResource
     * @param IndexSwitcherInterface $indexSwitcher
<<<<<<< HEAD
     * @param Scope\State $indexScopeState
     * @param ProcessManager $processManager
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
=======
     * @param StateFactory $indexScopeStateFactory
     * @param DimensionProviderInterface $dimensionProvider
     * @param array $data
     * @param ProcessManager $processManager
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    public function __construct(
        FullFactory $fullActionFactory,
        IndexerHandlerFactory $indexerHandlerFactory,
        FulltextResource $fulltextResource,
        IndexSwitcherInterface $indexSwitcher,
        StateFactory $indexScopeStateFactory,
        DimensionProviderInterface $dimensionProvider,
        array $data,
<<<<<<< HEAD
        IndexSwitcherInterface $indexSwitcher = null,
        State $indexScopeState = null,
=======
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        ProcessManager $processManager = null
    ) {
        $this->fullAction = $fullActionFactory->create(['data' => $data]);
        $this->indexerHandlerFactory = $indexerHandlerFactory;
        $this->fulltextResource = $fulltextResource;
        $this->data = $data;
<<<<<<< HEAD
        if (null === $indexSwitcher) {
            $indexSwitcher = ObjectManager::getInstance()->get(IndexSwitcherInterface::class);
        }
        if (null === $indexScopeState) {
            $indexScopeState = ObjectManager::getInstance()->get(State::class);
        }
        if (null === $processManager) {
            $processManager = ObjectManager::getInstance()->get(ProcessManager::class);
        }
        $this->indexSwitcher = $indexSwitcher;
        $this->indexScopeState = $indexScopeState;
        $this->processManager = $processManager;
=======
        $this->indexSwitcher = $indexSwitcher;
        $this->indexScopeState = $indexScopeStateFactory->create();
        $this->dimensionProvider = $dimensionProvider;
        $this->processManager = $processManager ?: \Magento\Framework\App\ObjectManager::getInstance()->get(
            ProcessManager::class
        );
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
<<<<<<< HEAD
        $storeIds = array_keys($this->storeManager->getStores());
        /** @var IndexerHandler $saveHandler */
        $saveHandler = $this->indexerHandlerFactory->create([
            'data' => $this->data
        ]);

        foreach ($storeIds as $storeId) {
            $dimension = $this->dimensionFactory->create(['name' => 'scope', 'value' => $storeId]);
            $productIds = array_unique(array_merge($ids, $this->fulltextResource->getRelationsByChild($ids)));
            $saveHandler->deleteIndex([$dimension], new \ArrayObject($productIds));
            $saveHandler->saveIndex([$dimension], $this->fullAction->rebuildStoreIndex($storeId, $productIds));
=======
        foreach ($this->dimensionProvider->getIterator() as $dimension) {
            $this->executeByDimensions($dimension, new \ArrayIterator($entityIds));
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        }
    }

    /**
     * @inheritdoc
     *
     * @throws \InvalidArgumentException
     */
    public function executeByDimensions(array $dimensions, \Traversable $entityIds = null)
    {
<<<<<<< HEAD
        $storeIds = array_keys($this->storeManager->getStores());

        $userFunctions = [];
        foreach ($storeIds as $storeId) {
            $userFunctions[$storeId] = function () use ($storeId) {
                return $this->executeFullByStore($storeId);
            };
        }

        $this->processManager->execute($userFunctions);

        $this->fulltextResource->resetSearchResults();
        $this->searchRequestConfig->reset();
=======
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
            if ($saveHandler->isAvailable($dimensions)) {
                $saveHandler->deleteIndex($dimensions, new \ArrayIterator($productIds));
                $saveHandler->saveIndex($dimensions, $this->fullAction->rebuildStoreIndex($storeId, $productIds));
            }
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
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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

    /**
     * Execute full indexation by storeID
     *
     * @param int $storeId
     */
    private function executeFullByStore($storeId)
    {
        /** @var IndexerHandler $saveHandler */
        $saveHandler = $this->indexerHandlerFactory->create([
            'data' => $this->data
        ]);

        $dimensions = [$this->dimensionFactory->create(['name' => 'scope', 'value' => $storeId])];
        $this->indexScopeState->useTemporaryIndex();

        $saveHandler->cleanIndex($dimensions);
        $saveHandler->saveIndex($dimensions, $this->fullAction->rebuildStoreIndex($storeId));

        $this->indexSwitcher->switchIndex($dimensions);
        $this->indexScopeState->useRegularIndex();
    }
}
