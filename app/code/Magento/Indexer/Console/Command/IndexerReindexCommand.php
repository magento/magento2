<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Console\Command;

use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Indexer\ConfigInterface;

/**
 * Command for reindexing indexers.
 */
class IndexerReindexCommand extends AbstractIndexerManageCommand
{
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
                // Skip indexers that have shared index that was already
                if (!in_array($indexer->getSharedIndex(), $sharedIndexesComplete)) {
                    $indexer->reindexAll();
                }
                if ($indexer->getSharedIndex()) {
                    $sharedIndexesComplete[] = $indexer->getSharedIndex();
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
