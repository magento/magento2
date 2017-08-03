<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model\Indexer;

/**
 * Class \Magento\Indexer\Model\Indexer\Collection
 *
 */
class Collection extends \Magento\Framework\Data\Collection
{
    /**
     * Item object class name
     *
     * @var string
     */
    protected $_itemObjectClass = \Magento\Framework\Indexer\IndexerInterface::class;

    /**
     * @var \Magento\Framework\Indexer\ConfigInterface
     */
    protected $config;

    /**
     * @var \Magento\Indexer\Model\ResourceModel\Indexer\State\CollectionFactory
     */
    protected $statesFactory;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Magento\Framework\Indexer\ConfigInterface $config
     * @param \Magento\Indexer\Model\ResourceModel\Indexer\State\CollectionFactory $statesFactory
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Magento\Framework\Indexer\ConfigInterface $config,
        \Magento\Indexer\Model\ResourceModel\Indexer\State\CollectionFactory $statesFactory
    ) {
        $this->config = $config;
        $this->statesFactory = $statesFactory;
        parent::__construct($entityFactory);
    }

    /**
     * Load data
     *
     * @param bool $printQuery
     * @param bool $logQuery
     * @return \Magento\Indexer\Model\Indexer\Collection
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function loadData($printQuery = false, $logQuery = false)
    {
        if (!$this->isLoaded()) {
            $states = $this->statesFactory->create();
            foreach (array_keys($this->config->getIndexers()) as $indexerId) {
                /** @var \Magento\Framework\Indexer\IndexerInterface $indexer */
                $indexer = $this->getNewEmptyItem();
                $indexer->load($indexerId);
                foreach ($states->getItems() as $state) {
                    /** @var \Magento\Indexer\Model\Indexer\State $state */
                    if ($state->getIndexerId() == $indexerId) {
                        $indexer->setState($state);
                        break;
                    }
                }
                $this->_addItem($indexer);
            }
            $this->_setIsLoaded(true);
        }
        return $this;
    }
}
