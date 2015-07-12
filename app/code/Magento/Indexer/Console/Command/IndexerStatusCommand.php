<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for displaying status of indexers.
 */
class IndexerStatusCommand extends AbstractIndexerManageCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('indexer:status')
            ->setDescription('Shows status of Indexer')
            ->setDefinition($this->getInputList());
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $indexers = $this->getIndexers($input, $output);
        foreach ($indexers as $indexer) {
            $status = 'unknown';
            switch ($indexer->getStatus()) {
                case \Magento\Indexer\Model\Indexer\State::STATUS_VALID:
                    $status = 'Ready';
                    break;
                case \Magento\Indexer\Model\Indexer\State::STATUS_INVALID:
                    $status = 'Reindex required';
                    break;
                case \Magento\Indexer\Model\Indexer\State::STATUS_WORKING:
                    $status = 'Processing';
                    break;
            }
            $output->writeln(sprintf('%-50s ', $indexer->getTitle() . ':') . $status);
        }
    }
}
