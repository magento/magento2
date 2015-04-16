<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Command\Command;
use Magento\Indexer\Model\IndexerFactory;
use Magento\Indexer\Model\Indexer\CollectionFactory;
use Magento\Indexer\Model\IndexerInterface;
use Magento\Indexer\Model\Indexer;
use Magento\Framework\Console\ObjectManagerProvider;

/**
 * An Abstract class for all Indexer related commands.
 */
class AbstractIndexerCommand extends Command
{
    /**
     * Names of input arguments or options
     */
    const INPUT_KEY_ALL = 'all';
    const INPUT_KEY_INDEXERS = 'index';

    /**
     * Collection of Indexers factory
     *
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * Indexer factory
     *
     * @var IndexerFactory
     */
    protected $indexerFactory;

    /**
     * Constructor
     * @param ObjectManagerProvider $objectManagerProvider
     */
    public function __construct(ObjectManagerProvider $objectManagerProvider)
    {
        $objectManager = $objectManagerProvider->get();
        $this->collectionFactory = $objectManager->create('Magento\Indexer\Model\Indexer\CollectionFactory');
        $this->indexerFactory = $objectManager->create('Magento\Indexer\Model\IndexerFactory');
        parent::__construct();
    }

    /**
     * Gets list of indexers
     * 
     * @param InputInterface $input
     * @return IndexerInterface[]
     */
    public function getIndexers(InputInterface $input)
    {
        $inputArguments = $input->getArgument(AbstractIndexerCommand::INPUT_KEY_INDEXERS);
        if (isset($inputArguments) && sizeof($inputArguments)>0) {
            $indexes = implode(',', $inputArguments);
        } else {
            $indexes = AbstractIndexerCommand::INPUT_KEY_ALL;
        }
        $indexers = $this->parseIndexerString($indexes);
        return $indexers;
    }

    /**
     * Get list of arguments for the command
     *
     * @return InputOption[]
     */
    public function getOptionsList()
    {
        return [
            new InputOption(self::INPUT_KEY_ALL, 'a', InputOption::VALUE_NONE, 'Displays status of all Indexes'),
            new InputArgument(
                self::INPUT_KEY_INDEXERS,
                InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
                'List of Indexes'
            ),
        ];
    }

    /**
     * Parses string with indexers and return array of indexer instances
     *
     * @param string $string
     * @return IndexerInterface[]
     */
    public function parseIndexerString($string)
    {
        $indexers = [];
        if ($string === AbstractIndexerCommand::INPUT_KEY_ALL) {
            /** @var Indexer[] $indexers */
            $indexers = $this->collectionFactory->create()->getItems();
        } elseif (!empty($string)) {
            $codes = explode(',', $string);
            foreach ($codes as $code) {
                $indexer = $this->indexerFactory->create();
                try {
                    $indexer->load($code);
                    $indexers[] = $indexer;
                } catch (\Exception $e) {
                    echo 'Warning: Unknown indexer with code ' . trim($code) . PHP_EOL;
                }
            }
        }
        return $indexers;
    }
}
