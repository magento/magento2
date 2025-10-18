<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Indexer\Console\Command;

use Exception;
use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for invalidating indexers.
 */
class IndexerResetStateCommand extends AbstractIndexerManageCommand
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('indexer:reset')
            ->setDescription('Resets indexer status to invalid')
            ->setDefinition($this->getInputList());

        parent::configure();
    }

    /**
     * Invalidate / reset the indexer
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $indexers = $this->getIndexers($input);
        foreach ($indexers as $indexer) {
            try {
                $indexer->invalidate();
                $output->writeln($indexer->getTitle() . ' indexer has been invalidated.');
            } catch (LocalizedException $e) {
                $output->writeln($e->getMessage());
                return Cli::RETURN_FAILURE;
            } catch (Exception $e) {
                $output->writeln($indexer->getTitle() . ' indexer process unknown error:');
                $output->writeln($e->getMessage());
                return Cli::RETURN_FAILURE;
            }
        }

        return Cli::RETURN_SUCCESS;
    }
}
