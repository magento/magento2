<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Indexer\Console\Command;

use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for displaying current index mode for indexers.
 */
class IndexerShowModeCommand extends AbstractIndexerManageCommand
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('indexer:show-mode')
            ->setDescription('Shows Index Mode')
            ->setDefinition($this->getInputList());

        parent::configure();
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $indexers = $this->getIndexers($input);
        foreach ($indexers as $indexer) {
            $status = $indexer->isScheduled() ? 'Update by Schedule' : 'Update on Save';
            $output->writeln(sprintf('%-50s ', $indexer->getTitle() . ':') . $status);
        }

        return Cli::RETURN_SUCCESS;
    }
}
