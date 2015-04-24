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
use Symfony\Component\Console\Command\Command;
use Magento\Indexer\Model\IndexerFactory;
use Magento\Indexer\Model\Indexer\CollectionFactory;
use Magento\Indexer\Model\IndexerInterface;
use Magento\Indexer\Model\Indexer;
use Magento\Store\Model\StoreManager;
use Magento\Framework\App\ObjectManagerFactory;

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
     * @param ObjectManagerFactory $objectManagerFactory
     */
    public function __construct(ObjectManagerFactory $objectManagerFactory)
    {
        $params = $_SERVER;
        $params[StoreManager::PARAM_RUN_CODE] = 'admin';
        $params[StoreManager::PARAM_RUN_TYPE] = 'store';
        $objectManager = $objectManagerFactory->create($params);
        $this->collectionFactory = $objectManager->create('Magento\Indexer\Model\Indexer\CollectionFactory');
        $this->indexerFactory = $objectManager->create('Magento\Indexer\Model\IndexerFactory');
        parent::__construct();
    }

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
            $indexes = implode(',', $inputArguments);
        } else {
            $indexes = self::INPUT_KEY_ALL;
        }
        $indexers = $this->parseIndexerString($indexes, $output);
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

    /**
     * Parses string with indexers and return array of indexer instances
     *
     * @param string $string
     * @param OutputInterface $output
     * @return IndexerInterface[]
     */
    protected function parseIndexerString($string, OutputInterface $output)
    {
        $indexers = [];
        if ($string === self::INPUT_KEY_ALL) {
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
                    $output->writeln('Warning: Unknown indexer with code ' . trim($code));
                }
            }
        }
        return $indexers;
    }
}
