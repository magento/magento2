<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Mview\View;

class Collection extends \Magento\Data\Collection implements CollectionInterface
{
    /**
     * Item object class name
     *
     * @var string
     */
    protected $_itemObjectClass = 'Magento\Mview\ViewInterface';

    /**
     * @var \Magento\Mview\ConfigInterface
     */
    protected $config;

    /**
     * @var \Magento\Mview\View\State\CollectionFactory
     */
    protected $statesFactory;

    /**
     * @param \Magento\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Magento\Mview\ConfigInterface $config
     * @param State\CollectionFactory $statesFactory
     */
    public function __construct(
        \Magento\Data\Collection\EntityFactoryInterface $entityFactory,
        \Magento\Mview\ConfigInterface $config,
        \Magento\Mview\View\State\CollectionFactory $statesFactory
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
     * @return \Magento\Mview\View\CollectionInterface
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function loadData($printQuery = false, $logQuery = false)
    {
        if (!$this->isLoaded()) {
            $states = $this->statesFactory->create();
            foreach (array_keys($this->config->getViews()) as $viewId) {
                /** @var \Magento\Mview\ViewInterface $view */
                $view = $this->getNewEmptyItem();
                $view->load($viewId);
                foreach ($states->getItems() as $state) {
                    /** @var \Magento\Mview\View\StateInterface $state */
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
     * @return \Magento\Mview\ViewInterface[]
     */
    public function getViewsByStateMode($mode)
    {
        $this->load();

        $result = array();
        foreach ($this as $view) {
            /** @var \Magento\Mview\ViewInterface $view */
            if ($view->getState()->getMode() == $mode) {
                $result[] = $view;
            }
        }
        return $result;
    }
}
