<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Indexer\Console\Command;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ObjectManagerFactory;
use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Indexer\Config\DependencyInfoProvider;
use Magento\Framework\Indexer\ConfigInterface;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Indexer\StateInterface;
use Magento\Indexer\Model\Processor\MakeSharedIndexValid;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to run indexers
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
     * @var MakeSharedIndexValid|null
     */
    private $makeSharedValid;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ObjectManagerFactory $objectManagerFactory
     * @param IndexerRegistry|null $indexerRegistry
     * @param DependencyInfoProvider|null $dependencyInfoProvider
     * @param MakeSharedIndexValid|null $makeSharedValid
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        ObjectManagerFactory $objectManagerFactory,
        IndexerRegistry $indexerRegistry = null,
        DependencyInfoProvider $dependencyInfoProvider = null,
        MakeSharedIndexValid $makeSharedValid = null,
        ?LoggerInterface $logger = null
    ) {
        $this->indexerRegistry = $indexerRegistry;
        $this->dependencyInfoProvider = $dependencyInfoProvider;
        $this->makeSharedValid = $makeSharedValid;
        $this->logger = $logger ?: ObjectManager::getInstance()->get(LoggerInterface::class);
        parent::__construct($objectManagerFactory);
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('indexer:reindex')
            ->setDescription('Reindexes Data')
            ->setDefinition($this->getInputList());

        parent::configure();
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output):int
    {
        $returnValue = Cli::RETURN_SUCCESS;
        foreach ($this->getIndexers($input) as $indexer) {
            try {
                $this->validateIndexerStatus($indexer);

                $output->write($indexer->getTitle() . ' index ');

                $startTime = new \DateTimeImmutable();
                $indexerConfig = $this->getConfig()->getIndexer($indexer->getId());
                $sharedIndex = $indexerConfig['shared_index'] ?? null;

                // Skip indexers having shared index that was already complete
                if (!in_array($sharedIndex, $this->sharedIndexesComplete)) {
                    $indexer->reindexAll();
                    if (!empty($sharedIndex) && $this->getMakeSharedValid()->execute($sharedIndex)) {
                        $this->sharedIndexesComplete[] = $sharedIndex;
                    }
                }
                $endTime = new \DateTimeImmutable();
                $interval = $startTime->diff($endTime);
                $days = $interval->format('%d');
                $hours = $days > 0 ? $days * 24 + $interval->format('%H') : $interval->format('%H');
                $minutes = $interval->format('%I');
                $seconds = $interval->format('%S');

                $output->writeln(
                    __('has been rebuilt successfully in %1:%2:%3', $hours, $minutes, $seconds)
                );
            } catch (\Throwable $e) {
                $output->writeln('process error during indexation process:');
                $output->writeln($e->getMessage());

                $output->writeln($e->getTraceAsString(), OutputInterface::VERBOSITY_DEBUG);
                $returnValue = Cli::RETURN_FAILURE;

                $this->logger->critical($e->getMessage());
            }
        }

        return $returnValue;
    }

    /**
     * @inheritdoc
     *
     * Returns the ordered list of specified indexers and related indexers.
     */
    protected function getIndexers(InputInterface $input)
    {
        $indexers = parent::getIndexers($input);
        $allIndexers = $this->getAllIndexers();
        if (!array_diff_key($allIndexers, $indexers)) {
            return $indexers;
        }

        $relatedIndexers = [];
        $dependentIndexers = [];

        foreach ($indexers as $indexer) {
            $relatedIndexers[] = $this->getRelatedIndexerIds($indexer->getId());
            $dependentIndexers[] = $this->getDependentIndexerIds($indexer->getId());
        }

        $relatedIndexers = array_unique(array_merge([], ...$relatedIndexers));
        $dependentIndexers = array_merge([], ...$dependentIndexers);

        $invalidRelatedIndexers = [];
        foreach ($relatedIndexers as $relatedIndexer) {
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
    private function getRelatedIndexerIds(string $indexerId): array
    {
        $relatedIndexerIds = [];
        foreach ($this->getDependencyInfoProvider()->getIndexerIdsToRunBefore($indexerId) as $relatedIndexerId) {
            $relatedIndexerIds[] = [$relatedIndexerId];
            $relatedIndexerIds[] = $this->getRelatedIndexerIds($relatedIndexerId);
        }
        $relatedIndexerIds = array_unique(array_merge([], ...$relatedIndexerIds));

        return $relatedIndexerIds;
    }

    /**
     * Return all indexer Ids which depend on the current indexer (directly or indirectly).
     *
     * @param string $indexerId
     * @return array
     */
    private function getDependentIndexerIds(string $indexerId): array
    {
        $dependentIndexerIds = [];
        foreach (array_keys($this->getConfig()->getIndexers()) as $id) {
            $dependencies = $this->getDependencyInfoProvider()->getIndexerIdsToRunBefore($id);
            if (array_search($indexerId, $dependencies) !== false) {
                $dependentIndexerIds[] = [$id];
                $dependentIndexerIds[] = $this->getDependentIndexerIds($id);
            }
        }
        $dependentIndexerIds = array_unique(array_merge([], ...$dependentIndexerIds));

        return $dependentIndexerIds;
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
     * Get config
     *
     * @return ConfigInterface
     * @deprecated 100.1.0 We don't recommend this approach anymore
     * @see Add a new optional parameter to the constructor at the end of the arguments list instead
     * and fetch the dependency using Magento\Framework\App\ObjectManager::getInstance() in the constructor body
     */
    private function getConfig()
    {
        if (!$this->config) {
            $this->config = $this->getObjectManager()->get(ConfigInterface::class);
        }
        return $this->config;
    }

    /**
     * Get dependency info provider
     *
     * @return DependencyInfoProvider
     * @deprecated 100.2.0 We don't recommend this approach anymore
     * @see Add a new optional parameter to the constructor at the end of the arguments list instead
     * and fetch the dependency using Magento\Framework\App\ObjectManager::getInstance() in the constructor body
     */
    private function getDependencyInfoProvider()
    {
        if (!$this->dependencyInfoProvider) {
            $this->dependencyInfoProvider = $this->getObjectManager()->get(DependencyInfoProvider::class);
        }
        return $this->dependencyInfoProvider;
    }

    /**
     * Get MakeSharedIndexValid processor.
     *
     * @return MakeSharedIndexValid
     */
    private function getMakeSharedValid(): MakeSharedIndexValid
    {
        if (!$this->makeSharedValid) {
            $this->makeSharedValid = $this->getObjectManager()->get(MakeSharedIndexValid::class);
        }

        return $this->makeSharedValid;
    }
}
