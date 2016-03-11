<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Console\Command;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Indexer\StateInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Indexer\ConfigInterface;
use Magento\Framework\App\ObjectManagerFactory;

/**
 * Command for reindexing indexers.
 */
class IndexerReindexCommand extends AbstractIndexerManageCommand
{
    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * Constructor
     * @param ObjectManagerFactory $objectManagerFactory
     */
    public function __construct(ObjectManagerFactory $objectManagerFactory)
    {
        parent::__construct($objectManagerFactory);
        $this->config = $this->getObjectManager()->create(ConfigInterface::class);
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

        $sharedIndexesComplete = [];
        foreach ($indexers as $indexer) {
            try {
                $startTime = microtime(true);
                $indexerConfig = $this->config->getIndexer($indexer->getId());

                // Skip indexers having shared index that was already complete
                if (!in_array($indexerConfig['shared_index'], $sharedIndexesComplete)) {
                    $indexer->reindexAll();
                } else {
                    $indexer->getState()->setStatus(StateInterface::STATUS_VALID)->save();
                }
                if ($indexerConfig['shared_index']) {
                    $sharedIndexesComplete[] = $indexerConfig['shared_index'];
                }
                $resultTime = microtime(true) - $startTime;
                $output->writeln(
                    $indexer->getTitle() . ' index has been rebuilt successfully in ' . gmdate('H:i:s', $resultTime)
                );
            } catch (LocalizedException $e) {
                $output->writeln($e->getMessage());
            } catch (\Exception $e) {
                $output->writeln($indexer->getTitle() . ' indexer process unknown error:');
                $output->writeln($e->getMessage());
            }
        }
    }
}
