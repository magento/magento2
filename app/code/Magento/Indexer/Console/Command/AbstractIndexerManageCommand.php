<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Magento\Indexer\Model\IndexerInterface;

/**
 * An Abstract class for all Indexer related commands.
 */
abstract class AbstractIndexerManageCommand extends AbstractIndexerCommand
{
    /**
     * Gets list of indexers
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return IndexerInterface[]
     */
    protected function getIndexers(InputInterface $input, OutputInterface $output)
    {
        $inputArguments = $input->getArgument(self::INPUT_KEY_INDEXERS);
        if (isset($inputArguments) && sizeof($inputArguments)>0) {
            $indexers = [];
            foreach ($inputArguments as $code) {
                $indexer = $this->indexerFactory->create();
                try {
                    $indexer->load($code);
                    $indexers[] = $indexer;
                } catch (\Exception $e) {
                    $output->writeln('Warning: Unknown indexer with code ' . trim($code));
                }
            }
        } else {
            $indexers = $this->getAllIndexers();
        }
        return $indexers;
    }

    /**
     * Get list of options and arguments for the command
     *
     * @return mixed
     */
    public function getInputList()
    {
        return [
            new InputOption(self::INPUT_KEY_ALL, 'a', InputOption::VALUE_NONE, 'All Indexes'),
            new InputArgument(
                self::INPUT_KEY_INDEXERS,
                InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
                'List of Indexes'
            ),
        ];
    }
}
