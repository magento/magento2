<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mview\View;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Indexer\ConfigInterface;
use Magento\Framework\Indexer\IndexerInterface;

/**
 * Class \Magento\Framework\Mview\View\Collection
 *
 */
class Collection extends \Magento\Framework\Data\Collection implements CollectionInterface
{
    /**
     * Item object class name
     *
     * @var string
     */
    protected $_itemObjectClass = \Magento\Framework\Mview\ViewInterface::class;

    /**
     * @var \Magento\Framework\Mview\ConfigInterface
     */
    protected $config;

    /**
     * @var \Magento\Framework\Mview\View\State\CollectionFactory
     */
    protected $statesFactory;

    /**
     * @var ConfigInterface
     */
    private $indexerConfig;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Magento\Framework\Mview\ConfigInterface $config
     * @param State\CollectionFactory $statesFactory
     * @param ConfigInterface $indexerConfig
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Magento\Framework\Mview\ConfigInterface $config,
        \Magento\Framework\Mview\View\State\CollectionFactory $statesFactory,
        ConfigInterface $indexerConfig = null
    ) {
        $this->config = $config;
        $this->statesFactory = $statesFactory;
        $this->indexerConfig = $indexerConfig ?: ObjectManager::getInstance()->get(ConfigInterface::class);
        parent::__construct($entityFactory);
    }

    /**
     * Load data
     *
     * @param bool $printQuery
     * @param bool $logQuery
     * @return \Magento\Framework\Mview\View\CollectionInterface
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function loadData($printQuery = false, $logQuery = false)
    {
        if (!$this->isLoaded()) {
            $states = $this->statesFactory->create();
            foreach ($this->getOrderedViewIds() as $viewId) {
                /** @var \Magento\Framework\Mview\ViewInterface $view */
                $view = $this->getNewEmptyItem();
                $view = $view->load($viewId);
                foreach ($states->getItems() as $state) {
                    /** @var \Magento\Framework\Mview\View\StateInterface $state */
                    if ($state->getViewId() == $viewId) {
                        $view->setState($state);
                        break;
                    }
                }
                $this->_addItem($view);
            }
            $this->_setIsLoaded(true);
        }
        return $this;
    }

    /**
     * @return array
     */
    private function getOrderedViewIds()
    {
        $orderedViewIds = [];
        /** @var IndexerInterface $indexer */
        foreach (array_keys($this->indexerConfig->getIndexers()) as $indexerId) {
            $indexer = $this->_entityFactory->create(IndexerInterface::class);
            $orderedViewIds[] = $indexer->load($indexerId)->getViewId();
        }
        $orderedViewIds = array_filter($orderedViewIds);
        $orderedViewIds += array_diff(array_keys($this->config->getViews()), $orderedViewIds);

        return $orderedViewIds;
    }

    /**
     * Return views by given state mode
     *
     * @param string $mode
     * @return \Magento\Framework\Mview\ViewInterface[]
     */
    public function getViewsByStateMode($mode)
    {
        $this->load();

        $result = [];
        foreach ($this as $view) {
            /** @var \Magento\Framework\Mview\ViewInterface $view */
            if ($view->getState()->getMode() == $mode) {
                $result[] = $view;
            }
        }
        return $result;
    }
}
