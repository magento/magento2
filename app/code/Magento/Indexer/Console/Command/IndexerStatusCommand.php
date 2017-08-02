<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
        $indexers = $this->getIndexers($input);
        foreach ($indexers as $indexer) {
            $status = 'unknown';
            switch ($indexer->getStatus()) {
                case \Magento\Framework\Indexer\StateInterface::STATUS_VALID:
                    $status = 'Ready';
                    break;
                case \Magento\Framework\Indexer\StateInterface::STATUS_INVALID:
                    $status = 'Reindex required';
                    break;
                case \Magento\Framework\Indexer\StateInterface::STATUS_WORKING:
                    $status = 'Processing';
                    break;
            }
            $output->writeln(sprintf('%-50s ', $indexer->getTitle() . ':') . $status);
        }
    }
}
