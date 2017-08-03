<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mview\View;

/**
 * Class \Magento\Framework\Mview\View\Collection
 *
 * @since 2.0.0
 */
class Collection extends \Magento\Framework\Data\Collection implements CollectionInterface
{
    /**
     * Item object class name
     *
     * @var string
     * @since 2.0.0
     */
    protected $_itemObjectClass = \Magento\Framework\Mview\ViewInterface::class;

    /**
     * @var \Magento\Framework\Mview\ConfigInterface
     * @since 2.0.0
     */
    protected $config;

    /**
     * @var \Magento\Framework\Mview\View\State\CollectionFactory
     * @since 2.0.0
     */
    protected $statesFactory;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Magento\Framework\Mview\ConfigInterface $config
     * @param State\CollectionFactory $statesFactory
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
