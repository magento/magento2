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
 */
class IndexerReindexCommand extends AbstractIndexerManageCommand
{
    /**
     * @var array
     */
    private $sharedIndexesComplete = [];

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var IndexerRegistry
     */
    private $indexerRegistry;

    /**
     * @var DependencyInfoProvider|null
     */
    private $dependencyInfoProvider;

    /**
     * @param ObjectManagerFactory $objectManagerFactory
     * @param IndexerRegistry|null $indexerRegistry
     * @param DependencyInfoProvider|null $dependencyInfoProvider
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
     */
    private function validateSharedIndex($sharedIndex)
    {
        if (empty($sharedIndex)) {
            throw new \InvalidArgumentException(
                'The sharedIndex is an invalid shared index identifier. Verify the identifier and try again.'
            );
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
     * @deprecated 100.1.0
     */
    private function getConfig()
    {
        if (!$this->config) {
            $this->config = $this->getObjectManager()->get(ConfigInterface::class);
        }
        return $this->config;
    }

    /**
     * @return IndexerRegistry
     * @deprecated 100.2.0
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
     * @deprecated 100.2.0
     */
    private function getDependencyInfoProvider()
    {
        if (!$this->dependencyInfoProvider) {
            $this->dependencyInfoProvider = $this->getObjectManager()->get(DependencyInfoProvider::class);
        }
        return $this->dependencyInfoProvider;
    }
}
