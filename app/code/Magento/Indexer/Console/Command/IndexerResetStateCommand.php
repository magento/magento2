<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Console\Command;

use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Indexer\StateInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for invalidating indexers.
 */
class IndexerResetStateCommand extends AbstractIndexerManageCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('indexer:reset')
            ->setDescription('Resets indexer status to invalid')
            ->setDefinition($this->getInputList());

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $indexers = $this->getIndexers($input);

        $success = true;
        foreach ($indexers as $indexer) {
            try {
                $indexer->getState()
                    ->setStatus(StateInterface::STATUS_INVALID)
                    ->save();
                $output->writeln($indexer->getTitle() . ' indexer has been invalidated.');
            } catch (LocalizedException $e) {
                $output->writeln($e->getMessage());
                $success = false;
            } catch (\Exception $e) {
                $output->writeln($indexer->getTitle() . ' indexer process unknown error:');
                $output->writeln($e->getMessage());
                $success = false;
            }
        }

        return $success ? Cli::RETURN_SUCCESS : Cli::RETURN_FAILURE;
    }
}
