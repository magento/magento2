<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Console\Command;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Framework\ObjectManagerInterface;
use Symfony\Component\Console\Command\Command;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\App\ObjectManagerFactory;

/**
 * An Abstract class for Indexer related commands.
 * @since 2.0.0
 */
abstract class AbstractIndexerCommand extends Command
{
    /**
     * @var ObjectManagerFactory
     * @since 2.0.0
     */
    private $objectManagerFactory;

    /**
     * @var ObjectManagerInterface
     * @since 2.0.0
     */
    private $objectManager;

    /**
     * @var \Magento\Indexer\Model\Indexer\CollectionFactory
     * @since 2.2.0
     */
    private $collectionFactory;

    /**
     * Constructor
     *
     * @param ObjectManagerFactory $objectManagerFactory
     * @param \Magento\Indexer\Model\Indexer\CollectionFactory|null $collectionFactory
     * @since 2.0.0
     */
    public function __construct(
        ObjectManagerFactory $objectManagerFactory,
        \Magento\Indexer\Model\Indexer\CollectionFactory $collectionFactory = null
    ) {
        $this->objectManagerFactory = $objectManagerFactory;
        $this->collectionFactory = $collectionFactory;
        parent::__construct();
    }

    /**
     * Return the array of all indexers with keys as indexer ids.
     *
     * @return IndexerInterface[]
     * @since 2.0.0
     */
    protected function getAllIndexers()
    {
        $indexers = $this->getCollectionFactory()->create()->getItems();
        return array_combine(
            array_map(
                function ($item) {
                    /** @var IndexerInterface $item */
                    return $item->getId();
                },
                $indexers
            ),
            $indexers
        );
    }

    /**
     * Gets initialized object manager
     *
     * @return ObjectManagerInterface
     * @since 2.0.0
     */
    protected function getObjectManager()
    {
        if (null == $this->objectManager) {
            $area = FrontNameResolver::AREA_CODE;
            $this->objectManager = $this->objectManagerFactory->create($_SERVER);
            /** @var \Magento\Framework\App\State $appState */
            $appState = $this->objectManager->get(\Magento\Framework\App\State::class);
            $appState->setAreaCode($area);
            $configLoader = $this->objectManager->get(\Magento\Framework\ObjectManager\ConfigLoaderInterface::class);
            $this->objectManager->configure($configLoader->load($area));
        }
        return $this->objectManager;
    }

    /**
     * Get collection factory
     *
     * @return \Magento\Indexer\Model\Indexer\CollectionFactory
     * @deprecated 2.2.0
     * @since 2.2.0
     */
    private function getCollectionFactory()
    {
        if (null === $this->collectionFactory) {
            $this->collectionFactory = $this->getObjectManager()
                ->get(\Magento\Indexer\Model\Indexer\CollectionFactory::class);
        }
        return $this->collectionFactory;
    }
}
