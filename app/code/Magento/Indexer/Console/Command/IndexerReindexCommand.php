<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Console\Command;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Indexer\StateInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Indexer\ConfigInterface;
use Magento\Framework\App\ObjectManagerFactory;
use Magento\Indexer\Model\IndexerFactory;

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
     * @var \Magento\Framework\Indexer\ConfigInterface
     */
    private $config;

    /**
     * @var IndexerFactory
     */
    private $indexerFactory;

    /**
     * Constructor
     *
     * @param ObjectManagerFactory $objectManagerFactory
     * @param IndexerFactory|null $indexerFactory
     */
    public function __construct(
        ObjectManagerFactory $objectManagerFactory,
        IndexerFactory $indexerFactory = null
    ) {
        parent::__construct($objectManagerFactory, $indexerFactory);
        $this->indexerFactory = $indexerFactory;
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
        $indexers = $this->getIndexers($input);
        $returnValue = \Magento\Framework\Console\Cli::RETURN_SUCCESS;
        foreach ($indexers as $indexer) {
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
            } catch (LocalizedException $e) {
                $output->writeln($e->getMessage());
                // we must have an exit code higher than zero to indicate something was wrong
                $returnValue = \Magento\Framework\Console\Cli::RETURN_FAILURE;
            } catch (\Exception $e) {
                $output->writeln($indexer->getTitle() . ' indexer process unknown error:');
                $output->writeln($e->getMessage());
                // we must have an exit code higher than zero to indicate something was wrong
                $returnValue = \Magento\Framework\Console\Cli::RETURN_FAILURE;
            }
        }
        return $returnValue;
    }

    /**
     * Validate that indexer is not locked
     *
     * @param \Magento\Framework\Indexer\IndexerInterface $indexer
     * @return void
     * @throws LocalizedException
     */
    private function validateIndexerStatus(\Magento\Framework\Indexer\IndexerInterface $indexer)
    {
        if ($indexer->getStatus() == \Magento\Framework\Indexer\StateInterface::STATUS_WORKING) {
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
            throw new \InvalidArgumentException('sharedIndex must be a valid shared index identifier');
        }
        $indexerIds = $this->getIndexerIdsBySharedIndex($sharedIndex);
        if (empty($indexerIds)) {
            return $this;
        }
        foreach ($indexerIds as $indexerId) {
            /** @var \Magento\Indexer\Model\Indexer $indexer */
            $indexer = $this->getIndexerFactory()->create();
            $indexer->load($indexerId);
            /** @var \Magento\Indexer\Model\Indexer\State $state */
            $state = $indexer->getState();
            $state->setStatus(\Magento\Framework\Indexer\StateInterface::STATUS_VALID);
            $state->save();
        }
        $this->sharedIndexesComplete[] = $sharedIndex;
        return $this;
    }

    /**
     * Get config
     *
     * @return \Magento\Framework\Indexer\ConfigInterface
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
     * Get indexer factory
     *
     * @return IndexerFactory
     * @deprecated 100.2.0
     */
    private function getIndexerFactory()
    {
        if (null === $this->indexerFactory) {
            $this->indexerFactory = $this->getObjectManager()->get(IndexerFactory::class);
        }
        return $this->indexerFactory;
    }
}
