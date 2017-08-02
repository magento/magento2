<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Console\Command;

use Magento\Framework\Indexer\IndexerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * An Abstract class for all Indexer related commands.
 * @since 2.0.0
 */
abstract class AbstractIndexerManageCommand extends AbstractIndexerCommand
{
    /**
     * Indexer name option
     */
    const INPUT_KEY_INDEXERS = 'index';

    /**
     * Returns the ordered list of indexers.
     *
     * @param InputInterface $input
     * @return IndexerInterface[]
     * @throws \InvalidArgumentException
     * @since 2.0.0
     */
    protected function getIndexers(InputInterface $input)
    {
        $requestedTypes = [];
        if ($input->getArgument(self::INPUT_KEY_INDEXERS)) {
            $requestedTypes = $input->getArgument(self::INPUT_KEY_INDEXERS);
            $requestedTypes = array_filter(array_map('trim', $requestedTypes), 'strlen');
        }

        if (empty($requestedTypes)) {
            $indexers = $this->getAllIndexers();
        } else {
            $availableIndexers = $this->getAllIndexers();
            $unsupportedTypes = array_diff($requestedTypes, array_keys($availableIndexers));
            if ($unsupportedTypes) {
                throw new \InvalidArgumentException(
                    "The following requested index types are not supported: '" . join("', '", $unsupportedTypes)
                    . "'." . PHP_EOL . 'Supported types: ' . join(", ", array_keys($availableIndexers))
                );
            }
            $indexers = array_intersect_key($availableIndexers, array_flip($requestedTypes));
        }

        return $indexers;
    }

    /**
     * Get list of options and arguments for the command
     *
     * @return mixed
     * @since 2.0.0
     */
    public function getInputList()
    {
        return [
            new InputArgument(
                self::INPUT_KEY_INDEXERS,
                InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
                'Space-separated list of index types or omit to apply to all indexes.'
            ),
        ];
    }
}
