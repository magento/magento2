<?php
/**
 * Google Optimizer Observer Category Tab
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\GoogleOptimizer\Observer\Block\Category;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class AddGoogleExperimentTabObserver implements ObserverInterface
{
    /**
     * @var \Magento\GoogleOptimizer\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $_layout;

    /**
     * @param \Magento\GoogleOptimizer\Helper\Data $helper
     * @param \Magento\Framework\View\LayoutInterface $layout
     */
    public function __construct(\Magento\GoogleOptimizer\Helper\Data $helper, \Magento\Framework\View\LayoutInterface $layout)
    {
        $this->_helper = $helper;
        $this->_layout = $layout;
    }

    /**
     * Adds Google Experiment tab to the category edit page
     *
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        if ($this->_helper->isGoogleExperimentActive()) {
            $block = $this->_layout->createBlock(
                'Magento\GoogleOptimizer\Block\Adminhtml\Catalog\Category\Edit\Tab\Googleoptimizer',
                'google-experiment-form'
            );

            /** @var $tabs \Magento\Catalog\Block\Adminhtml\Category\Tabs */
            $tabs = $observer->getEvent()->getTabs();
            $tabs->addTab(
                'google-experiment-tab',
                ['label' => __('Category View Optimization'), 'content' => $block->toHtml()]
            );
        }
    }
}
