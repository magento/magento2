<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\App\ObjectManagerFactory;
use Magento\Indexer\Model\IndexerFactory;

/**
 * An Abstract class for all Indexer related commands.
 */
abstract class AbstractIndexerManageCommand extends AbstractIndexerCommand
{
    /**
     * Indexer name option
     */
    const INPUT_KEY_INDEXERS = 'index';

    /**
     * @var IndexerFactory
     */
    private $indexerFactory;

    /**
     * Constructor
     *
     * @param ObjectManagerFactory $objectManagerFactory
     * @param IndexerFactory|null $indexerFactory
     */
    public function __construct(
        ObjectManagerFactory $objectManagerFactory,
        IndexerFactory $indexerFactory = null
    ) {
        parent::__construct($objectManagerFactory);
        $this->indexerFactory = $indexerFactory;
    }

    /**
     * Gets list of indexers
     *
     * @param InputInterface $input
     * @return IndexerInterface[]
     * @throws \InvalidArgumentException
     */
    protected function getIndexers(InputInterface $input)
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
                $indexer = $this->getIndexerFactory()->create();
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
                'Space-separated list of index types or omit to apply to all indexes.'
            ),
        ];
    }

    /**
     * Get indexer factory
     *
     * @return IndexerFactory
     * @deprecated
     */
    private function getIndexerFactory()
    {
        if (null === $this->indexerFactory) {
            $this->indexerFactory = $this->getObjectManager()->get(IndexerFactory::class);
        }
        return $this->indexerFactory;
    }
}
