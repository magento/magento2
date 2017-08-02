<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Console\Command;

use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Indexer\Config\DependencyInfoProvider;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Indexer\StateInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Indexer\ConfigInterface;
use Magento\Framework\App\ObjectManagerFactory;

/**
 * Command to run indexers
 * @since 2.0.0
 */
class IndexerReindexCommand extends AbstractIndexerManageCommand
{
    /**
     * @var array
     * @since 2.1.0
     */
    private $sharedIndexesComplete = [];

    /**
     * @var ConfigInterface
     * @since 2.1.0
     */
    private $config;

    /**
     * @var IndexerRegistry
     * @since 2.2.0
     */
    private $indexerRegistry;

    /**
     * @var DependencyInfoProvider|null
     * @since 2.2.0
     */
    private $dependencyInfoProvider;

    /**
     * @param ObjectManagerFactory $objectManagerFactory
     * @param IndexerRegistry|null $indexerRegistry
     * @param DependencyInfoProvider|null $dependencyInfoProvider
     * @since 2.2.0
     */
    public function __construct(
        ObjectManagerFactory $objectManagerFactory,
        IndexerRegistry $indexerRegistry = null,
        DependencyInfoProvider $dependencyInfoProvider = null
    ) {
        $this->indexerRegistry = $indexerRegistry;
        $this->dependencyInfoProvider = $dependencyInfoProvider;
        parent::__construct($objectManagerFactory);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    protected function configure()
    {
        $this->setName('indexer:reindex')
            ->setDescription('Reindexes Data')
            ->setDefinition($this->getInputList());

        parent::configure();
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $returnValue = Cli::RETURN_FAILURE;
        foreach ($this->getIndexers($input) as $indexer) {
            try {
                $this->validateIndexerStatus($indexer);
                $startTime = microtime(true);
                $indexerConfig = $this->getConfig()->getIndexer($indexer->getId());
                $sharedIndex = $indexerConfig['shared_index'];

                // Skip indexers having shared index that was already complete
                if (!in_array($sharedIndex, $this->sharedIndexesComplete)) {
                    $indexer->reindexAll();
                    if ($sharedIndex) {
                        $this->validateSharedIndex($sharedIndex);
                    }
                }
                $resultTime = microtime(true) - $startTime;
                $output->writeln(
                    $indexer->getTitle() . ' index has been rebuilt successfully in ' . gmdate('H:i:s', $resultTime)
                );
                $returnValue = Cli::RETURN_SUCCESS;
            } catch (LocalizedException $e) {
                $output->writeln($e->getMessage());
            } catch (\Exception $e) {
                $output->writeln($indexer->getTitle() . ' indexer process unknown error:');
                $output->writeln($e->getMessage());
            }
        }
        return $returnValue;
    }

    /**
     * {@inheritdoc} Returns the ordered list of specified indexers and related indexers.
     * @since 2.2.0
     */
    protected function getIndexers(InputInterface $input)
    {
        $indexers =  parent::getIndexers($input);
        $allIndexers = $this->getAllIndexers();
        if (!array_diff_key($allIndexers, $indexers)) {
            return $indexers;
        }

        $relatedIndexers = [];
        $dependentIndexers = [];
        foreach ($indexers as $indexer) {
            $relatedIndexers = array_merge(
                $relatedIndexers,
                $this->getRelatedIndexerIds($indexer->getId())
            );
            $dependentIndexers = array_merge(
                $dependentIndexers,
                $this->getDependentIndexerIds($indexer->getId())
            );
        }

        $invalidRelatedIndexers = [];
        foreach (array_unique($relatedIndexers) as $relatedIndexer) {
            if ($allIndexers[$relatedIndexer]->isInvalid()) {
                $invalidRelatedIndexers[] = $relatedIndexer;
            }
        }

        return array_intersect_key(
            $allIndexers,
            array_flip(
                array_unique(
                    array_merge(
                        array_keys($indexers),
                        $invalidRelatedIndexers,
                        $dependentIndexers
                    )
                )
            )
        );
    }

    /**
     * Return all indexer Ids on which the current indexer depends (directly or indirectly).
     *
     * @param string $indexerId
     * @return array
     * @since 2.2.0
     */
    private function getRelatedIndexerIds(string $indexerId)
    {
        $relatedIndexerIds = [];
        foreach ($this->getDependencyInfoProvider()->getIndexerIdsToRunBefore($indexerId) as $relatedIndexerId) {
            $relatedIndexerIds = array_merge(
                $relatedIndexerIds,
                [$relatedIndexerId],
                $this->getRelatedIndexerIds($relatedIndexerId)
            );
        }

        return array_unique($relatedIndexerIds);
    }

    /**
     * Return all indexer Ids which depend on the current indexer (directly or indirectly).
     *
     * @param string $indexerId
     * @return array
     * @since 2.2.0
     */
    private function getDependentIndexerIds(string $indexerId)
    {
        $dependentIndexerIds = [];
        foreach (array_keys($this->getConfig()->getIndexers()) as $id) {
            $dependencies = $this->getDependencyInfoProvider()->getIndexerIdsToRunBefore($id);
            if (array_search($indexerId, $dependencies) !== false) {
                $dependentIndexerIds = array_merge(
                    $dependentIndexerIds,
                    [$id],
                    $this->getDependentIndexerIds($id)
                );
            }
        };

        return array_unique($dependentIndexerIds);
    }

    /**
     * Validate that indexer is not locked
     *
     * @param IndexerInterface $indexer
     * @return void
     * @throws LocalizedException
     * @since 2.1.0
     */
    private function validateIndexerStatus(IndexerInterface $indexer)
    {
        if ($indexer->getStatus() == StateInterface::STATUS_WORKING) {
            throw new LocalizedException(
                __(
                    '%1 index is locked by another reindex process. Skipping.',
                    $indexer->getTitle()
                )
            );
        }
    }

    /**
     * Get indexer ids that have common shared index
     *
     * @param string $sharedIndex
     * @return array
     * @since 2.1.0
     */
    private function getIndexerIdsBySharedIndex($sharedIndex)
    {
        $indexers = $this->getConfig()->getIndexers();
        $result = [];
        foreach ($indexers as $indexerConfig) {
            if ($indexerConfig['shared_index'] == $sharedIndex) {
                $result[] = $indexerConfig['indexer_id'];
            }
        }
        return $result;
    }

    /**
     * Validate indexers by shared index ID
     *
     * @param string $sharedIndex
     * @return $this
     * @since 2.1.0
     */
    private function validateSharedIndex($sharedIndex)
    {
        if (empty($sharedIndex)) {
            throw new \InvalidArgumentException('sharedIndex must be a valid shared index identifier');
        }
        $indexerIds = $this->getIndexerIdsBySharedIndex($sharedIndex);
        if (empty($indexerIds)) {
            return $this;
        }
        foreach ($indexerIds as $indexerId) {
            $indexer = $this->getIndexerRegistry()->get($indexerId);
            /** @var \Magento\Indexer\Model\Indexer\State $state */
            $state = $indexer->getState();
            $state->setStatus(StateInterface::STATUS_VALID);
            $state->save();
        }
        $this->sharedIndexesComplete[] = $sharedIndex;
        return $this;
    }

    /**
     * Get config
     *
     * @return ConfigInterface
     * @deprecated 2.1.0
     * @since 2.1.0
     */
    private function getConfig()
    {
        if (!$this->config) {
            $this->config = $this->getObjectManager()->get(ConfigInterface::class);
        }
        return $this->config;
    }

    /**
     * Get indexer registry.
     *
     * @return IndexerRegistry
     * @deprecated 2.2.0
     * @since 2.2.0
     */
    private function getIndexerRegistry()
    {
        if (!$this->indexerRegistry) {
            $this->indexerRegistry = $this->getObjectManager()->get(IndexerRegistry::class);
        }
        return $this->indexerRegistry;
    }

    /**
     * @return DependencyInfoProvider
     * @deprecated 2.2.0
     * @since 2.2.0
     */
    private function getDependencyInfoProvider()
    {
        if (!$this->dependencyInfoProvider) {
            $this->dependencyInfoProvider = $this->getObjectManager()->get(DependencyInfoProvider::class);
        }
        return $this->dependencyInfoProvider;
    }
}
