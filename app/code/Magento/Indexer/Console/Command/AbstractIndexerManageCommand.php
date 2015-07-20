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
        $requestedTypes = [];
        if ($input->getArgument(self::INPUT_KEY_INDEXERS)) {
            $requestedTypes = $input->getArgument(self::INPUT_KEY_INDEXERS);
            $requestedTypes = array_filter(array_map('trim', $requestedTypes), 'strlen');
        }
        if (empty($requestedTypes)) {
            return $this->getAllIndexers();
        } else {
            $indexers = [];
            $unsupportedTypes = [];
            foreach ($requestedTypes as $code) {
                $indexer = $this->indexerFactory->create();
                try {
                    $indexer->load($code);
                    $indexers[] = $indexer;
                } catch (\Exception $e) {
                    $unsupportedTypes[] = $code;
                }
            }
            if ($unsupportedTypes) {
                $availableTypes = [];
                $indexers = $this->getAllIndexers();
                foreach ($indexers as $indexer) {
                    $availableTypes[] = $indexer->getId();
                }
                throw new \InvalidArgumentException(
                    "The following requested index types are not supported: '" . join("', '", $unsupportedTypes)
                    . "'." . PHP_EOL . 'Supported types: ' . join(", ", $availableTypes)
                );
            }
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
            new InputArgument(
                self::INPUT_KEY_INDEXERS,
                InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
                'List of index types, space separated. If omitted, all indexes will be affected'
            ),
        ];
    }
}
