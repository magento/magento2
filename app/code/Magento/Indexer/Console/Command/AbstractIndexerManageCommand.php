<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Console\Command;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ObjectManagerFactory;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Indexer\Model\IndexerFactory;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

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
     * @var IndexerFactory|null
     */
    private $indexerFactory;

    /**
     * AbstractIndexerManageCommand constructor.
     * @param ObjectManagerFactory $objectManagerFactory
     * @param null $collectionFactory
     * @param IndexerFactory|null $indexerFactory
     * @throws \LogicException
     */
    public function __construct(
        ObjectManagerFactory $objectManagerFactory,
        $collectionFactory = null,
        IndexerFactory $indexerFactory = null
    ) {
        parent::__construct($objectManagerFactory, $collectionFactory);
        $this->indexerFactory = $indexerFactory;
    }

    /**
     * Gets list of indexers
     *
     * @param InputInterface $input
     * @return IndexerInterface[]
     * @throws \InvalidArgumentException
     * @throws \Magento\Framework\Exception\LocalizedException
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
            $indexerFactory = $this->getIndexerFactory();
            $indexers = [];
            $unsupportedTypes = [];
            foreach ($requestedTypes as $code) {
                $indexer = $indexerFactory->create();
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
                    "The following requested index types are not supported: '" . implode("', '", $unsupportedTypes)
                    . "'." . PHP_EOL . 'Supported types: ' . implode(', ', $availableTypes)
                );
            }
        }
        return $indexers;
    }

    /**
     * Get list of options and arguments for the command
     *
     * @return mixed
     * @throws \InvalidArgumentException
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
     * @deprecated
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getIndexerFactory()
    {
        if (null === $this->indexerFactory) {
            $this->indexerFactory = $this->getObjectManager()->create(IndexerFactory::class);
        }

        return $this->indexerFactory;
    }
}
