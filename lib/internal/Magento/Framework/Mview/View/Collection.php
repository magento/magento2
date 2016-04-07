<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mview\View;

class Collection extends \Magento\Framework\Data\Collection implements CollectionInterface
{
    /**
     * Item object class name
     *
     * @var string
     */
    protected $_itemObjectClass = 'Magento\Framework\Mview\ViewInterface';

    /**
     * @var \Magento\Framework\Mview\ConfigInterface
     */
    protected $config;

    /**
     * @var \Magento\Framework\Mview\View\State\CollectionFactory
     */
    protected $statesFactory;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Magento\Framework\Mview\ConfigInterface $config
     * @param State\CollectionFactory $statesFactory
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Magento\Framework\Mview\ConfigInterface $config,
        \Magento\Framework\Mview\View\State\CollectionFactory $statesFactory
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
     * @return \Magento\Framework\Mview\View\CollectionInterface
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function loadData($printQuery = false, $logQuery = false)
    {
        if (!$this->isLoaded()) {
            $states = $this->statesFactory->create();
            foreach (array_keys($this->config->getViews()) as $viewId) {
                /** @var \Magento\Framework\Mview\ViewInterface $view */
                $view = $this->getNewEmptyItem();
                $view->load($viewId);
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
